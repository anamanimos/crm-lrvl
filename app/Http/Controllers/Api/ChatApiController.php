<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\WaGroup;
use App\Models\Message;
use App\Models\Setting;
use App\Services\WaGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatApiController extends Controller
{
    protected $waGateway;

    public function __construct(WaGateway $waGateway)
    {
        $this->waGateway = $waGateway;
    }

    /**
     * Send a WhatsApp message and record it in CRM.
     *
     * Expected payload:
     * - phone / target / recipient: string (required)
     * - message / content / text: string (required if no media)
     * - customer_name / name: string (optional)
     * - source: string (optional, default: 'ERP Notifikasi')
     * - media_url / image_url / document_url: string (optional)
     * - media_type: string (optional: 'image', 'document', 'video', 'audio')
     * - caption: string (optional)
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string',
            'target' => 'nullable|string',
            'recipient' => 'nullable|string',
            'message' => 'nullable|string',
            'content' => 'nullable|string',
            'text' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
            'media_url' => 'nullable|string',
            'image_url' => 'nullable|string',
            'document_url' => 'nullable|string',
            'media_type' => 'nullable|string|in:image,document,video,audio',
            'caption' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $rawPhone = $request->input('phone') ?? $request->input('target') ?? $request->input('recipient');
        $rawMessage = $request->input('message') ?? $request->input('content') ?? $request->input('text') ?? '';
        $customerName = $request->input('customer_name') ?? $request->input('name');
        $source = $request->input('source') ?: 'ERP Notifikasi';

        $mediaUrl = $request->input('media_url') ?? $request->input('image_url') ?? $request->input('document_url');
        $mediaType = $request->input('media_type');
        $caption = $request->input('caption', '');

        if (empty($rawPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor tujuan (phone) wajib diisi.'
            ], 422);
        }

        if (empty($rawMessage) && empty($mediaUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan (message) atau media_url wajib diisi.'
            ], 422);
        }

        // Format phone
        $phone = trim($rawPhone);
        $isGroup = (strpos($phone, '@g.us') !== false || (strpos($phone, '-') !== false && strlen($phone) > 15));

        $customerId = null;
        $waGroupId = null;
        $targetJid = '';

        if ($isGroup) {
            $groupJid = $phone;
            if (strpos($groupJid, '@') === false) {
                $groupJid .= '@g.us';
            }
            $targetJid = $groupJid;

            $group = WaGroup::where('jid', $groupJid)->first();
            if (!$group) {
                $group = WaGroup::create([
                    'jid' => $groupJid,
                    'name' => $customerName ?: 'Unknown Group',
                    'last_chat_at' => now(),
                ]);
            } else {
                $group->update(['last_chat_at' => now()]);
            }
            $waGroupId = $group->id;
        } else {
            // Clean phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($cleanPhone, '08')) {
                $cleanPhone = '628' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '8')) {
                $cleanPhone = '628' . substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $targetJid = $cleanPhone;

            $customer = Customer::where('wa_number', $cleanPhone)->first();
            if (!$customer) {
                $displayName = $customerName ?: ('WA - ' . $cleanPhone);
                if ($customerName && !str_contains($displayName, $cleanPhone)) {
                    $displayName = $customerName . ' - ' . $cleanPhone;
                }

                $customer = Customer::create([
                    'wa_number' => $cleanPhone,
                    'name' => $displayName,
                    'source' => $source,
                    'last_chat_at' => now(),
                ]);
            } else {
                $updates = ['last_chat_at' => now()];
                if ($customerName && (empty($customer->name) || str_starts_with($customer->name, 'WA - '))) {
                    $updates['name'] = $customerName . ' - ' . $cleanPhone;
                }
                $customer->update($updates);
            }
            $customerId = $customer->id;
        }

        // Determine message type and format content
        $msgType = 'text';
        $finalContent = $rawMessage;

        if ($mediaUrl) {
            if (!$mediaType) {
                $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $mediaType = 'image';
                } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'])) {
                    $mediaType = 'document';
                } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
                    $mediaType = 'video';
                } elseif (in_array($ext, ['mp3', 'ogg', 'wav'])) {
                    $mediaType = 'audio';
                } else {
                    $mediaType = 'document';
                }
            }
            $msgType = $mediaType;
            $captionText = $caption ?: $rawMessage;

            if ($mediaType === 'image') {
                $finalContent = "[IMAGE:{$mediaUrl}]" . ($captionText ? " {$captionText}" : "");
            } elseif ($mediaType === 'document') {
                $filename = basename(parse_url($mediaUrl, PHP_URL_PATH)) ?: 'file';
                $finalContent = "[DOCUMENT:{$mediaUrl}:{$filename}]";
            } elseif ($mediaType === 'video') {
                $finalContent = "[VIDEO:{$mediaUrl}]" . ($captionText ? " {$captionText}" : "");
            } elseif ($mediaType === 'audio') {
                $finalContent = "[AUDIO:{$mediaUrl}]";
            }
        }

        // Send via Gateway
        try {
            if ($mediaUrl && $mediaType === 'image') {
                $gatewayRes = $this->waGateway->sendImageUrl($targetJid, $mediaUrl, $caption ?: $rawMessage);
            } elseif ($mediaUrl && $mediaType === 'document') {
                $filename = basename(parse_url($mediaUrl, PHP_URL_PATH)) ?: 'file';
                $gatewayRes = $this->waGateway->sendDocumentUrl($targetJid, $mediaUrl, $filename, $caption ?: $rawMessage);
            } else {
                $gatewayRes = $this->waGateway->sendMessage($targetJid, $finalContent);
            }
        } catch (\Exception $e) {
            Log::error('ChatApiController: Gateway error: ' . $e->getMessage());
            $gatewayRes = ['success' => false, 'error' => $e->getMessage()];
        }

        $isSuccess = $gatewayRes['success'] ?? false;
        $waMessageId = $gatewayRes['data']['results']['message_id'] ?? null;

        // Record in CRM database
        $message = Message::create([
            'customer_id' => $customerId,
            'wa_group_id' => $waGroupId,
            'user_id' => null, // Sent via external API
            'content' => $finalContent,
            'direction' => 'out',
            'type' => $msgType,
            'media_url' => $mediaUrl,
            'status' => $isSuccess ? 'sent' : 'failed',
            'wa_message_id' => $waMessageId,
            'wa_timestamp' => time(),
            'is_read' => true,
            'is_external_reply' => false,
        ]);

        if ($isSuccess) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim dan dicatat di CRM.',
                'data' => [
                    'message_id' => $message->id,
                    'wa_message_id' => $waMessageId,
                    'customer_id' => $customerId,
                    'wa_group_id' => $waGroupId,
                    'status' => 'sent',
                    'sent_at' => now()->toIso8601String(),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesan tercatat di CRM tapi gagal dikirim oleh WhatsApp Gateway.',
            'error' => $gatewayRes['error'] ?? 'Gateway returned error',
            'data' => [
                'message_id' => $message->id,
                'status' => 'failed',
            ]
        ], 502);
    }

    /**
     * Check status of WhatsApp connection in CRM.
     */
    public function getStatus(Request $request)
    {
        $deviceId = Setting::get('gowa_device_id', 'crm-session');
        $baseUrl = rtrim(Setting::get('gowa_api_url', Setting::get('gateway_url', 'http://localhost:3000')), '/');

        return response()->json([
            'success' => true,
            'session' => $deviceId,
            'gateway_url' => $baseUrl,
            'status' => Setting::get('wa_status', 'unknown'),
            'last_sync' => Setting::get('wa_last_status_check'),
        ]);
    }
}

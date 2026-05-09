<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Setting;
use App\Models\AutoReply;
use App\Models\WaGroup;
use App\Models\WebhookLog;
use App\Services\WaGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    protected $waGateway;

    public function __construct(WaGateway $waGateway)
    {
        $this->waGateway = $waGateway;
    }

    public function receive(Request $request)
    {
        $rawPayload = $request->getContent();
        $input = $request->all();

        // Fallback for non-JSON content types if sent as raw
        if (empty($input) && !empty($rawPayload)) {
            $input = json_decode($rawPayload, true) ?: [];
        }

        if (empty($input)) {
            return response('OK - No payload', 200);
        }

        // --- WEBHOOK PAYLOAD SHIM (Compatibility with different gateway versions) ---
        // Flatten structure if nested under 'payload'
        if (isset($input['payload']) && is_array($input['payload'])) {
            $inner = $input['payload'];
            if (isset($inner['payload']) && is_array($inner['payload'])) {
                $inner = array_merge($inner, $inner['payload']);
            }
            $input = array_merge($input, $inner);
        }

        // Normalize booleans and event types
        if (isset($input['is_from_me']) && !isset($input['fromMe'])) {
            $input['fromMe'] = (bool) $input['is_from_me'];
        }
        if (isset($input['event']) && !isset($input['type'])) {
            $input['type'] = $input['event'];
        }

        // --- WEBHOOK LOGGING ---
        $fromNumber = $this->extractFromNumber($input);
        $messageId = $this->extractMessageId($input);
        $type = $input['type'] ?? $input['event'] ?? (isset($input['message']) ? 'message' : 'unknown');

        $log = WebhookLog::create([
            'event_type' => $type,
            'from_number' => $fromNumber,
            'message_id' => $messageId,
            'payload' => $input,
            'processed' => 0,
            'ip_address' => $request->ip()
        ]);

        // --- MULTIPLEXER (FORWARDER) ---
        if (Setting::get('webhook_forward_enabled') == '1') {
            $forwardUrl = Setting::get('webhook_forward_url');
            if (!empty($forwardUrl) && filter_var($forwardUrl, FILTER_VALIDATE_URL)) {
                $this->forwardWebhook($forwardUrl, $rawPayload);
            }
        }

        // --- DEVICE ID FILTERING ---
        $incomingDevice = $input['device_id'] ?? null;
        $pairedJid = Setting::get('gowa_paired_jid');

        if ($incomingDevice && !empty($pairedJid)) {
            // Clean both for comparison
            $cleanIncoming = preg_replace('/[^0-9]/', '', $incomingDevice);
            $cleanPaired = preg_replace('/[^0-9]/', '', $pairedJid);
            
            if ($cleanIncoming !== $cleanPaired) {
                $log->update(['error_message' => 'Ignored cross-device traffic: ' . $incomingDevice]);
                return response()->json(['success' => true, 'message' => 'Ignored cross-device traffic']);
            }
        }

        // --- EVENT ROUTING ---
        try {
            switch ($type) {
                case 'message':
                case 'incoming':
                case 'message.incoming':
                case 'message.upsert':
                case 'message.create':
                    $this->handleIncomingMessage($input);
                    break;
                
                case 'message.ack':
                case 'message.update':
                case 'status':
                case 'delivery':
                case 'read':
                    $this->handleStatusUpdate($input);
                    break;
            }
            
            $log->update(['processed' => 1]);
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            $log->update(['error_message' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    protected function handleIncomingMessage($data)
    {
        // 1. Detect if it's from me
        $fromMe = $data['fromMe'] ?? $data['is_from_me'] ?? $data['key']['fromMe'] ?? $data['message']['key']['fromMe'] ?? false;
        
        // 2. Extract Message ID
        $messageId = $this->extractMessageId($data);
        if (!$messageId) return;

        // 3. Check for Duplicates (IMPORTANT)
        if (Message::where('wa_message_id', $messageId)->exists()) {
            return;
        }

        // 4. Extract Content & Phone
        $content = $this->extractContent($data);
        $phone = $this->extractPhone($data, $fromMe);
        
        // 5. Identify Chat & Sender
        $chatJid = $data['chat_id'] ?? $data['key']['remoteJid'] ?? null;
        $from = $data['from'] ?? $data['key']['participant'] ?? $data['participant'] ?? $data['author'] ?? $chatJid;
        
        if (!$chatJid) {
            $chatJid = $from;
        }

        if (!$chatJid || ($content === null && !$this->hasMedia($data))) {
            return;
        }

        $isGroup = (strpos($chatJid, '@g.us') !== false || strpos($chatJid, '-') !== false);
        $waGroupId = null;
        $customerId = null;

        // Clean JIDs
        $chatJid = explode(':', $chatJid)[0];
        $from = explode(':', $from)[0];

        // Always identify the sender as a customer
        $senderPhone = preg_replace('/[^0-9]/', '', explode('@', $from)[0]);
        $senderName = $data['pushname'] ?? $data['name'] ?? $data['sender_name'] ?? $data['from_name'] ?? null;

        if ($senderPhone) {
            $customer = Customer::where('wa_number', $senderPhone)->first();
            if (!$customer) {
                $formattedName = $senderName ?: ('WA - ' . $senderPhone);
                if ($senderName && !str_contains($formattedName, $senderPhone)) {
                    $formattedName = $senderName . ' - ' . $senderPhone;
                }
                $customer = Customer::create([
                    'wa_number' => $senderPhone,
                    'name' => $formattedName
                ]);
            } elseif ($senderName && (empty($customer->name) || str_starts_with($customer->name, 'WA - '))) {
                $customer->update(['name' => $senderName . ' - ' . $senderPhone]);
            }
            $customerId = $customer->id;
            // Only update last_chat_at for personal chats (not in group context)
            if (!$isGroup) {
                $customer->update(['last_chat_at' => now()]);
            }
        }

        if ($isGroup) {
            $waGroup = WaGroup::where('jid', $chatJid)->first();
            
            // Candidate for group name from payload
            $groupNameFromPayload = $data['group_subject'] ?? $data['subject'] ?? $data['group_name'] ?? null;
            
            if (!$waGroup) {
                $gInfo = $this->waGateway->getGroupInfo($chatJid);
                $finalGroupName = $gInfo['success'] ? ($gInfo['data']['results']['Name'] ?? $gInfo['data']['name'] ?? $groupNameFromPayload) : $groupNameFromPayload;
                
                $waGroup = WaGroup::create([
                    'jid' => $chatJid,
                    'name' => $finalGroupName ?: 'Unknown Group'
                ]);
            } elseif (($waGroup->name === 'Unknown Group' || empty($waGroup->name)) && $groupNameFromPayload) {
                $waGroup->update(['name' => $groupNameFromPayload]);
            }
            
            $waGroupId = $waGroup->id;
            $waGroup->update(['last_chat_at' => now()]);
            
            // Add Sender Prefix for Group Messages (Parity with Legacy)
            if (!$fromMe && $senderName && !empty($content) && strpos($content, '[SENDER:') === false) {
                $content = "[SENDER:{$senderName}] " . $content;
            }
        }

        // --- OUTGOING SYNC / SIMILARITY MATCH ---
        // If it's from me, check if we already have a record without an ID (sent from CRM)
        if ($fromMe && !empty($content)) {
            $similarQuery = Message::where('direction', 'out')
                ->whereNull('wa_message_id')
                ->where('created_at', '>', now()->subSeconds(30));

            if ($isGroup) {
                $similarQuery->where('wa_group_id', $waGroupId);
            } else {
                $similarQuery->where('customer_id', $customerId);
            }

            // Simple content match (ignoring whitespace)
            $trimmedContent = trim($content);
            $similar = $similarQuery->get()->filter(function($msg) use ($trimmedContent) {
                // Remove media prefixes if matching against raw content
                $msgContent = preg_replace('/^\[(IMAGE|DOCUMENT|VIDEO|AUDIO):[^\]]+\]\s*/', '', $msg->content);
                return trim($msgContent) === $trimmedContent;
            })->first();

            if ($similar) {
                $similar->update([
                    'wa_message_id' => $messageId,
                    'status' => 'sent'
                ]);
                return;
            }
        }

        // 6. Media Handling
        $messageType = 'text';
        $mediaUrl = null;
        
        if (isset($data['image'])) {
            $messageType = 'image';
            $mediaUrl = $this->getMediaUrl($data['image']);
            $caption = is_array($data['image']) ? ($data['image']['caption'] ?? '') : ($data['caption'] ?? '');
            $content = "[IMAGE:{$mediaUrl}]" . ($caption ? " {$caption}" : "");
        } elseif (isset($data['document'])) {
            $messageType = 'document';
            $mediaUrl = $this->getMediaUrl($data['document']);
            $filename = is_array($data['document']) ? ($data['document']['filename'] ?? 'document') : ($data['filename'] ?? 'document');
            $content = "[DOCUMENT:{$mediaUrl}:{$filename}]";
        }

        // 7. Extract Reply/Quoted Message
        $replyMessageId = $data['replied_to_id'] ?? $data['quoted_id'] ?? null;
        $replyContent = $data['quoted_body'] ?? $data['quoted_message'] ?? null;
        $replySenderName = $data['quoted_sender_name'] ?? $data['quoted_name'] ?? null;

        if ($replyMessageId && empty($replySenderName)) {
            // Try to find the original sender in our database
            $originalMsg = Message::where('wa_message_id', $replyMessageId)->first();
            if ($originalMsg) {
                $replySenderName = $originalMsg->direction == 'out' ? 'Anda' : ($originalMsg->customer->name ?? 'Customer');
                if (!$replyContent) $replyContent = $originalMsg->content;
            }
        }

        // 8. Save Message
        Message::create([
            'customer_id' => $customerId,
            'wa_group_id' => $waGroupId,
            'content' => $content,
            'direction' => $fromMe ? 'out' : 'in',
            'type' => $messageType,
            'media_url' => $mediaUrl,
            'wa_message_id' => $messageId,
            'reply_message_id' => $replyMessageId,
            'reply_content' => $replyContent,
            'reply_sender_name' => $replySenderName,
            'status' => $fromMe ? 'sent' : 'unread',
            'created_at' => now(),
        ]);

        // 8. Auto Reply (only for individual incoming messages)
        if (!$isGroup && !$fromMe && Setting::get('auto_reply_enabled') == '1') {
            $this->checkAutoReply($customer, $content);
        }
    }

    protected function handleStatusUpdate($data)
    {
        $messageId = $this->extractMessageId($data);
        $messageIds = is_array($messageId) ? $messageId : ($messageId ? [$messageId] : []);
        
        // Additional check for 'ids' array if extractMessageId missed it
        if (empty($messageIds) && isset($data['ids']) && is_array($data['ids'])) {
            $messageIds = $data['ids'];
        }

        $status = $data['status'] ?? $data['receipt_type'] ?? $data['ack'] ?? null;
        
        // Normalize status
        if (is_numeric($status)) {
            $statusMap = [0 => 'pending', 1 => 'sent', 2 => 'delivered', 3 => 'read', 4 => 'played'];
            $status = $statusMap[$status] ?? 'sent';
        }

        if (!empty($messageIds) && $status) {
            foreach ($messageIds as $id) {
                $exists = Message::where('wa_message_id', $id)->exists();
                
                if (!$exists) {
                    // --- ACK ID CAPTURE (Gunakan chat_id untuk mencari pesan yang "menggantung") ---
                    $phone = $this->extractPhone($data, true);
                    if ($phone) {
                        $phoneNum = preg_replace('/[^0-9]/', '', explode('@', $phone)[0]);
                        $customer = Customer::where('wa_number', $phoneNum)->first();
                        
                        if ($customer) {
                            $pending = Message::where('customer_id', $customer->id)
                                ->where('direction', 'out')
                                ->whereNull('wa_message_id')
                                ->where('created_at', '>', now()->subMinutes(2))
                                ->orderBy('created_at', 'desc')
                                ->first();
                            
                            if ($pending) {
                                $pending->update(['wa_message_id' => $id]);
                            }
                        }
                    }
                }
                
                Message::where('wa_message_id', $id)->update(['status' => $status]);
            }
        }
    }

    protected function checkAutoReply($customer, $content)
    {
        $rules = AutoReply::where('is_active', 1)->get();
        foreach ($rules as $rule) {
            if ($rule->matches($content)) {
                // 1. Send media if attached
                if (!empty($rule->media_path)) {
                    $mediaUrl = $rule->media_path;
                    // Resolve local path to full URL
                    if (!Str::startsWith($mediaUrl, ['http://', 'https://'])) {
                        $mediaUrl = asset('storage/' . $mediaUrl);
                    }

                    try {
                        if ($rule->media_type === 'image') {
                            $this->waGateway->sendImageUrl($customer->wa_number, $mediaUrl, '');
                        } else {
                            $this->waGateway->sendDocumentUrl($customer->wa_number, $mediaUrl, basename($mediaUrl));
                        }

                        Message::create([
                            'customer_id' => $customer->id,
                            'content' => "[" . strtoupper($rule->media_type ?? 'image') . ":{$mediaUrl}]",
                            'direction' => 'out',
                            'type' => $rule->media_type ?? 'image',
                            'media_url' => $mediaUrl,
                            'status' => 'sent',
                            'sender_type' => 'system'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('AutoReply media send failed: ' . $e->getMessage());
                    }
                }

                // 2. Send text responses
                $responses = $rule->response_messages;
                if (!empty($responses)) {
                    foreach ($responses as $resp) {
                        $text = $resp['content'] ?? $resp['text'] ?? (is_string($resp) ? $resp : null);
                        if (empty($text)) continue;

                        $text = str_replace('{name}', $customer->name ?? '', $text);

                        $this->waGateway->sendMessage($customer->wa_number, $text);
                        
                        Message::create([
                            'customer_id' => $customer->id,
                            'content' => $text,
                            'direction' => 'out',
                            'type' => 'text',
                            'status' => 'sent',
                            'sender_type' => 'system'
                        ]);
                    }
                }
            }
        }
    }

    // --- EXTRACTION HELPERS ---
    
    private function extractFromNumber($data) {
        $chatId = $data['chat_id'] ?? $data['key']['remoteJid'] ?? null;
        if ($chatId) return $chatId;
        
        return $data['phone'] ?? $data['sender_id'] ?? null;
    }

    private function extractMessageId($data) {
        if (isset($data['ids']) && is_array($data['ids'])) return $data['ids'];
        return $data['message_id'] ?? $data['messageId'] ?? $data['id'] ?? $data['key']['id'] ?? ($data['message']['id'] ?? null);
    }

    private function extractContent($data) {
        // Handle nested message structure (v8+)
        if (isset($data['message']) && is_array($data['message'])) {
            return $data['message']['text'] ?? $data['message']['conversation'] ?? $data['message']['body'] ?? null;
        }
        return $data['message'] ?? $data['text'] ?? $data['body'] ?? null;
    }

    private function hasMedia($data) {
        return isset($data['image']) || isset($data['document']) || isset($data['video']) || isset($data['audio']);
    }

    private function extractPhone($data, $fromMe) {
        $chatId = $data['chat_id'] ?? $data['key']['remoteJid'] ?? null;
        
        // Prioritize chat_id for Groups
        if ($chatId && (strpos($chatId, '@g.us') !== false || strpos($chatId, '-') !== false)) {
            return explode(':', $chatId)[0];
        }

        // In some ACKs, 'from' is the customer phone while 'chat_id' is an LID
        $from = $data['from'] ?? null;
        if ($from && strpos($from, '@s.whatsapp.net') !== false) {
            return explode(':', $from)[0];
        }

        if ($chatId) {
            // Remove instance suffix if any
            return explode(':', $chatId)[0];
        }
        
        if ($fromMe) return $data['to'] ?? null;
        return $from ?? $data['phone'] ?? $data['sender_id'] ?? null;
    }

    private function getMediaUrl($media) {
        $path = is_string($media) ? $media : ($media['path'] ?? $media['url'] ?? null);
        if (!$path) return null;
        
        if (strpos($path, 'http') === 0) return $path;
        
        $baseUrl = rtrim(Setting::get('gowa_api_url', 'https://wag.anam.ch'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function forwardWebhook($url, $payload) {
        try {
            Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(2)
                ->post($url, json_decode($payload, true));
        } catch (\Exception $e) {
            // Silent fail for forwarder
        }
    }
}

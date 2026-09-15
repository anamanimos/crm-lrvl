<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Setting;
use App\Models\AutoReply;
use App\Models\WaGroup;
use App\Models\WebhookLog;
use App\Models\Label;
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
        $strictDeviceFilter = Setting::get('gowa_strict_device_filter', '0');

        if ($incomingDevice && !empty($pairedJid) && $strictDeviceFilter === '1') {
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

                case 'connection':
                case 'connection.update':
                case 'device.state':
                case 'session.state':
                case 'disconnected':
                case 'logged_out':
                case 'authenticated':
                case 'ready':
                    $this->handleConnectionUpdate($input, $type);
                    break;

                case 'label.edit':
                case 'label_edit':
                    $this->handleLabelEdit($input);
                    break;

                case 'label.association':
                case 'label_association':
                    $this->handleLabelAssociation($input);
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
        $customer = null;

        // Clean JIDs
        $chatJid = explode(':', $chatJid)[0];
        $from = explode(':', $from)[0];

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
            
            $senderName = $data['pushname'] ?? $data['name'] ?? $data['sender_name'] ?? $data['from_name'] ?? null;
            // Add Sender Prefix for Group Messages (Parity with Legacy)
            if (!$fromMe && $senderName && !empty($content) && strpos($content, '[SENDER:') === false) {
                $content = "[SENDER:{$senderName}] " . $content;
            }
        } else {
            // 1-on-1 Chat
            if ($fromMe) {
                // Outgoing message sent from device/WA Web: customer is the recipient (chatJid / to), not the sender ($from)
                $targetPhone = preg_replace('/[^0-9]/', '', explode('@', $chatJid)[0]);
                if (empty($targetPhone) && !empty($data['to'])) {
                    $targetPhone = preg_replace('/[^0-9]/', '', explode('@', $data['to'])[0]);
                }

                if ($targetPhone) {
                    $customer = Customer::where('wa_number', $targetPhone)->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'wa_number' => $targetPhone,
                            'name' => 'WA - ' . $targetPhone,
                            'source' => 'WhatsApp Outgoing'
                        ]);
                    }
                    $customerId = $customer->id;
                    $customer->update(['last_chat_at' => now()]);
                }
            } else {
                // Incoming message from customer: customer is the sender ($from)
                $senderPhone = preg_replace('/[^0-9]/', '', explode('@', $from)[0]);
                $senderName = $data['pushname'] ?? $data['name'] ?? $data['sender_name'] ?? $data['from_name'] ?? null;

                if ($senderPhone) {
                    $customer = Customer::where('wa_number', $senderPhone)->first();
                    if (!$customer) {
                        // Detect source from incoming message content only for new customers
                        $detectedSource = null;
                        if (!empty($content)) {
                            $matchedRule = \App\Models\ChatSourceRule::findMatch($content);
                            if ($matchedRule) {
                                $detectedSource = $matchedRule->source_name;
                            }
                        }

                        $formattedName = $senderName ?: ('WA - ' . $senderPhone);
                        if ($senderName && !str_contains($formattedName, $senderPhone)) {
                            $formattedName = $senderName . ' - ' . $senderPhone;
                        }
                        $customer = Customer::create([
                            'wa_number' => $senderPhone,
                            'name' => $formattedName,
                            'source' => $detectedSource ?: 'Unknown'
                        ]);
                    } elseif ($senderName && (empty($customer->name) || str_starts_with($customer->name, 'WA - '))) {
                        $customer->update(['name' => $senderName . ' - ' . $senderPhone]);
                    }
                    $customerId = $customer->id;
                    $customer->update(['last_chat_at' => now()]);
                }
            }
        }

        if (!$customerId && !$waGroupId) {
            return;
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
        } elseif (isset($data['video'])) {
            $messageType = 'video';
            $mediaUrl = $this->getMediaUrl($data['video']);
            $caption = is_array($data['video']) ? ($data['video']['caption'] ?? '') : ($data['caption'] ?? '');
            $content = "[VIDEO:{$mediaUrl}]" . ($caption ? " {$caption}" : "");
        } elseif (isset($data['audio']) || isset($data['voice'])) {
            $messageType = 'audio';
            $audioData = $data['audio'] ?? $data['voice'];
            $mediaUrl = $this->getMediaUrl($audioData);
            $content = "[AUDIO:{$mediaUrl}]";
        } elseif (isset($data['sticker'])) {
            $messageType = 'sticker';
            $mediaUrl = $this->getMediaUrl($data['sticker']);
            $content = "[STICKER:{$mediaUrl}]";
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

        $waTimestamp = null;
        if (isset($data['timestamp'])) {
            $ts = is_numeric($data['timestamp']) ? (int)$data['timestamp'] : strtotime($data['timestamp']);
            if ($ts > 0) $waTimestamp = $ts;
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
            'wa_timestamp' => $waTimestamp ?: time(),
            'reply_message_id' => $replyMessageId,
            'reply_content' => $replyContent,
            'reply_sender_name' => $replySenderName,
            'status' => $fromMe ? 'sent' : 'unread',
            'is_external_reply' => (bool)$fromMe,
            'user_id' => null,
            'created_at' => now(),
        ]);

        // 8. Auto Reply (only for individual incoming messages)
        if (!$isGroup && !$fromMe && $customer && Setting::get('auto_reply_enabled') == '1') {
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
                $msg = Message::where('wa_message_id', $id)->first();
                
                if (!$msg) {
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
                                $msg = $pending;
                            }
                        }
                    }
                }
                
                if ($msg) {
                    $statusWeight = ['pending' => 0, 'unread' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3, 'played' => 4];
                    $currentWeight = $statusWeight[$msg->status] ?? -1;
                    $newWeight = $statusWeight[$status] ?? -1;

                    // Prevent downgrading status (e.g., read -> delivered)
                    if ($newWeight >= $currentWeight || $currentWeight === -1) {
                        $msg->update(['status' => $status]);
                    }
                }
            }
        }
    }

    protected function handleConnectionUpdate($data, $eventType)
    {
        $status = strtolower($data['status'] ?? $data['state'] ?? $eventType ?? '');
        $isConnected = in_array($status, ['connected', 'open', 'authenticated', 'ready', 'online']);

        if (in_array($status, ['disconnected', 'close', 'closed', 'logged_out', 'logout', 'unpaired'])) {
            $isConnected = false;
        }

        if (!empty($data['device_id']) && $isConnected) {
            Setting::set('gowa_paired_jid', $data['device_id']);
        }

        $details = [
            'session' => $data['device_id'] ?? Setting::get('gowa_device_id', 'crm-session'),
            'reason' => $data['reason'] ?? $data['message'] ?? "Event Webhook: {$eventType} ({$status})"
        ];

        \App\Services\TelegramService::evaluateAndNotifyWaStatus($isConnected, $details);
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
        
        $baseUrl = rtrim(Setting::get('gowa_api_url', 'https://wag.nams.my.id'), '/');
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

    protected function handleLabelEdit($data)
    {
        $labelId = $data['label_id'] ?? ($data['payload']['label_id'] ?? null);
        if (!$labelId) {
            return;
        }

        $name = $data['name'] ?? ($data['payload']['name'] ?? null);
        $deleted = $data['deleted'] ?? ($data['payload']['deleted'] ?? false);
        $color = $data['color'] ?? ($data['payload']['color'] ?? null);
        $orderIndex = $data['order_index'] ?? ($data['payload']['order_index'] ?? 0);
        $predefinedId = $data['predefined_id'] ?? ($data['payload']['predefined_id'] ?? null);
        $isActive = $data['is_active'] ?? ($data['payload']['is_active'] ?? true);

        if ($deleted) {
            $label = Label::where('wa_label_id', (string) $labelId)->first();
            if ($label) {
                // Detach from all customers and delete
                $label->customers()->detach();
                $label->delete();
                Log::info("WhatsApp Label deleted via webhook: ID={$labelId} ({$label->name})");
            }
            return;
        }

        if (empty($name)) {
            $existing = Label::where('wa_label_id', (string) $labelId)->first();
            $name = $existing ? $existing->name : ("Label #" . $labelId);
        }

        $hexColor = Label::colorFromIndex($color);

        $label = Label::updateOrCreate(
            ['wa_label_id' => (string) $labelId],
            [
                'name' => $name,
                'color' => $hexColor,
                'is_active' => (bool) $isActive,
                'order_index' => (int) $orderIndex,
                'predefined_id' => $predefinedId ? (string) $predefinedId : null,
            ]
        );

        Log::info("WhatsApp Label saved/updated via webhook: ID={$label->id}, WA_ID={$labelId}, Name={$name}, Color={$hexColor}");
    }

    protected function handleLabelAssociation($data)
    {
        $labelId = $data['label_id'] ?? ($data['payload']['label_id'] ?? null);
        $chatId = $data['chat_id'] ?? ($data['payload']['chat_id'] ?? null);
        $labeled = $data['labeled'] ?? ($data['payload']['labeled'] ?? null);

        if (!$labelId || !$chatId) {
            return;
        }

        // 1. Find or create the Label
        $label = Label::where('wa_label_id', (string) $labelId)->first();
        if (!$label) {
            $label = Label::create([
                'wa_label_id' => (string) $labelId,
                'name' => 'Label #' . $labelId,
                'color' => '#00a884',
                'is_active' => true,
            ]);
        }

        // 2. Resolve Customer from chat_id
        if (str_contains($chatId, '@g.us')) {
            Log::info("Label association received for group: {$chatId}, label={$labelId}");
            return;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', explode('@', $chatId)[0]);
        if (empty($cleanPhone)) {
            return;
        }

        $customer = Customer::where('wa_number', $cleanPhone)->first();
        if (!$customer) {
            $customer = Customer::create([
                'wa_number' => $cleanPhone,
                'name' => 'WA - ' . $cleanPhone,
                'source' => 'WhatsApp',
            ]);
        }

        // 3. Attach or Detach
        if ($labeled === true || $labeled === 1 || $labeled === 'true') {
            $customer->labels()->syncWithoutDetaching([$label->id]);
            Log::info("Attached label {$label->name} (WA: {$labelId}) to customer {$cleanPhone}");
        } elseif ($labeled === false || $labeled === 0 || $labeled === 'false') {
            $customer->labels()->detach($label->id);
            Log::info("Detached label {$label->name} (WA: {$labelId}) from customer {$cleanPhone}");
        }
    }
}

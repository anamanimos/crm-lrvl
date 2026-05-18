<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use App\Models\Message;
use App\Services\WaGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Setting;

class ProcessBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $broadcast;

    public function __construct(Broadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    public function handle(WaGateway $waGateway): void
    {
        // Reload broadcast to get latest status
        $broadcast = $this->broadcast->fresh();

        if (!$broadcast || $broadcast->status != 'running') {
            Log::info("Broadcast Job Stopped: ID " . ($broadcast->id ?? 'unknown') . " status is " . ($broadcast->status ?? 'deleted'));
            return;
        }

        $recipient = BroadcastRecipient::where('broadcast_id', $broadcast->id)
            ->where('status', 'pending')
            ->first();

        if (!$recipient) {
            $broadcast->update(['status' => 'completed', 'completed_at' => now()]);
            Log::info("Broadcast {$broadcast->id} completed.");
            return;
        }

        // Rate Limiting Checks (Using Global Settings)
        $maxPerDay = (int) Setting::get('broadcast_max_per_day', 0);
        if ($maxPerDay > 0) {
            $sentThisDay = BroadcastRecipient::where('status', 'sent')
                ->whereNotNull('sent_at')
                ->where('sent_at', '>=', now()->startOfDay())
                ->count();
                
            if ($sentThisDay >= $maxPerDay) {
                $nextDay = now()->addDay()->startOfDay();
                Log::info("Global max_per_day ({$maxPerDay}) reached for Broadcast {$broadcast->id}. Delaying to {$nextDay}");
                ProcessBroadcastJob::dispatch($broadcast)->delay($nextDay);
                return;
            }
        }

        $maxPerHour = (int) Setting::get('broadcast_max_per_hour', 0);
        if ($maxPerHour > 0) {
            $sentThisHour = BroadcastRecipient::where('status', 'sent')
                ->whereNotNull('sent_at')
                ->where('sent_at', '>=', now()->startOfHour())
                ->count();
                
            if ($sentThisHour >= $maxPerHour) {
                $nextHour = now()->addHour()->startOfHour();
                Log::info("Global max_per_hour ({$maxPerHour}) reached for Broadcast {$broadcast->id}. Delaying to {$nextHour}");
                ProcessBroadcastJob::dispatch($broadcast)->delay($nextHour);
                return;
            }
        }

        try {
            $customer = $recipient->customer;
            
            // Check if template is a JSON array
            $templates = json_decode($broadcast->message_template, true);
            if (is_array($templates) && count($templates) > 0) {
                $selectedTemplate = $templates[array_rand($templates)];
            } else {
                $selectedTemplate = $broadcast->message_template;
            }

            // Process Spintax
            $message = $this->processSpintax($selectedTemplate);
            
            // Replace Variables
            $message = str_replace('{name}', $customer->name, $message);
            $message = str_replace('{wa_number}', $customer->wa_number, $message);

            $mediaSent = false;
            $waMessageId = null;

            if (!empty($broadcast->media_path)) {
                $mediaUrl = $broadcast->media_path;
                if (!Str::startsWith($mediaUrl, ['http://', 'https://'])) {
                    $mediaUrl = asset('storage/' . $mediaUrl);
                }

                try {
                    if ($broadcast->media_type === 'image') {
                        $result = $waGateway->sendImageUrl($customer->wa_number, $mediaUrl, $message);
                    } else {
                        $result = $waGateway->sendDocumentUrl($customer->wa_number, $mediaUrl, basename($mediaUrl), $message);
                    }
                    $mediaSent = true;
                    $waMessageId = $result['data']['results']['message_id'] ?? null;
                } catch (\Exception $e) {
                    Log::error("Broadcast Media Send Failed: " . $e->getMessage());
                }
            }

            if (!$mediaSent) {
                $result = $waGateway->sendMessage($customer->wa_number, $message);
                $waMessageId = $result['data']['results']['message_id'] ?? null;
            }

            $recipient->update(['status' => 'sent', 'sent_at' => now()]);

            // Record Message
            $msgContent = $message;
            if ($mediaSent) {
                $mediaUrl = $broadcast->media_path;
                if (!Str::startsWith($mediaUrl, ['http://', 'https://'])) {
                    $mediaUrl = asset('storage/' . $mediaUrl);
                }
                $msgContent = "[" . strtoupper($broadcast->media_type) . ":{$mediaUrl}] " . $message;
            }

            Message::create([
                'customer_id' => $customer->id,
                'user_id' => $broadcast->created_by,
                'content' => $msgContent,
                'direction' => 'out',
                'type' => $mediaSent ? $broadcast->media_type : 'text',
                'media_url' => $mediaSent ? $broadcast->media_path : null,
                'status' => 'sent',
                'wa_message_id' => $waMessageId,
                'wa_timestamp' => time(),
            ]);

            // Update customer last_chat_at so it moves to top in chat list
            if ($customer) {
                $customer->update(['last_chat_at' => now()]);
            }

            // Dispatch next job with random delay
            $delay = rand($broadcast->delay_min, $broadcast->delay_max);
            ProcessBroadcastJob::dispatch($broadcast)->delay(now()->addSeconds($delay));

        } catch (\Exception $e) {
            Log::error("Broadcast Job Error (ID: {$broadcast->id}): " . $e->getMessage());
            $recipient->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            
            // Continue to next even on failure
            $delay = rand($broadcast->delay_min, $broadcast->delay_max);
            ProcessBroadcastJob::dispatch($broadcast)->delay(now()->addSeconds($delay));
        }
    }

    private function processSpintax($text)
    {
        return preg_replace_callback('/\{([^{}]+)\}/', function($matches) {
            $options = explode('|', $matches[1]);
            if (count($options) == 1) return '{' . $options[0] . '}';
            return $options[rand(0, count($options) - 1)];
        }, $text);
    }
}

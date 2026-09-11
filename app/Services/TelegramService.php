<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Get default or configured bot token
     */
    public static function getBotToken($customToken = null)
    {
        if (!empty($customToken)) {
            return $customToken;
        }

        $token = Setting::get('telegram_wa_bot_token');
        if (empty($token)) {
            $token = Setting::get('telegram_bot_token');
        }

        return $token;
    }

    /**
     * Get default or configured chat ID
     */
    public static function getChatId($customChatId = null)
    {
        if (!empty($customChatId)) {
            return $customChatId;
        }

        $chatId = Setting::get('telegram_wa_chat_id');
        if (empty($chatId)) {
            $chatId = Setting::get('telegram_chat_id');
        }

        return $chatId;
    }

    /**
     * Check if WhatsApp status alerts to Telegram are enabled
     */
    public static function isAlertEnabled()
    {
        return Setting::get('telegram_wa_alert_enabled', '0') == '1';
    }

    /**
     * Send arbitrary message to Telegram
     */
    public static function sendMessage($message, $token = null, $chatId = null, $parseMode = 'HTML')
    {
        $botToken = self::getBotToken($token);
        $targetChatId = self::getChatId($chatId);

        if (empty($botToken) || empty($targetChatId)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token atau Chat ID belum dikonfigurasi.'
            ];
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            $response = Http::withoutVerifying()->timeout(10)->post($url, [
                'chat_id' => $targetChatId,
                'text' => $message,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Pesan Telegram berhasil dikirim.',
                    'data' => $response->json()
                ];
            }

            $errorMsg = $response->json()['description'] ?? $response->body();
            Log::warning("Telegram API Error ({$response->status()}): {$errorMsg}");

            return [
                'success' => false,
                'message' => 'Gagal mengirim ke Telegram: ' . $errorMsg
            ];
        } catch (\Exception $e) {
            Log::error('Telegram Service Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Koneksi ke Telegram gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Evaluate WA status and trigger notification if state transitioned
     *
     * @param bool $isConnected Current connection state
     * @param array $details Extra details from gateway or webhook
     * @return array
     */
    public static function evaluateAndNotifyWaStatus($isConnected, array $details = [])
    {
        if (!self::isAlertEnabled()) {
            return ['status' => 'disabled', 'notified' => false];
        }

        $lastStatus = Setting::get('telegram_last_wa_status', null);
        $currentStatus = $isConnected ? 'connected' : 'disconnected';

        $session = Setting::get('gowa_device_id', 'crm-session');
        $gatewayUrl = Setting::get('gowa_api_url', 'https://wag.nams.my.id');
        $details = array_merge([
            'session' => $session,
            'gateway_url' => $gatewayUrl,
            'time' => now()->translatedFormat('d M Y, H:i') . ' WIB'
        ], $details);

        // If no prior record exists, initialize without alert unless disconnected
        if ($lastStatus === null) {
            Setting::set('telegram_last_wa_status', $currentStatus, 'Status koneksi terakhir WhatsApp untuk notifikasi');
            return ['status' => 'initialized', 'current' => $currentStatus, 'notified' => false];
        }

        // Check for state change
        if ($lastStatus !== $currentStatus) {
            Setting::set('telegram_last_wa_status', $currentStatus, 'Status koneksi terakhir WhatsApp untuk notifikasi');

            if ($currentStatus === 'disconnected') {
                $notifyDisconnect = Setting::get('telegram_wa_notify_disconnect', '1') == '1';
                if ($notifyDisconnect) {
                    $res = self::sendWaDisconnectedAlert($details);
                    return ['status' => 'changed', 'current' => $currentStatus, 'notified' => true, 'result' => $res];
                }
            } elseif ($currentStatus === 'connected') {
                $notifyConnect = Setting::get('telegram_wa_notify_connect', '1') == '1';
                if ($notifyConnect) {
                    $res = self::sendWaConnectedAlert($details);
                    return ['status' => 'changed', 'current' => $currentStatus, 'notified' => true, 'result' => $res];
                }
            }
        }

        return ['status' => 'unchanged', 'current' => $currentStatus, 'notified' => false];
    }

    /**
     * Send Disconnected Alert
     */
    public static function sendWaDisconnectedAlert(array $details = [])
    {
        $session = $details['session'] ?? Setting::get('gowa_device_id', 'crm-session');
        $gatewayUrl = $details['gateway_url'] ?? Setting::get('gowa_api_url', 'https://wag.nams.my.id');
        $time = $details['time'] ?? (now()->translatedFormat('d M Y, H:i') . ' WIB');
        $reason = $details['reason'] ?? 'Sesi WhatsApp tidak aktif atau perangkat telah terputus (logged out/disconnected).';

        $msg = "🔴 <b>PERINGATAN: KONEKSI WHATSAPP TERPUTUS!</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📱 <b>Sesi Perangkat:</b> <code>{$session}</code>\n";
        $msg .= "🌐 <b>Gateway:</b> <code>{$gatewayUrl}</code>\n";
        $msg .= "⚠️ <b>Status:</b> <b>TERPUTUS (Disconnected)</b>\n";
        $msg .= "🕒 <b>Waktu:</b> <code>{$time}</code>\n\n";
        $msg .= "<i>Keterangan: {$reason}</i>\n\n";
        $msg .= "👉 <i>Harap segera buka dashboard CRM di menu <b>Pengaturan > WhatsApp</b> untuk menautkan ulang perangkat.</i>";

        return self::sendMessage($msg);
    }

    /**
     * Send Connected / Reconnected Alert
     */
    public static function sendWaConnectedAlert(array $details = [])
    {
        $session = $details['session'] ?? Setting::get('gowa_device_id', 'crm-session');
        $gatewayUrl = $details['gateway_url'] ?? Setting::get('gowa_api_url', 'https://wag.nams.my.id');
        $time = $details['time'] ?? (now()->translatedFormat('d M Y, H:i') . ' WIB');
        $userNumber = $details['number'] ?? $details['user'] ?? '';

        $msg = "🟢 <b>INFORMASI: WHATSAPP TERHUBUNG KEMBALI</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📱 <b>Sesi Perangkat:</b> <code>{$session}</code>\n";
        if (!empty($userNumber)) {
            $msg .= "📞 <b>Nomor WA:</b> <code>{$userNumber}</code>\n";
        }
        $msg .= "🌐 <b>Gateway:</b> <code>{$gatewayUrl}</code>\n";
        $msg .= "✅ <b>Status:</b> <b>TERHUBUNG & AKTIF (Connected)</b>\n";
        $msg .= "🕒 <b>Waktu:</b> <code>{$time}</code>\n\n";
        $msg .= "<i>Keterangan: Koneksi WhatsApp Gateway telah pulih dan siap mengirim serta menerima pesan pelanggan.</i>";

        return self::sendMessage($msg);
    }

    /**
     * Send Test Notification
     */
    public static function testNotification($token = null, $chatId = null)
    {
        $session = Setting::get('gowa_device_id', 'crm-session');
        $time = now()->translatedFormat('d M Y, H:i:s') . ' WIB';

        $msg = "🔔 <b>TEST NOTIFIKASI TELEGRAM CRM</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "✅ <b>Status:</b> Konfigurasi Bot Telegram Berhasil!\n";
        $msg .= "📱 <b>Sesi WhatsApp:</b> <code>{$session}</code>\n";
        $msg .= "🕒 <b>Waktu:</b> <code>{$time}</code>\n\n";
        $msg .= "<i>Sistem siap mengirimkan notifikasi otomatis saat status koneksi WhatsApp terputus atau terhubung kembali.</i>";

        return self::sendMessage($msg, $token, $chatId);
    }
}

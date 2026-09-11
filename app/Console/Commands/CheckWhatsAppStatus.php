<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckWhatsAppStatus extends Command
{
    protected $signature = 'wa:check-status';
    protected $description = 'Periksa status koneksi WhatsApp Gateway dan kirim notifikasi ke Telegram jika status berubah';

    public function handle()
    {
        $deviceId = Setting::get('gowa_device_id', 'crm-session');
        $baseUrl = rtrim(Setting::get('gowa_api_url', 'https://wag.nams.my.id'), '/');
        $username = Setting::get('gowa_username', '');
        $password = Setting::get('gowa_password', '');

        $this->info("Memeriksa status WhatsApp untuk sesi: {$deviceId} di {$baseUrl}...");

        $url = $baseUrl . '/devices/' . $deviceId . '/status';

        try {
            $client = Http::withoutVerifying()->timeout(10)->withHeaders([
                'X-Device-Id' => $deviceId,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]);

            if (!empty($username)) {
                $client->withBasicAuth($username, $password);
            }

            $response = $client->get($url);

            $isConnected = false;
            $details = [
                'session' => $deviceId,
                'gateway_url' => $baseUrl,
            ];

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['results'] ?? ($data['data']['results'] ?? ($data['data'] ?? []));

                if (isset($results['is_connected']) && isset($results['is_logged_in'])) {
                    $isConnected = (bool) ($results['is_connected'] && $results['is_logged_in']);
                } elseif (isset($data['status']) && ($data['status'] === 'connected' || $data['status'] === 'ready')) {
                    $isConnected = true;
                }

                if (isset($results['device_id'])) {
                    $details['user'] = $results['device_id'];
                }
            } else {
                $details['reason'] = 'Gateway server mengembalikan HTTP ' . $response->status() . ': ' . $response->body();
            }

            $res = TelegramService::evaluateAndNotifyWaStatus($isConnected, $details);

            $statusText = $isConnected ? 'TERHUBUNG (Connected)' : 'TERPUTUS (Disconnected)';
            $this->info("Status saat ini: {$statusText} | Notifikasi Telegram: " . ($res['notified'] ? 'Terkirim' : 'Tidak dikirim (Status sama / Dinonaktifkan)'));

            return 0;
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke WhatsApp Gateway: " . $e->getMessage());
            Log::error("wa:check-status exception: " . $e->getMessage());

            TelegramService::evaluateAndNotifyWaStatus(false, [
                'session' => $deviceId,
                'gateway_url' => $baseUrl,
                'reason' => 'Koneksi ke gateway error/timeout: ' . $e->getMessage()
            ]);

            return 1;
        }
    }
}

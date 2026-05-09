<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Traits\HasCloudinary;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use App\Models\Message;

class SyncWhatsAppMedia extends Command
{
    use HasCloudinary;

    protected $signature = 'wa:sync-media {--limit=10 : Jumlah media yang disinkronkan sekaligus} {--ids= : ID pesan yang dipisahkan koma untuk disinkronkan spesifik}';
    protected $description = 'Sinkronisasi media dari WhatsApp ke Cloud Storage (Cloudinary/MinIO)';

    public function handle()
    {
        $limit = $this->option('limit');
        $ids = $this->option('ids');
        $settings = Setting::getAll();
        
        $cloudinaryEnabled = ($settings['cloudinary_enabled'] ?? '0') == '1';
        $minioEnabled = ($settings['minio_enabled'] ?? '0') == '1';

        if (!$cloudinaryEnabled && !$minioEnabled) {
            $this->error('Cloud Storage tidak aktif. Silakan aktifkan Cloudinary atau MinIO di pengaturan.');
            return;
        }

        $query = Message::whereNotNull('media_url');

        if ($ids) {
            $idArray = explode(',', $ids);
            $query->whereIn('id', $idArray);
        } else {
            $query->where(function($q) {
                $q->whereNull('media_status')->orWhere('media_status', '!=', 'uploaded');
            })->limit($limit);
        }

        $messages = $query->get();

        if ($messages->isEmpty()) {
            $this->info('Tidak ada media yang perlu disinkronkan.');
            return;
        }

        $this->info("Memulai sinkronisasi " . $messages->count() . " media...");

        foreach ($messages as $message) {
            $this->line("Memproses media untuk pesan: {$message->wa_message_id}...");
            
            try {
                $content = null;
                $contentType = null;

                if (\Illuminate\Support\Str::startsWith($message->media_url, ['http://', 'https://'])) {
                    // Download from Remote URL
                    $response = Http::get($message->media_url);
                    if (!$response->successful()) {
                        throw new \Exception("Gagal mengunduh media dari remote: " . $response->status());
                    }
                    $content = $response->body();
                    $contentType = $response->header('Content-Type');
                } else {
                    // Read from Local Path
                    $localPath = public_path($message->media_url);
                    if (!file_exists($localPath)) {
                        // Try storage path if public_path fails
                        $localPath = storage_path('app/public/' . $message->media_url);
                    }

                    if (!file_exists($localPath)) {
                        throw new \Exception("File lokal tidak ditemukan: " . $message->media_url);
                    }
                    $content = file_get_contents($localPath);
                    $contentType = mime_content_type($localPath);
                }

                $extension = $this->getExtensionFromMime($contentType);
                $filename = 'wa_' . ($message->wa_message_id ?: $message->id) . '.' . $extension;
                
                $uploadResult = null;
                if ($cloudinaryEnabled) {
                    $uploadResult = $this->uploadToCloudinary($content, $filename);
                } elseif ($minioEnabled) {
                    $uploadResult = $this->uploadToMinio($content, $filename, $settings);
                }

                if ($uploadResult) {
                    $message->update([
                        'media_status' => 'uploaded',
                        'media_path' => $uploadResult['url'],
                        'media_uploaded_at' => now(),
                        'media_last_error' => null
                    ]);
                    $this->info("Berhasil sinkron: {$filename}");
                }
            } catch (\Exception $e) {
                $message->update([
                    'media_status' => 'failed',
                    'media_last_error' => $e->getMessage(),
                    'media_attempts' => $message->media_attempts + 1
                ]);
                $this->error("Gagal sinkron {$message->wa_message_id}: " . $e->getMessage());
            }
        }

        $this->info('Sinkronisasi selesai.');
    }

    private function getExtensionFromMime($mime)
    {
        $mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'video/mp4' => 'mp4',
            'audio/mpeg' => 'mp3',
            'application/pdf' => 'pdf'
        ];
        return $mimes[$mime] ?? 'bin';
    }

    private function uploadToMinio($content, $filename, $settings)
    {
        try {
            $path = 'whatsapp/' . $filename;
            Storage::disk('s3')->put($path, $content);
            return ['url' => Storage::disk('s3')->url($path)];
        } catch (\Exception $e) {
            throw new \Exception("MinIO Upload Error: " . $e->getMessage());
        }
    }
}

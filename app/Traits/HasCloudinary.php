<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait HasCloudinary
{
    /**
     * Upload a file to Cloudinary
     * 
     * @param \Illuminate\Http\UploadedFile|string $file Content or File object
     * @param string $filename
     * @param string|null $customFolder
     * @return array|null [public_id, url]
     */
    public function uploadToCloudinary($file, $filename = null, $customFolder = null)
    {
        $settings = Setting::getAll();
        
        $enabled = ($settings['cloudinary_enabled'] ?? '0') == '1';
        $cloudName = $settings['cloudinary_cloud_name'] ?? '';
        $apiKey = $settings['cloudinary_api_key'] ?? '';
        $apiSecret = $settings['cloudinary_api_secret'] ?? '';
        $folder = $customFolder ?: ($settings['cloudinary_folder'] ?? 'crm');

        if (!$enabled || empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            \Log::info('Cloudinary disabled or missing config', [
                'enabled' => $enabled,
                'has_cloud' => !empty($cloudName),
                'has_key' => !empty($apiKey),
                'has_secret' => !empty($apiSecret)
            ]);
            return null;
        }

        $timestamp = time();
        
        // Prepare signature params
        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp
        ];
        ksort($params);
        
        $stringToSign = "";
        foreach($params as $key => $value) {
            $stringToSign .= ($stringToSign ? "&" : "") . "{$key}={$value}";
        }
        $signature = sha1($stringToSign . $apiSecret);

        \Log::info('Cloudinary Upload Attempt', [
            'folder' => $folder,
            'timestamp' => $timestamp,
            'string_to_sign' => $stringToSign,
            'signature' => $signature
        ]);

        // Prepare Http Request
        $request = Http::asMultipart();
        
        if (is_string($file)) {
            $request->attach('file', $file, $filename ?: 'file_' . time());
        } else {
            // Use getPathname() first (more reliable), fallback to getRealPath()
            $filePath = $file->getPathname() ?: $file->getRealPath();
            if (!$filePath || !file_exists($filePath)) {
                // Last resort: read file content directly
                try {
                    $content = file_get_contents($file->getPathname());
                    $request->attach('file', $content, $file->getClientOriginalName());
                } catch (\Exception $e) {
                    throw new \Exception("Gagal membaca file: Path file tidak dapat diakses.");
                }
            } else {
                $request->attach('file', fopen($filePath, 'r'), $file->getClientOriginalName());
            }
        }

        $response = $request->post("https://api.cloudinary.com/v1_1/{$cloudName}/auto/upload", [
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder
        ]);

        if ($response->successful()) {
            return [
                'public_id' => $response->json('public_id'),
                'url' => $response->json('secure_url')
            ];
        }

        throw new \Exception("Cloudinary Upload Error: " . ($response->json('error.message') ?: $response->body()));
    }

    /**
     * Delete a file from Cloudinary
     * 
     * @param string $publicId
     * @return bool
     */
    public function deleteFromCloudinary($publicId)
    {
        if (empty($publicId)) return false;

        $settings = Setting::getAll();
        $cloudName = $settings['cloudinary_cloud_name'] ?? '';
        $apiKey = $settings['cloudinary_api_key'] ?? '';
        $apiSecret = $settings['cloudinary_api_secret'] ?? '';

        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            return false;
        }

        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp
        ];
        ksort($params);
        
        $stringToSign = "";
        foreach($params as $key => $value) {
            $stringToSign .= ($stringToSign ? "&" : "") . "{$key}={$value}";
        }
        $signature = sha1($stringToSign . $apiSecret);

        $response = Http::asMultipart()->post("https://api.cloudinary.com/v1_1/{$cloudName}/auto/destroy", [
            'public_id' => $publicId,
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature
        ]);

        return $response->successful() && $response->json('result') === 'ok';
    }

    /**
     * Extract public ID from Cloudinary URL
     * 
     * @param string $url
     * @return string|null
     */
    public function extractPublicId($url)
    {
        if (empty($url) || !Str::contains($url, 'cloudinary.com')) return null;

        // Pattern: .../upload/v12345678/folder/public_id.ext
        $parts = explode('/upload/', $url);
        if (count($parts) < 2) return null;

        $path = $parts[1];
        // Remove version if exists (v12345678/)
        if (preg_match('/^v\d+\//', $path)) {
            $path = preg_replace('/^v\d+\//', '', $path);
        }

        // Remove extension
        $pathParts = explode('.', $path);
        array_pop($pathParts);
        return implode('.', $pathParts);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request, $section = 'general', $subsection = null)
    {
        $settings = Setting::getAll();
        
        $sections = [
            'general' => 'Umum',
            'whatsapp' => 'WhatsApp Gateway',
            'erp' => 'Sistem ERP',
            'katalog' => 'Katalog Produk',
            'google' => 'Google Contact',
            'ai' => 'AI Assistant',
            'storage' => 'Cloud Storage',
            'backup' => 'Backup & Maintenance'
        ];

        if (!array_key_exists($section, $sections)) {
            return redirect()->route('settings.section', 'general');
        }

        // Default subsection for whatsapp
        if ($section == 'whatsapp' && empty($subsection)) {
            $subsection = 'koneksi';
        }

        $data = compact('settings', 'section', 'sections', 'subsection');

        if ($section == 'whatsapp' && $subsection == 'webhook') {
            $query = \App\Models\WebhookLog::query();

            // Filters
            if ($request->has('category')) {
                $categories = (array) $request->input('category');
                $query->whereIn('category', $categories);
            }
            if ($request->has('event_type')) {
                $types = (array) $request->input('event_type');
                $query->whereIn('event_type', $types);
            }
            if ($request->has('processed') && $request->input('processed') !== '') {
                $status = (array) $request->input('processed');
                $status = array_map('intval', $status);
                $query->whereIn('processed', $status);
            }
            if ($request->has('date_from')) $query->whereDate('created_at', '>=', $request->input('date_from'));
            if ($request->has('date_to')) $query->whereDate('created_at', '<=', $request->input('date_to'));
            
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('from_number', 'like', '%' . $search . '%')
                      ->orWhere('message_id', 'like', '%' . $search . '%');
                });
            }

            // Sorting
            $orderBy = $request->input('order_by', 'created_at');
            $orderDir = $request->input('order_dir', 'desc');
            $query->orderBy($orderBy, $orderDir);

            // Pagination
            $perPage = $request->input('per_page', 10);
            $logs = $query->paginate($perPage)->withQueryString();
            $data['logs'] = $logs;
            $data['categories'] = [
                'message_incoming' => 'Pesan Masuk',
                'message_outgoing' => 'Pesan Keluar',
                'status_update' => 'Update Status',
                'media' => 'Media',
                'group_event' => 'Grup',
                'connection' => 'Koneksi',
                'unknown' => 'Lainnya'
            ];
            $data['event_types'] = \App\Models\WebhookLog::distinct()->pluck('event_type')->filter()->toArray();
        }

        if ($section == 'whatsapp' && $subsection == 'storage_sync') {
            $data['stats'] = [
                'total' => \App\Models\Message::whereNotNull('media_url')->count(),
                'synced' => \App\Models\Message::where('media_status', 'uploaded')->count(),
                'pending' => \App\Models\Message::whereNotNull('media_url')->where(function($q) {
                    $q->whereNull('media_status')->orWhere('media_status', 'pending');
                })->count(),
                'failed' => \App\Models\Message::where('media_status', 'failed')->count(),
            ];

            $query = \App\Models\Message::whereNotNull('media_url');

            // Filters
            if ($request->has('media_status')) {
                $status = (array) $request->input('media_status');
                $query->whereIn('media_status', $status);
            }
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where('wa_message_id', 'like', '%' . $search . '%');
            }

            // Sorting
            $orderBy = $request->input('order_by', 'updated_at');
            $orderDir = $request->input('order_dir', 'desc');
            $query->orderBy($orderBy, $orderDir);

            // Pagination
            $perPage = $request->input('per_page', 10);
            $logs = $query->paginate($perPage)->withQueryString();
            $data['logs'] = $logs;

            if ($request->ajax()) {
                return view('settings.sections.whatsapp.storage_sync_table', $data);
            }
        }

        if ($request->ajax()) {
            return view('settings.sections.whatsapp.webhook_table', $data);
        }

        return view('settings.index', $data);
    }

    public function store(Request $request)
    {
        $section = $request->input('section', 'general');
        $settings = $request->except('_token', 'section');
        
        $sections = [
            'general' => 'Umum',
            'whatsapp' => 'WhatsApp Gateway',
            'erp' => 'Sistem ERP',
            'katalog' => 'Katalog Produk',
            'google' => 'Google Contact',
            'ai' => 'AI Assistant',
            'storage' => 'Cloud Storage',
            'backup' => 'Backup & Maintenance'
        ];

        // Handle checkboxes (switches) that are not sent when unchecked
        if ($section == 'storage') {
            if (!$request->has('cloudinary_enabled')) $settings['cloudinary_enabled'] = '0';
            if (!$request->has('minio_enabled')) $settings['minio_enabled'] = '0';
        }
        if ($section == 'google' && !$request->has('google_sync_enabled')) {
            $settings['google_sync_enabled'] = '0';
        }
        if ($section == 'whatsapp' && !$request->has('webhook_forward_enabled')) {
            $settings['webhook_forward_enabled'] = '0';
        }
        if ($section == 'whatsapp' && !$request->has('auto_reply_enabled')) {
            $settings['auto_reply_enabled'] = '0';
        }
        if ($section == 'backup' && !$request->has('backup_enabled')) {
            $settings['backup_enabled'] = '0';
        }

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value);
        }

        $message = 'Pengaturan ' . ($sections[$section] ?? 'Sistem') . ' berhasil disimpan';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        $subsection = $request->input('subsection');
        $params = ['section' => $section];
        if (!empty($subsection)) $params['subsection'] = $subsection;

        if ($section == 'whatsapp') {
            return redirect()->route('settings.section', $params);
        }

        return redirect()->route('settings.section', $params)->with('success', $message);
    }

    public function testCloudinary(Request $request)
    {
        $cloud_name = $request->get('cloud_name');
        $api_key = $request->get('api_key');
        $api_secret = $request->get('api_secret');

        if (empty($cloud_name) || empty($api_key) || empty($api_secret)) {
            return response()->json(['success' => false, 'message' => 'Cloud Name, API Key, dan API Secret harus diisi']);
        }

        $url = "https://api.cloudinary.com/v1_1/{$cloud_name}/resources/image";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERPWD => "{$api_key}:{$api_secret}"
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return response()->json(['success' => false, 'message' => 'Connection error: ' . $error]);
        }
        
        if ($http_code === 200) {
            return response()->json(['success' => true, 'message' => 'Cloudinary terkoneksi!']);
        } elseif ($http_code === 401) {
            return response()->json(['success' => false, 'message' => 'API credentials tidak valid']);
        } else {
            return response()->json(['success' => false, 'message' => 'Koneksi gagal (HTTP ' . $http_code . ')']);
        }
    }

    public function testErp(Request $request)
    {
        $url = rtrim($request->get('url'), '/');
        $api_key = $request->get('api_key');

        if (empty($url)) {
            return response()->json(['success' => false, 'message' => 'API URL ERP harus diisi']);
        }

        // Test by calling a simple endpoint in ERP, for example /status or just the base URL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $api_key,
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return response()->json(['success' => false, 'message' => 'Connection error: ' . $error]);
        }
        
        // If HTTP code is 200, 401, or 404, it means the server is reachable
        if ($http_code >= 200 && $http_code < 500) {
            if ($http_code === 401) {
                return response()->json(['success' => false, 'message' => 'Koneksi terhubung tapi API Key salah (HTTP 401)']);
            }
            return response()->json(['success' => true, 'message' => 'Koneksi ke ERP berhasil! (HTTP ' . $http_code . ')']);
        } else {
            return response()->json(['success' => false, 'message' => 'ERP tidak dapat dijangkau (HTTP ' . $http_code . ')']);
        }
    }

    public function testMinio(Request $request)
    {
        $endpoint = rtrim($request->get('endpoint'), '/');
        $access_key = $request->get('access_key');
        $secret_key = $request->get('secret_key');

        if (empty($endpoint) || empty($access_key) || empty($secret_key)) {
            return response()->json(['success' => false, 'message' => 'Credentials tidak lengkap']);
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_NOBODY => true
        ]);
        
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return response()->json(['success' => false, 'message' => 'Connection error: ' . $error]);
        }
        
        if ($http_code >= 200 && $http_code < 500) {
            return response()->json(['success' => true, 'message' => 'MinIO terkoneksi!']);
        } else {
            return response()->json(['success' => false, 'message' => 'MinIO tidak dapat dijangkau (HTTP ' . $http_code . ')']);
        }
    }

    public function testUploadCloudinary(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'File upload error']);
        }

        $cloud_name = $request->post('cloud_name');
        $api_key = $request->post('api_key');
        $api_secret = $request->post('api_secret');

        if (empty($cloud_name) || empty($api_key) || empty($api_secret)) {
            return response()->json(['success' => false, 'message' => 'Credentials tidak lengkap']);
        }

        $file = $request->file('file');
        $timestamp = time();
        $folder = 'test';

        $params = ['folder' => $folder, 'timestamp' => $timestamp];
        ksort($params);
        $string_to_sign = '';
        foreach ($params as $key => $value) {
            $string_to_sign .= ($string_to_sign ? '&' : '') . $key . '=' . $value;
        }
        $signature = sha1($string_to_sign . $api_secret);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $file->getClientOriginalName()),
                'api_key' => $api_key,
                'timestamp' => $timestamp,
                'folder' => $folder,
                'signature' => $signature
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($http_code >= 200 && $http_code < 300 && isset($result['secure_url'])) {
            return response()->json(['success' => true, 'message' => 'Upload berhasil', 'data' => ['public_id' => $result['public_id'], 'url' => $result['secure_url']]]);
        } else {
            return response()->json(['success' => false, 'message' => $result['error']['message'] ?? 'Upload gagal']);
        }
    }

    public function testDeleteCloudinary(Request $request)
    {
        $public_id = $request->post('public_id');
        $cloud_name = $request->post('cloud_name');
        $api_key = $request->post('api_key');
        $api_secret = $request->post('api_secret');

        if (empty($public_id) || empty($cloud_name) || empty($api_key) || empty($api_secret)) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        $timestamp = time();
        $params = ['public_id' => $public_id, 'timestamp' => $timestamp];
        ksort($params);
        $string_to_sign = '';
        foreach ($params as $key => $value) {
            $string_to_sign .= ($string_to_sign ? '&' : '') . $key . '=' . $value;
        }
        $signature = sha1($string_to_sign . $api_secret);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.cloudinary.com/v1_1/{$cloud_name}/image/destroy",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'public_id' => $public_id,
                'api_key' => $api_key,
                'timestamp' => $timestamp,
                'signature' => $signature
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        if (($result['result'] ?? '') === 'ok') {
            return response()->json(['success' => true, 'message' => 'File berhasil dihapus']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus file']);
        }
    }

    public function testUploadMinio(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'File upload error']);
        }

        $endpoint = rtrim($request->post('endpoint'), '/');
        $access_key = $request->post('access_key');
        $secret_key = $request->post('secret_key');
        $bucket = $request->post('bucket') ?: 'crm';
        $region = $request->post('region') ?: 'us-east-1';

        if (empty($endpoint) || empty($access_key) || empty($secret_key)) {
            return response()->json(['success' => false, 'message' => 'Credentials tidak lengkap']);
        }

        $file = $request->file('file');
        $file_content = file_get_contents($file->getRealPath());
        $object_name = 'test/' . time() . '_' . $file->getClientOriginalName();
        
        // AWS Signature V4 Implementation (simplified for S3-compatible like MinIO)
        $date = gmdate('Ymd\THis\Z');
        $date_short = gmdate('Ymd');
        $parsed = parse_url($endpoint);
        $host = $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
        $uri = '/' . $bucket . '/' . $object_name;
        $payload_hash = hash('sha256', $file_content);

        $headers = [
            'content-length' => strlen($file_content),
            'content-type' => $file->getMimeType(),
            'host' => $host,
            'x-amz-acl' => 'public-read',
            'x-amz-content-sha256' => $payload_hash,
            'x-amz-date' => $date
        ];
        ksort($headers);

        $canonical_headers = '';
        $signed_headers = [];
        foreach ($headers as $key => $value) {
            $canonical_headers .= strtolower($key) . ':' . trim($value) . "\n";
            $signed_headers[] = strtolower($key);
        }
        $signed_headers_str = implode(';', $signed_headers);

        $canonical_request = "PUT\n{$uri}\n\n{$canonical_headers}\n{$signed_headers_str}\n{$payload_hash}";
        $scope = "{$date_short}/{$region}/s3/aws4_request";
        $string_to_sign = "AWS4-HMAC-SHA256\n{$date}\n{$scope}\n" . hash('sha256', $canonical_request);

        $k_date = hash_hmac('sha256', $date_short, 'AWS4' . $secret_key, true);
        $k_region = hash_hmac('sha256', $region, $k_date, true);
        $k_service = hash_hmac('sha256', 's3', $k_region, true);
        $k_signing = hash_hmac('sha256', 'aws4_request', $k_service, true);
        $signature = hash_hmac('sha256', $string_to_sign, $k_signing);

        $authorization = "AWS4-HMAC-SHA256 Credential={$access_key}/{$scope}, SignedHeaders={$signed_headers_str}, Signature={$signature}";

        $ch = curl_init($endpoint . $uri);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $file_content,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $authorization,
                'Content-Type: ' . $file->getMimeType(),
                'Content-Length: ' . strlen($file_content),
                'Host: ' . $host,
                'x-amz-acl: public-read',
                'x-amz-content-sha256: ' . $payload_hash,
                'x-amz-date: ' . $date
            ]
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            return response()->json(['success' => true, 'message' => 'Upload berhasil', 'data' => ['object_name' => $object_name, 'url' => $endpoint . $uri]]);
        } else {
            return response()->json(['success' => false, 'message' => 'Upload gagal (HTTP ' . $http_code . ')']);
        }
    }

    public function testDeleteMinio(Request $request)
    {
        $object_name = $request->post('object_name');
        $endpoint = rtrim($request->post('endpoint'), '/');
        $access_key = $request->post('access_key');
        $secret_key = $request->post('secret_key');
        $bucket = $request->post('bucket') ?? 'crm';
        $region = $request->post('region') ?? 'us-east-1';

        if (empty($object_name) || empty($endpoint) || empty($access_key) || empty($secret_key)) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        $date = gmdate('Ymd\THis\Z');
        $date_short = gmdate('Ymd');
        $parsed = parse_url($endpoint);
        $host = $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
        $uri = '/' . $bucket . '/' . $object_name;
        $payload_hash = hash('sha256', '');

        $headers = ['host' => $host, 'x-amz-content-sha256' => $payload_hash, 'x-amz-date' => $date];
        ksort($headers);
        $canonical_headers = '';
        $signed_headers = [];
        foreach ($headers as $key => $value) {
            $canonical_headers .= strtolower($key) . ':' . trim($value) . "\n";
            $signed_headers[] = strtolower($key);
        }
        $signed_headers_str = implode(';', $signed_headers);

        $canonical_request = "DELETE\n{$uri}\n\n{$canonical_headers}\n{$signed_headers_str}\n{$payload_hash}";
        $scope = "{$date_short}/{$region}/s3/aws4_request";
        $string_to_sign = "AWS4-HMAC-SHA256\n{$date}\n{$scope}\n" . hash('sha256', $canonical_request);

        $k_date = hash_hmac('sha256', $date_short, 'AWS4' . $secret_key, true);
        $k_region = hash_hmac('sha256', $region, $k_date, true);
        $k_service = hash_hmac('sha256', 's3', $k_region, true);
        $k_signing = hash_hmac('sha256', 'aws4_request', $k_service, true);
        $signature = hash_hmac('sha256', $string_to_sign, $k_signing);

        $authorization = "AWS4-HMAC-SHA256 Credential={$access_key}/{$scope}, SignedHeaders={$signed_headers_str}, Signature={$signature}";

        $ch = curl_init($endpoint . $uri);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $authorization,
                'Host: ' . $host,
                'x-amz-content-sha256: ' . $payload_hash,
                'x-amz-date: ' . $date
            ]
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            return response()->json(['success' => true, 'message' => 'File berhasil dihapus']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus file (HTTP ' . $http_code . ')']);
        }
    }
    public function waStatus()
    {
        $deviceId = Setting::get('gowa_device_id', 'crm-session');
        $response = $this->waRequest('/devices/' . $deviceId . '/status');
        return response()->json($response);
    }

    public function waPairing()
    {
        $deviceId = Setting::get('gowa_device_id', 'crm-session');
        // Ensure device slot exists
        $this->waRequest('/devices', 'POST', ['device_id' => $deviceId]);
        // Get QR
        $response = $this->waRequest('/app/login');
        
        if ($response['success'] && isset($response['data']['results']['qr_link'])) {
            return response()->json(['success' => true, 'data' => $response['data']['results']]);
        }
        return response()->json(['success' => false, 'message' => 'Gagal mengambil QR Code', 'data' => $response['data'] ?? null]);
    }

    public function waLogout()
    {
        $deviceId = Setting::get('gowa_device_id', 'crm-session');
        $response = $this->waRequest('/devices/' . $deviceId . '/logout', 'POST');
        return response()->json($response);
    }

    public function waWebhookDelete($id)
    {
        $log = \App\Models\WebhookLog::findOrFail($id);
        $log->delete();
        return response()->json(['success' => true]);
    }

    public function waWebhookClearOld(Request $request)
    {
        $days = $request->post('days', 7);
        $date = now()->subDays($days);
        $count = \App\Models\WebhookLog::where('created_at', '<', $date)->delete();
        
        return response()->json(['success' => true, 'message' => $count . ' log lama berhasil dihapus']);
    }

    private function waRequest($endpoint, $method = 'GET', $data = null)
    {
        $baseUrl = rtrim(Setting::get('gowa_api_url', 'https://wag.anam.ch'), '/');
        $username = Setting::get('gowa_username', '');
        $password = Setting::get('gowa_password', '');
        $deviceId = Setting::get('gowa_device_id', 'crm-session');

        $url = $baseUrl . $endpoint;
        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Device-Id: ' . $deviceId
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            } else {
                $options[CURLOPT_POSTFIELDS] = '{}';
            }
        }

        if (!empty($username)) {
            $options[CURLOPT_USERPWD] = $username . ':' . $password;
            $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'CURL Error: ' . $error];
        }

        $decoded = json_decode($response, true);
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decoded,
            'http_code' => $httpCode
        ];
    }

    public function startStorageSync(Request $request)
    {
        try {
            set_time_limit(0);
            
            $limit = $request->input('limit', 20);
            $ids = $request->input('ids', []);

            $params = ['--limit' => $limit];
            if (!empty($ids)) {
                $params['--ids'] = implode(',', $ids);
            }
            
            \Illuminate\Support\Facades\Artisan::call('wa:sync-media', $params);
            
            return response()->json([
                'message' => !empty($ids) ? count($ids) . " item berhasil disinkron." : "Sinkronisasi berhasil dijalankan untuk {$limit} item.",
                'synced' => !empty($ids) ? count($ids) : $limit
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function testBackup(Request $request)
    {
        $token = $request->input('token');
        $chatId = $request->input('chat_id');

        if (empty($token) || empty($chatId)) {
            return response()->json(['success' => false, 'message' => 'Token dan Chat ID harus diisi!']);
        }

        try {
            $exitCode = \Illuminate\Support\Facades\Artisan::call('db:backup-telegram', [
                '--token' => $token,
                '--chat_id' => $chatId
            ]);

            if ($exitCode === 0) {
                return response()->json(['success' => true, 'message' => 'Backup berhasil dikirim ke Telegram!']);
            } else {
                $output = \Illuminate\Support\Facades\Artisan::output();
                return response()->json(['success' => false, 'message' => 'Gagal mengirim backup. Pastikan mysqldump dapat dijalankan oleh sistem. Detail: ' . $output]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

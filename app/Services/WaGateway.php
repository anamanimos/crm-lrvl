<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaGateway
{
    protected $gatewayUrl;
    protected $username;
    protected $password;
    protected $deviceId;

    public function __construct()
    {
        $this->reloadSettings();
    }

    public function reloadSettings()
    {
        $this->gatewayUrl = rtrim(Setting::get('gowa_api_url', Setting::get('gateway_url', 'http://localhost:3000')), '/');
        $this->username = Setting::get('gowa_username', '');
        $this->password = Setting::get('gowa_password', '');
        $this->deviceId = Setting::get('gowa_device_id', 'crm-session');
    }

    protected function getClient()
    {
        $client = Http::withHeaders([
            'X-Device-Id' => $this->deviceId,
            'Accept' => 'application/json',
        ]);

        if (!empty($this->username)) {
            $client->withBasicAuth($this->username, $this->password);
        }

        return $client;
    }

    public function sendMessage($to, $message, $replyMessageId = null)
    {
        $data = [
            'phone' => $this->prepareJid($to),
            'message' => $message
        ];

        if ($replyMessageId) {
            $data['reply_message_id'] = $replyMessageId;
        }

        return $this->post('/send/message', $data);
    }

    public function updateMessage($to, $messageId, $message)
    {
        $data = [
            'phone' => $this->prepareJid($to),
            'message' => $message
        ];

        return $this->post('/message/' . urlencode($messageId) . '/update', $data);
    }

    public function sendImage($to, $filePath, $caption = '', $replyMessageId = null)
    {
        $client = $this->getClient();
        
        $data = [
            'phone' => $this->prepareJid($to),
            'caption' => $caption
        ];

        if ($replyMessageId) {
            $data['reply_message_id'] = $replyMessageId;
        }

        $response = $client->attach('image', file_get_contents($filePath), basename($filePath))
            ->post($this->gatewayUrl . '/send/image', $data);

        return $this->handleResponse($response);
    }

    public function sendDocument($to, $filePath, $filename, $caption = '', $replyMessageId = null)
    {
        $client = $this->getClient();
        
        $data = [
            'phone' => $this->prepareJid($to),
            'filename' => $filename,
            'caption' => $caption
        ];

        if ($replyMessageId) {
            $data['reply_message_id'] = $replyMessageId;
        }

        $response = $client->attach('document', file_get_contents($filePath), $filename)
            ->post($this->gatewayUrl . '/send/document', $data);

        return $this->handleResponse($response);
    }

    public function sendImageUrl($to, $imageUrl, $caption = '')
    {
        return $this->post('/send/image', [
            'phone' => $this->prepareJid($to),
            'image_url' => $imageUrl,
            'caption' => $caption
        ]);
    }

    public function sendDocumentUrl($to, $documentUrl, $filename, $caption = '')
    {
        return $this->post('/send/document', [
            'phone' => $this->prepareJid($to),
            'document_url' => $documentUrl,
            'filename' => $filename,
            'caption' => $caption
        ]);
    }

    public function getMessages($to, $limit = 50, $offset = 0)
    {
        return $this->get('/chat/' . $this->prepareJid($to) . '/messages', [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function getChats($limit = 25, $offset = 0)
    {
        return $this->get('/chats', [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function checkUser($phone)
    {
        return $this->get('/user/check', [
            'phone' => $this->prepareJid($phone)
        ]);
    }

    public function getProfilePicture($phone, $isCommunity = false, $isPreview = false)
    {
        return $this->get('/user/avatar', [
            'phone' => $this->prepareJid($phone),
            'is_preview' => $isPreview ? 'true' : 'false',
            'is_community' => $isCommunity ? 'true' : 'false'
        ]);
    }

    public function getGroupInfo($phone)
    {
        return $this->get('/group/info', [
            'group_id' => $this->prepareJid($phone)
        ]);
    }

    protected function get($endpoint, $params = [])
    {
        $response = $this->getClient()->get($this->gatewayUrl . $endpoint, $params);
        return $this->handleResponse($response);
    }

    protected function post($endpoint, $data = [])
    {
        $response = $this->getClient()->post($this->gatewayUrl . $endpoint, $data);
        return $this->handleResponse($response);
    }

    protected function handleResponse($response)
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
                'http_code' => $response->status()
            ];
        }

        Log::error('WA Gateway Error: ' . $response->body());
        return [
            'success' => false,
            'error' => $response->reason(),
            'data' => $response->json(),
            'http_code' => $response->status()
        ];
    }

    private function prepareJid($to)
    {
        if (strpos($to, '@') !== false) {
            return explode(':', $to)[0];
        }
        return $this->formatPhone($to) . '@s.whatsapp.net';
    }

    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}

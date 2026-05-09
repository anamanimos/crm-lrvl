<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DealStage;
use App\Models\Label;
use App\Models\Message;
use App\Models\MessageRevision;
use App\Models\User;
use App\Models\WaGroup;
use App\Services\WaGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    protected $waGateway;

    public function __construct(WaGateway $waGateway)
    {
        $this->waGateway = $waGateway;
    }

    public function index(Request $request)
    {
        $customer_id = $request->get('customer');
        $phone_query = $request->get('phone');
        $selected_customer = null;

        if ($customer_id) {
            $selected_customer = Customer::find($customer_id);
        } elseif ($phone_query) {
            // Logic to find or create customer from phone
            $selected_customer = Customer::where('wa_number', 'like', "%$phone_query%")->first();
        }

        $labels = Label::all();
        $deal_stages = DealStage::orderBy('sort_order')->get();
        $agents = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['superadmin', 'admin', 'cs', 'sales']);
        })->get();

        return view('chat.index', compact('labels', 'deal_stages', 'agents', 'selected_customer'));
    }

    public function customers(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $mode = $request->get('mode', 'all');
        $filter_type = $request->get('filter', 'all'); // 'all', 'unread', 'personal', 'groups'
        $label_id = $request->get('label_id');
        $deal_stage_id = $request->get('deal_stage_id');

        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Start with Customers
        $query = Customer::query()->where('is_archived', 0);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('wa_number', 'like', "%$search%");
            });
        }

        if ($label_id) {
            $query->whereHas('labels', function($q) use ($label_id) {
                $q->where('labels.id', $label_id);
            });
        }

        if ($deal_stage_id) {
            $query->whereHas('deals', function($q) use ($deal_stage_id) {
                $q->where('deal_stage_id', $deal_stage_id);
            });
        }

        // Standardize list for merged response
        $customers = [];
        if ($filter_type !== 'groups') {
            // Logic for 'has_chat' or 'unread'
            if ($filter_type === 'unread') {
                $query->whereHas('messages', function($q) {
                    $q->whereIn('status', ['unread', 'delivered'])->where('direction', 'in');
                });
            }

            $customer_list = $query->with(['labels', 'assignedUser'])
                ->orderBy('last_chat_at', 'desc')
                ->limit(100) // Fetch more for in-memory merging if needed
                ->get();

            foreach ($customer_list as $c) {
                $c->type = 'individual';
                $c->display_name = $c->name ?: $c->wa_number ?: 'Tanpa Nama';
                
                $c->avatar_url = $this->resolveAvatarUrl($c->avatar);

                // Unread count
                $c->unread_count = Message::where('customer_id', $c->id)
                    ->whereNull('wa_group_id')
                    ->where('direction', 'in')
                    ->whereIn('status', ['unread', 'delivered'])
                    ->count();
                
                // Last message (personal only)
                $last_msg = Message::where('customer_id', $c->id)->whereNull('wa_group_id')->orderBy('created_at', 'desc')->first();
                $c->last_message = $last_msg;

                $customers[] = $c;
            }
        }

        $groups = [];
        if ($filter_type === 'all' || $filter_type === 'groups') {
            $group_query = WaGroup::query();
            if ($search) {
                $group_query->where('name', 'like', "%$search%");
            }
            
            $group_list = $group_query->orderBy('last_chat_at', 'desc')->limit(50)->get();
            foreach ($group_list as $g) {
                $g->type = 'group';
                $g->display_name = $g->name ?: $g->jid;
                $g->wa_number = $g->jid;
                $g->unread_count = Message::where('wa_group_id', $g->id)
                    ->where('direction', 'in')
                    ->whereIn('status', ['unread', 'delivered'])
                    ->count();
                
                // Last message
                $g->last_message = Message::where('wa_group_id', $g->id)->orderBy('created_at', 'desc')->first();
                $g->avatar_url = $this->resolveAvatarUrl($g->avatar);
                $g->labels = [];

                $groups[] = $g;
            }
        }

        $merged = array_merge($customers, $groups);
        usort($merged, function($a, $b) {
            $t1 = 0;
            if ($a->last_chat_at) {
                $t1 = ($a->last_chat_at instanceof \Carbon\Carbon || $a->last_chat_at instanceof \DateTime) 
                    ? $a->last_chat_at->timestamp 
                    : strtotime($a->last_chat_at);
            }
            
            $t2 = 0;
            if ($b->last_chat_at) {
                $t2 = ($b->last_chat_at instanceof \Carbon\Carbon || $b->last_chat_at instanceof \DateTime) 
                    ? $b->last_chat_at->timestamp 
                    : strtotime($b->last_chat_at);
            }
            
            return $t2 - $t1;
        });

        $paged_data = array_slice($merged, $offset, $limit);

        return response()->json([
            'success' => true,
            'data' => $paged_data,
            'has_more' => count($merged) > ($offset + $limit),
            'page' => $page + 1
        ]);
    }

    public function conversation(Request $request)
    {
        $chat_id = $request->get('customer_id');
        $type = $request->get('type', 'individual');
        $limit = $request->get('limit', 30);

        if ($type === 'group') {
            $chat_info = WaGroup::find($chat_id);
            if ($chat_info) {
                $chat_info->type = 'group';
                $chat_info->display_name = $chat_info->name ?: $chat_info->jid;
                $chat_info->wa_number = $chat_info->jid;
                $chat_info->avatar_url = $this->resolveAvatarUrl($chat_info->avatar);
                $chat_info->labels = [];

                // Mark as read
                Message::where('wa_group_id', $chat_id)
                    ->where('direction', 'in')
                    ->whereIn('status', ['unread', 'delivered'])
                    ->update(['status' => 'read']);
            }
            $messages = Message::with(['replyMessage', 'customer', 'revisions'])
                ->where('wa_group_id', $chat_id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();
        } else {
            $chat_info = Customer::with('labels')->find($chat_id);
            if ($chat_info) {
                $chat_info->type = 'individual';
                $chat_info->display_name = $chat_info->name ?: $chat_info->wa_number;
                $chat_info->avatar_url = $this->resolveAvatarUrl($chat_info->avatar);
                
                // Mark as read
                Message::where('customer_id', $chat_id)
                    ->where('direction', 'in')
                    ->whereIn('status', ['unread', 'delivered'])
                    ->update(['status' => 'read']);
            }
            $messages = Message::with(['replyMessage', 'customer', 'revisions'])
                ->where('customer_id', $chat_id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();
        }

        return response()->json([
            'success' => true,
            'chat_info' => $chat_info,
            'data' => $messages,
            'server_time' => time()
        ]);
    }

    public function refreshAvatar(Request $request)
    {
        $chat_id = $request->get('customer_id');
        $type = $request->get('type', 'individual');

        if ($type === 'group') {
            $entity = WaGroup::find($chat_id);
            $jid = $entity->jid;
        } else {
            $entity = Customer::find($chat_id);
            $jid = $entity->wa_number;
        }

        if (!$entity) {
            return response()->json(['success' => false, 'error' => 'Entity not found']);
        }

        $response = $this->waGateway->getProfilePicture($jid, $type === 'group');

        if ($response['success'] && !empty($response['data']['url'])) {
            $url = $response['data']['url'];
            
            // Download and save locally
            try {
                $contents = file_get_contents($url);
                $filename = 'avatars/' . ($type === 'group' ? 'group_' : 'cust_') . $chat_id . '.jpg';
                Storage::disk('public')->put($filename, $contents);
                
                $entity->update([
                    'avatar' => $filename,
                    'avatar_last_updated' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'avatar_url' => Storage::url($filename)
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => false, 'error' => 'No avatar found on gateway']);
    }

    public function refreshGroupInfo(Request $request)
    {
        $group_id = $request->get('group_id');
        $group = WaGroup::find($group_id);

        if (!$group) {
            return response()->json(['success' => false, 'error' => 'Group not found']);
        }

        // 1. Get Group Info (Name)
        $infoResponse = $this->waGateway->getGroupInfo($group->jid);
        $updatedName = null;
        if ($infoResponse['success']) {
            $data = $infoResponse['data']['results'] ?? $infoResponse['data'];
            $updatedName = $data['Name'] ?? $data['subject'] ?? null;
            if ($updatedName) {
                $group->update(['name' => $updatedName]);
            }
        }

        // 2. Get Avatar
        $avatarUrl = null;
        $avatarResponse = $this->waGateway->getProfilePicture($group->jid, true);
        if ($avatarResponse['success'] && !empty($avatarResponse['data']['url'])) {
            $url = $avatarResponse['data']['url'];
            try {
                $contents = file_get_contents($url);
                $filename = 'avatars/group_' . $group->id . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $contents);
                
                $group->update([
                    'avatar' => $filename,
                    'avatar_last_updated' => now()
                ]);
                $avatarUrl = \Illuminate\Support\Facades\Storage::url($filename);
            } catch (\Exception $e) {
                \Log::error("Failed to download group avatar: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'name' => $updatedName ?: $group->name,
            'avatar_url' => $avatarUrl ?: ($group->avatar ? \Illuminate\Support\Facades\Storage::url($group->avatar) : null)
        ]);
    }

    private function resolveAvatarUrl($avatar)
    {
        if (!$avatar) return null;

        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        // Check if JSON (for legacy data)
        $avatar_data = json_decode($avatar, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($avatar_data)) {
            return $avatar_data['cloudinary']['url'] ?? ($avatar_data['minio']['url'] ?? null);
        }

        return Storage::url($avatar);
    }

    public function send(Request $request)
    {
        $chat_id = $request->get('customer_id');
        $type = $request->get('type', 'individual');
        $content = $request->get('message');
        $reply_message_id = $request->get('reply_message_id');

        $target_number = '';
        $customer_id = null;
        $wa_group_id = null;
        $replyContent = null;
        $replySenderName = null;

        if ($reply_message_id) {
            $orig = Message::where('wa_message_id', $reply_message_id)->first();
            if ($orig) {
                $replyContent = $orig->content;
                $replySenderName = $orig->direction == 'out' ? 'Anda' : ($orig->customer->name ?? 'Customer');
            }
        }

        if ($type === 'group') {
            $group = WaGroup::find($chat_id);
            if (!$group) return response()->json(['error' => 'Group not found'], 404);
            $target_number = $group->jid;
            $wa_group_id = $chat_id;
        } else {
            $customer = Customer::find($chat_id);
            if (!$customer) return response()->json(['error' => 'Customer not found'], 404);
            $target_number = $customer->wa_number;
            $customer_id = $chat_id;
        }

        $result = $this->waGateway->sendMessage($target_number, $content, $reply_message_id);

        // DEBUG: Log gateway response to help identify messageId structure
        Log::info('WA Gateway Send Response: ', ['result' => $result]);

        $message = Message::create([
            'customer_id' => $customer_id,
            'wa_group_id' => $wa_group_id,
            'user_id' => Auth::id(),
            'content' => $content,
            'direction' => 'out',
            'type' => 'text',
            'status' => $result['success'] ? 'sent' : 'failed',
            'wa_message_id' => $result['data']['results']['message_id'] ?? null,
            'wa_timestamp' => time(),
            'reply_message_id' => $reply_message_id,
            'reply_content' => $replyContent ?? $request->get('reply_content'),
            'reply_sender_name' => $replySenderName ?? $request->get('reply_sender_name'),
        ]);

        return response()->json([
            'success' => $result['success'],
            'data' => $message,
            'server_time' => time()
        ]);
    }

    protected function resolveChatTarget($chat_id, $type = 'individual')
    {
        $target_number = '';
        $customer_id = null;
        $wa_group_id = null;

        if ($type === 'group') {
            $group = WaGroup::find($chat_id);
            if (!$group) {
                return [false, null, null, null, 'Group not found'];
            }
            $target_number = $group->jid;
            $wa_group_id = $chat_id;
        } else {
            $customer = Customer::find($chat_id);
            if (!$customer) {
                return [false, null, null, null, 'Customer not found'];
            }
            $target_number = $customer->wa_number;
            $customer_id = $chat_id;
        }

        return [true, $target_number, $customer_id, $wa_group_id, null];
    }

    public function sendImage(Request $request)
    {
        $chat_id = $request->get('customer_id');
        $type = $request->get('type', 'individual');
        $caption = $request->get('caption', '');
        $reply_message_id = $request->get('reply_message_id');

        list($ok, $target_number, $customer_id, $wa_group_id, $error) = $this->resolveChatTarget($chat_id, $type);
        if (!$ok) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        $image = $request->file('image');
        if (!$image || !$image->isValid()) {
            return response()->json(['success' => false, 'message' => 'Gambar tidak valid atau tidak ditemukan'], 400);
        }

        $uploadDir = public_path('uploads/chat');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $image->getClientOriginalName());
        $filePath = $image->move($uploadDir, $filename)->getPathname();
        $mediaUrl = asset('uploads/chat/' . $filename);

        $result = $this->waGateway->sendImage($target_number, $filePath, $caption, $reply_message_id);

        $message = Message::create([
            'customer_id' => $customer_id,
            'wa_group_id' => $wa_group_id,
            'user_id' => Auth::id(),
            'content' => '[IMAGE:' . $mediaUrl . ']' . ($caption ? ' ' . trim($caption) : ''),
            'direction' => 'out',
            'type' => 'image',
            'status' => $result['success'] ? 'sent' : 'failed',
            'wa_message_id' => $result['data']['results']['message_id'] ?? null,
            'wa_timestamp' => time(),
            'reply_message_id' => $reply_message_id,
            'reply_content' => $request->get('reply_content'),
            'reply_sender_name' => $request->get('reply_sender_name'),
            'media_url' => $mediaUrl,
            'media_path' => 'uploads/chat/' . $filename,
            'media_meta' => [
                'filename' => $filename,
                'mime' => $image->getClientMimeType(),
            ]
        ]);

        return response()->json([
            'success' => $result['success'],
            'data' => $message,
            'server_time' => time(),
            'message' => $result['error'] ?? null,
        ]);
    }

    public function sendDocument(Request $request)
    {
        $chat_id = $request->get('customer_id');
        $type = $request->get('type', 'individual');
        $reply_message_id = $request->get('reply_message_id');

        list($ok, $target_number, $customer_id, $wa_group_id, $error) = $this->resolveChatTarget($chat_id, $type);
        if (!$ok) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        $document = $request->file('document');
        if (!$document || !$document->isValid()) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak valid atau tidak ditemukan'], 400);
        }

        $uploadDir = public_path('uploads/chat');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $document->getClientOriginalName());
        $filePath = $document->move($uploadDir, $filename)->getPathname();
        $mediaUrl = asset('uploads/chat/' . $filename);

        $result = $this->waGateway->sendDocument($target_number, $filePath, $filename, '', $reply_message_id);

        $message = Message::create([
            'customer_id' => $customer_id,
            'wa_group_id' => $wa_group_id,
            'user_id' => Auth::id(),
            'content' => '[DOCUMENT:' . $mediaUrl . ':' . $filename . ']',
            'direction' => 'out',
            'type' => 'document',
            'status' => $result['success'] ? 'sent' : 'failed',
            'wa_message_id' => $result['data']['results']['message_id'] ?? null,
            'wa_timestamp' => time(),
            'reply_message_id' => $reply_message_id,
            'reply_content' => $request->get('reply_content'),
            'reply_sender_name' => $request->get('reply_sender_name'),
            'media_url' => $mediaUrl,
            'media_path' => 'uploads/chat/' . $filename,
            'media_meta' => [
                'filename' => $filename,
                'mime' => $document->getClientMimeType(),
            ]
        ]);

        return response()->json([
            'success' => $result['success'],
            'data' => $message,
            'server_time' => time(),
            'message' => $result['error'] ?? null,
        ]);
    }

    public function editMessage(Request $request)
    {
        $id = $request->get('id');
        $content = $request->get('content');

        $message = Message::find($id);
        if (!$message) return response()->json(['error' => 'Pesan tidak ditemukan'], 404);

        // Limit edit to 10 minutes
        $timestamp = $message->wa_timestamp ?: ($message->created_at ? $message->created_at->timestamp : time());
        if (time() - $timestamp > 600) {
            return response()->json(['success' => false, 'error' => 'Pesan hanya dapat diubah dalam 10 menit setelah terkirim'], 400);
        }

        $target_number = '';
        if ($message->wa_group_id) {
            $target_number = $message->waGroup->jid ?? '';
        } else {
            $target_number = $message->customer->wa_number ?? '';
        }

        $result = $this->waGateway->updateMessage($target_number, $message->wa_message_id, $content);

        if ($result['success']) {
            // Save revision
            MessageRevision::create([
                'message_id' => $message->id,
                'old_content' => $message->content,
                'new_content' => $content
            ]);

            $message->update([
                'content' => $content,
                'is_edited' => 1
            ]);

            // Load revisions for UI update
            $message->load('revisions');

            return response()->json([
                'success' => true,
                'data' => $message,
                'server_time' => time()
            ]);
        }

        return response()->json(['success' => false, 'error' => $result['error'] ?? 'Gagal mengubah pesan']);
    }

    public function markAsRead(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type', 'individual');

        if ($type === 'group') {
            Message::where('wa_group_id', $id)->where('direction', 'in')->update(['status' => 'read']);
        } else {
            Message::where('customer_id', $id)->where('direction', 'in')->update(['status' => 'read']);
        }

        return response()->json(['success' => true]);
    }

    public function markAsUnread(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type', 'individual');

        // Logic for marking as unread usually involves setting the last message status
        if ($type === 'group') {
            $last_msg = Message::where('wa_group_id', $id)->where('direction', 'in')->orderBy('created_at', 'desc')->first();
            if ($last_msg) $last_msg->update(['status' => 'unread']);
        } else {
            $last_msg = Message::where('customer_id', $id)->where('direction', 'in')->orderBy('created_at', 'desc')->first();
            if ($last_msg) $last_msg->update(['status' => 'unread']);
        }

        return response()->json(['success' => true]);
    }

    public function bulkMarkRead()
    {
        Message::where('direction', 'in')->where('status', 'unread')->update(['status' => 'read']);
        return response()->json(['success' => true, 'message' => 'Semua pesan ditandai sudah dibaca']);
    }

    public function renameChat(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type', 'individual');
        $name = $request->get('name');

        if ($type === 'group') {
            WaGroup::where('id', $id)->update(['name' => $name]);
        } else {
            Customer::where('id', $id)->update(['name' => $name]);
        }

        return response()->json(['success' => true]);
    }

    public function forwardMessage(Request $request)
    {
        $source_id = $request->get('source_message_id');
        $target_ids = $request->get('target_chat_ids');
        $target_types = $request->get('target_types');

        $source_msg = Message::find($source_id);
        if (!$source_msg) return response()->json(['error' => 'Pesan asal tidak ditemukan'], 404);

        $results = [];
        foreach ($target_ids as $index => $target_id) {
            $type = $target_types[$index] ?? 'individual';
            
            $target_number = '';
            $customer_id = null;
            $wa_group_id = null;

            if ($type === 'group') {
                $group = WaGroup::find($target_id);
                if ($group) {
                    $target_number = $group->jid;
                    $wa_group_id = $target_id;
                }
            } else {
                $customer = Customer::find($target_id);
                if ($customer) {
                    $target_number = $customer->wa_number;
                    $customer_id = $target_id;
                }
            }

            if ($target_number) {
                $cleanContent = $source_msg->content;
                if ($source_msg->type === 'image') {
                    $cleanContent = preg_replace('/^\[IMAGE:[^\]]+\]\s*/', '', $source_msg->content);
                    $res = $this->waGateway->sendImage(
                        $target_number, 
                        public_path($source_msg->media_path), 
                        $cleanContent
                    );
                } else if ($source_msg->type === 'document') {
                    $res = $this->waGateway->sendDocument(
                        $target_number, 
                        public_path($source_msg->media_path), 
                        basename($source_msg->media_path), 
                        ''
                    );
                } else {
                    $res = $this->waGateway->sendMessage($target_number, $cleanContent);
                }

                $new_msg = Message::create([
                    'customer_id' => $customer_id,
                    'wa_group_id' => $wa_group_id,
                    'user_id' => Auth::id(),
                    'content' => $source_msg->content,
                    'direction' => 'out',
                    'type' => $source_msg->type,
                    'status' => $res['success'] ? 'sent' : 'failed',
                    'wa_message_id' => $res['data']['results']['message_id'] ?? null,
                    'wa_timestamp' => time(),
                    'media_url' => $source_msg->media_url,
                    'media_path' => $source_msg->media_path,
                    'media_meta' => $source_msg->media_meta,
                ]);
                $results[] = $new_msg;
            }
        }

        return response()->json(['success' => true, 'data' => $results, 'message' => 'Pesan berhasil diteruskan']);
    }

    public function poll(Request $request)
    {
        $chat_id = $request->get('customer_id');
        $type = $request->get('type', 'individual');
        $after_date = $request->get('after_date');

        $query = Message::query();

        if ($type === 'group') {
            $query->where('wa_group_id', $chat_id);
        } else {
            $query->where('customer_id', $chat_id);
        }

        if ($after_date) {
            $query->where('created_at', '>', $after_date);
        }

        $messages = $query->with(['replyMessage', 'customer', 'revisions'])->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
            'server_time' => time(),
            'after_date' => $messages->last() ? $messages->last()->created_at : $after_date
        ]);
    }

    public function assign(Request $request)
    {
        $customerId = $request->get('customer_id');
        $userId = $request->get('user_id');

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
        }

        $customer->assigned_user_id = $userId == 0 ? null : $userId;
        $customer->save();

        return response()->json(['success' => true, 'message' => 'Customer berhasil diassign']);
    }

    public function assignLabels(Request $request)
    {
        $customerId = $request->get('customer_id');
        $labels = $request->get('label_ids') ?? $request->get('labels', []);

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
        }

        $customer->labels()->sync($labels);
        $new_labels = $customer->labels;

        return response()->json([
            'success' => true,
            'message' => 'Label berhasil diperbarui',
            'labels' => $new_labels
        ]);
    }

    public function getCustomerDetailApi(Request $request)
    {
        $id = $request->get('id');
        $customer = Customer::with(['assignedUser', 'labels', 'deals.stage'])->find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
        }

        // Avatar handling
        $avatar_url = null;
        if ($customer->avatar) {
            if (filter_var($customer->avatar, FILTER_VALIDATE_URL)) {
                $avatar_url = $customer->avatar;
            } else {
                $avatar_data = json_decode($customer->avatar, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($avatar_data)) {
                    $avatar_url = $avatar_data['cloudinary']['url'] ?? ($avatar_data['minio']['url'] ?? null);
                } else {
                    $avatar_url = Storage::url($customer->avatar);
                }
            }
        }

        $data = [
            'id' => $customer->id,
            'name' => $customer->name,
            'wa_number' => $customer->wa_number,
            'email' => $customer->email,
            'address' => $customer->address,
            'assigned_user_name' => $customer->assignedUser ? $customer->assignedUser->name : null,
            'last_chat_at' => $customer->last_chat_at ? $customer->last_chat_at->format('d M Y H:i') : '-',
            'avatar_last_updated' => $customer->updated_at ? $customer->updated_at->diffForHumans() : 'Belum diupdate',
            'avatar_url' => $avatar_url,
            'deals' => $customer->deals->map(function ($deal) {
                return [
                    'id' => $deal->id,
                    'title' => $deal->title,
                    'stage_name' => $deal->stage->name ?? '-',
                    'stage_color' => $deal->stage->color ?? '#6c757d',
                ];
            }),
            'labels' => $customer->labels->map(function ($label) {
                return [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ];
            }),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function templates()
    {
        $templates = \App\Models\Template::where('is_active', 1)->get();
        $grouped = $templates->groupBy('category');

        return response()->json([
            'success' => true,
            'grouped' => $grouped
        ]);
    }

    public function sync(Request $request)
    {
        $chat_id = $request->input('customer_id');
        $type = $request->input('type', 'individual');
        $limit = 50;
        $offset = $request->input('offset', 0);

        if ($type === 'group') {
            $group = WaGroup::find($chat_id);
            if (!$group) {
                return response()->json(['success' => false, 'error' => 'Group not found'], 404);
            }
            $target_jid = $group->jid;
        } else {
            $customer = Customer::find($chat_id);
            if (!$customer) {
                return response()->json(['success' => false, 'error' => 'Customer not found'], 404);
            }
            $target_jid = $customer->wa_number;
        }

        $result = $this->_performSync($target_jid, $type, $chat_id, $limit, $offset);

        if (!$result['success']) {
            return response()->json(['success' => false, 'error' => 'Gagal mengambil pesan dari Gateway'], 500);
        }

        $total = $result['total'];
        $added_count = $result['added'];
        $next_offset = $offset + $limit;
        $has_next = $next_offset < $total;
        $current_page = floor($offset / $limit) + 1;
        $total_pages = $total > 0 ? ceil($total / $limit) : 1;

        $message = "Halaman $current_page/$total_pages selesai ($added_count pesan baru).";
        if (!$has_next) {
            $message = "Sinkronisasi selesai. Total: $total pesan dari $total_pages halaman.";
        } else {
            $message .= " Melanjutkan...";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'added' => $added_count,
            'offset' => $offset,
            'next_offset' => $next_offset,
            'total' => $total,
            'has_next' => $has_next
        ]);
    }

    public function syncContacts(Request $request)
    {
        $limit = 25;
        $offset = $request->input('offset', 0);

        $response = $this->waGateway->getChats($limit, $offset);

        if (!$response['success']) {
            return response()->json(['success' => false, 'error' => 'Gagal mengambil daftar chat dari Gateway'], 500);
        }

        $raw_data = $response['data'] ?? [];
        $chats = $raw_data['results']['data'] ?? $raw_data['data'] ?? $raw_data ?? [];
        $pagination = $raw_data['results']['pagination'] ?? [];
        $total = $pagination['total'] ?? 0;

        $synced_count = 0;

        foreach ($chats as $chat) {
            $jid = $chat['jid'] ?? $chat['id'] ?? null;
            if (!$jid || strpos($jid, 'status@broadcast') !== false) {
                continue;
            }

            $last_chat_date = null;
            if (!empty($chat['last_message_time'])) {
                $timestamp = strtotime($chat['last_message_time']);
                $last_chat_date = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
            } elseif (!empty($chat['conversationTimestamp'])) {
                $timestamp = is_numeric($chat['conversationTimestamp']) ? (int) $chat['conversationTimestamp'] : strtotime($chat['conversationTimestamp']);
                $last_chat_date = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
            }

            if (strpos($jid, '@g.us') !== false) {
                $groupName = $chat['name'] ?? $chat['subject'] ?? null;
                
                // Parity: Fetch group info if name is missing or looks like JID
                if (empty($groupName) || $groupName === $jid) {
                    $gInfo = $this->waGateway->getGroupInfo($jid);
                    if ($gInfo['success']) {
                        $groupName = $gInfo['data']['results']['Name'] ?? $gInfo['data']['name'] ?? $groupName;
                    }
                }

                WaGroup::updateOrCreate(['jid' => $jid], [
                    'name' => $groupName ?: 'Unknown Group',
                    'last_chat_at' => $last_chat_date
                ]);
            } else {
                $phone = str_replace('@s.whatsapp.net', '', $jid);
                $customerName = $chat['name'] ?? $chat['pushname'] ?? null;
                
                Customer::updateOrCreate(['wa_number' => $phone], [
                    'name' => $customerName,
                    'last_chat_at' => $last_chat_date
                ]);
            }
            $synced_count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Synced $synced_count contacts",
            'next_offset' => $offset + $limit,
            'has_next' => ($offset + $limit) < $total
        ]);
    }

    protected function _performSync($target_jid, $type, $chat_id, $limit, $offset)
    {
        $response = $this->waGateway->getMessages($target_jid, $limit, $offset);

        if (!$response['success']) {
            return ['success' => false];
        }

        $raw_data = $response['data'] ?? [];
        $messages = $raw_data['results']['data'] ?? $raw_data['data'] ?? $raw_data ?? [];
        $pagination = $raw_data['results']['pagination'] ?? [];

        $total = $pagination['total'] ?? 0;
        $added_count = 0;

        foreach ($messages as $msg) {
            $wa_msg_id = $msg['id'] ?? $msg['key']['id'] ?? null;
            if (!$wa_msg_id) {
                continue;
            }

            if (Message::where('wa_message_id', $wa_msg_id)->exists()) {
                $update_data = [];
                if ($type === 'group') {
                    $update_data['wa_group_id'] = $chat_id;
                } else {
                    $update_data['customer_id'] = $chat_id;
                    $update_data['wa_group_id'] = null;
                }
                Message::where('wa_message_id', $wa_msg_id)->update($update_data);
                continue;
            }

            $content = '';
            $msg_type = 'text';
            $is_from_me = false;
            $msg_timestamp = time();

            if (array_key_exists('is_from_me', $msg)) {
                $is_from_me = $msg['is_from_me'];
                $content = $msg['content'] ?? '';
                $msg_timestamp = isset($msg['timestamp']) ? strtotime($msg['timestamp']) : time();

                if (!empty($msg['media_type'])) {
                    $msg_type = $msg['media_type'];
                    if (in_array($msg_type, ['image', 'video', 'document', 'audio'])) {
                        $full_url = $msg['url'] ?? null;
                        if ($full_url) {
                            if ($msg_type == 'image') {
                                $content = '[IMAGE:' . $full_url . ']' . ($content ? ' ' . $content : '');
                            } elseif ($msg_type == 'document') {
                                $content = '[DOCUMENT:' . $full_url . ':' . ($msg['filename'] ?? 'file') . ']';
                            } elseif ($msg_type == 'video') {
                                $content = '[VIDEO:' . $full_url . ']' . ($content ? ' ' . $content : '');
                            } elseif ($msg_type == 'audio') {
                                $content = '[AUDIO:' . $full_url . ']';
                            }
                        }
                    }
                }
            } elseif (isset($msg['key'])) {
                $is_from_me = $msg['key']['fromMe'] ?? false;
                $msg_timestamp = $msg['messageTimestamp'] ?? time();
                $msg_content = $msg['message'] ?? [];
                if (isset($msg_content['conversation'])) {
                    $content = $msg_content['conversation'];
                } elseif (isset($msg_content['extendedTextMessage']['text'])) {
                    $content = $msg_content['extendedTextMessage']['text'];
                } elseif (isset($msg_content['imageMessage'])) {
                    $msg_type = 'image';
                    $content = $msg_content['imageMessage']['caption'] ?? '[Image]';
                }
            }

            if (!$content && $msg_type == 'text') {
                continue;
            }

            $content_final = $content;
            if ($type === 'group' && !$is_from_me) {
                $sender_name = $msg['sender_name'] ?? $msg['pushName'] ?? $msg['from_name'] ?? null;
                if ($sender_name) {
                    $content_final = "[SENDER:{$sender_name}] " . $content;
                }
            }

            Message::create([
                'wa_group_id' => ($type === 'group') ? $chat_id : null,
                'customer_id' => ($type === 'individual') ? $chat_id : null,
                'user_id' => null,
                'content' => $content_final,
                'direction' => $is_from_me ? 'out' : 'in',
                'type' => $msg_type,
                'status' => $is_from_me ? 'sent' : 'unread',
                'wa_message_id' => $wa_msg_id,
                'created_at' => date('Y-m-d H:i:s', $msg_timestamp)
            ]);
            $added_count++;
        }

        return [
            'success' => true,
            'total' => $total,
            'added' => $added_count
        ];
    }

    public function checkWhatsApp(Request $request)
    {
        $phone = $request->get('phone');
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Nomor telepon harus diisi'], 400);
        }

        // Normalize phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        $result = $this->waGateway->checkUser($phone);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa nomor. Gateway tidak tersedia.',
                'on_whatsapp' => false
            ]);
        }

        $data = $result['data'] ?? [];
        $results = $data['results'] ?? $data['data'] ?? $data;
        $isOnWhatsApp = false;
        $jid = null;

        // Handle different response formats from the API
        if (is_array($results)) {
            if (isset($results['is_on_whatsapp'])) {
                $isOnWhatsApp = $results['is_on_whatsapp'];
                $jid = $results['jid'] ?? null;
            } elseif (isset($results['data']) && is_array($results['data'])) {
                $first = $results['data'][0] ?? null;
                if ($first) {
                    $isOnWhatsApp = $first['is_on_whatsapp'] ?? false;
                    $jid = $first['jid'] ?? null;
                }
            } elseif (isset($results[0])) {
                $isOnWhatsApp = $results[0]['is_on_whatsapp'] ?? false;
                $jid = $results[0]['jid'] ?? null;
            }
        }

        // Check if customer already exists
        $existingCustomer = Customer::where('wa_number', $phone)->first();

        return response()->json([
            'success' => true,
            'on_whatsapp' => $isOnWhatsApp,
            'phone' => $phone,
            'jid' => $jid,
            'existing_customer' => $existingCustomer ? [
                'id' => $existingCustomer->id,
                'name' => $existingCustomer->name,
                'wa_number' => $existingCustomer->wa_number
            ] : null
        ]);
    }

    public function startNewChat(Request $request)
    {
        $phone = $request->get('phone');
        $name = $request->get('name', '');

        // Normalize phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        // Find or create customer
        $customer = Customer::where('wa_number', $phone)->first();
        if (!$customer) {
            $customer = Customer::create([
                'wa_number' => $phone,
                'name' => $name ?: $phone,
                'last_chat_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'wa_number' => $customer->wa_number,
                'type' => 'individual'
            ]
        ]);
    }
}

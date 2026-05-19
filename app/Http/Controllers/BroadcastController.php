<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use App\Models\Customer;
use App\Models\Label;
use App\Models\Message;
use App\Models\Setting;
use App\Traits\HasCloudinary;
use App\Services\WaGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BroadcastController extends Controller
{
    use HasCloudinary;

    protected $waGateway;

    public function __construct(WaGateway $waGateway)
    {
        $this->waGateway = $waGateway;
    }
    public function index()
    {
        $broadcasts = Broadcast::with('creator')->latest()->get();
        
        $sentThisHour = BroadcastRecipient::where('status', 'sent')
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', now()->startOfHour())
            ->count();
            
        $sentThisDay = BroadcastRecipient::where('status', 'sent')
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', now()->startOfDay())
            ->count();

        $maxPerHour = (int) Setting::get('broadcast_max_per_hour', 0);
        $maxPerDay = (int) Setting::get('broadcast_max_per_day', 0);

        $globalStats = [
            'total_broadcasts' => $broadcasts->count(),
            'total_sent' => BroadcastRecipient::where('status', 'sent')->count(),
            'total_pending' => BroadcastRecipient::where('status', 'pending')->count(),
            'sent_this_hour' => $sentThisHour,
            'sent_this_day' => $sentThisDay,
            'max_per_hour' => $maxPerHour,
            'max_per_day' => $maxPerDay,
        ];

        // Calculate stats for each broadcast
        foreach ($broadcasts as $broadcast) {
            $stats = $broadcast->recipients()
                ->selectRaw("COUNT(*) as total")
                ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent")
                ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
                ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
                ->first();
            
            $broadcast->stats = [
                'total' => $stats->total ?: 0,
                'sent' => $stats->sent ?: 0,
                'failed' => $stats->failed ?: 0,
                'pending' => $stats->pending ?: 0,
            ];
        }

        return view('broadcasts.index', compact('broadcasts', 'globalStats'));
    }

    public function create()
    {
        $labels = Label::where('is_active', true)->get();
        $customers = Customer::where('is_archived', false)->limit(1000)->get();
        
        $delayMin = Setting::get('broadcast_delay_min', 5);
        $delayMax = Setting::get('broadcast_delay_max', 15);

        return view('broadcasts.form', compact('labels', 'customers', 'delayMin', 'delayMax'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'message_template' => 'required|array|min:1',
            'message_template.*' => 'required|string',
            'target_type' => 'required|in:all,label,custom',
            'delay_min' => 'required|integer|min:1',
            'delay_max' => 'required|integer|min:1|gte:delay_min',
            'media_file' => 'nullable|file|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $filters = [];
            if ($request->target_type == 'label') {
                $filters['label_id'] = $request->label_id;
            } elseif ($request->target_type == 'custom') {
                $filters['customer_ids'] = $request->customer_ids;
            }

            $status = $request->schedule_type == 'date' ? 'scheduled' : 'running';

            $broadcast = Broadcast::create([
                'name' => $request->name,
                'message_template' => json_encode($request->message_template),
                'target_type' => $request->target_type,
                'target_filters' => $filters,
                'delay_min' => $request->delay_min,
                'delay_max' => $request->delay_max,
                'status' => $status,
                'scheduled_at' => $request->scheduled_at,
                'created_by' => auth()->id(),
            ]);

            // Handle Media Upload
            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                if ($file->isValid()) {
                    try {
                        $cloudinary = $this->uploadToCloudinary($file);
                        $broadcast->media_path = $cloudinary ? $cloudinary['url'] : $file->store('broadcasts/media', 'public');
                    } catch (\Exception $e) {
                        \Log::error('Broadcast Cloudinary Error: ' . $e->getMessage());
                        $broadcast->media_path = $file->store('broadcasts/media', 'public');
                    }
                    $broadcast->media_type = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';
                    $broadcast->save();
                }
            }

            // Generate Recipients
            $customerQuery = Customer::where('is_archived', false);
            
            if ($request->target_type == 'label') {
                $customerQuery->whereHas('labels', function($q) use ($request) {
                    $q->where('labels.id', $request->label_id);
                });
            } elseif ($request->target_type == 'custom') {
                $customerQuery->whereIn('id', $request->customer_ids ?: []);
            }

            $customerIds = $customerQuery->pluck('id');
            
            foreach ($customerIds as $customerId) {
                BroadcastRecipient::create([
                    'broadcast_id' => $broadcast->id,
                    'customer_id' => $customerId,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            // Dispatch if running immediately
            if ($broadcast->status == 'running') {
                $broadcast->update(['started_at' => now()]);
                \App\Jobs\ProcessBroadcastJob::dispatch($broadcast);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Broadcast berhasil dibuat',
                    'redirect' => route('admin.broadcasts.view', $broadcast->id)
                ]);
            }

            return redirect()->route('admin.broadcasts.view', $broadcast->id)->with('success', 'Broadcast berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat broadcast: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal membuat broadcast: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $broadcast = Broadcast::findOrFail($id);
        
        if (in_array($broadcast->status, ['completed', 'running'])) {
            return redirect()->route('admin.broadcasts.index')->with('error', 'Tidak dapat mengedit broadcast yang sedang berjalan atau sudah selesai');
        }

        $labels = Label::where('is_active', true)->get();
        $customers = Customer::where('is_archived', false)->limit(1000)->get();
        
        return view('broadcasts.edit', compact('broadcast', 'labels', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $broadcast = Broadcast::findOrFail($id);
        
        if (in_array($broadcast->status, ['completed', 'running'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat mengedit broadcast yang sedang berjalan atau sudah selesai'], 400);
            }
            return back()->with('error', 'Tidak dapat mengedit broadcast yang sedang berjalan atau sudah selesai');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'message_template' => 'required|array|min:1',
            'message_template.*' => 'required|string',
            'target_type' => 'required|in:all,label,custom',
            'delay_min' => 'required|integer|min:1',
            'delay_max' => 'required|integer|min:1|gte:delay_min',
            'media_file' => 'nullable|file|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $filters = [];
            if ($request->target_type == 'label') {
                $filters['label_id'] = $request->label_id;
            } elseif ($request->target_type == 'custom') {
                $filters['customer_ids'] = $request->customer_ids;
            }

            $status = $broadcast->status;
            // If it was scheduled and they removed schedule, it becomes draft. If they added schedule, it becomes scheduled.
            if ($broadcast->status == 'draft' || $broadcast->status == 'scheduled') {
                $status = $request->schedule_type == 'date' ? 'scheduled' : 'draft';
            }

            $broadcast->update([
                'name' => $request->name,
                'message_template' => json_encode($request->message_template),
                'target_type' => $request->target_type,
                'target_filters' => $filters,
                'delay_min' => $request->delay_min,
                'delay_max' => $request->delay_max,
                'status' => $status,
                'scheduled_at' => $request->scheduled_at,
            ]);

            // Handle Media Upload
            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                if ($file->isValid()) {
                    try {
                        $cloudinary = $this->uploadToCloudinary($file);
                        $broadcast->media_path = $cloudinary ? $cloudinary['url'] : $file->store('broadcasts/media', 'public');
                    } catch (\Exception $e) {
                        \Log::error('Broadcast Cloudinary Error: ' . $e->getMessage());
                        $broadcast->media_path = $file->store('broadcasts/media', 'public');
                    }
                    $broadcast->media_type = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';
                    $broadcast->save();
                }
            }

            // Update Recipients
            // Only recreate pending recipients
            BroadcastRecipient::where('broadcast_id', $broadcast->id)->where('status', 'pending')->delete();

            $customerQuery = Customer::where('is_archived', false);
            
            if ($request->target_type == 'label') {
                $customerQuery->whereHas('labels', function($q) use ($request) {
                    $q->where('labels.id', $request->label_id);
                });
            } elseif ($request->target_type == 'custom') {
                $customerQuery->whereIn('id', $request->customer_ids ?: []);
            }

            $customerIds = $customerQuery->pluck('id')->toArray();
            
            // Exclude already sent or failed
            $existingRecipientIds = BroadcastRecipient::where('broadcast_id', $broadcast->id)
                ->whereIn('status', ['sent', 'failed'])
                ->pluck('customer_id')
                ->toArray();
                
            $newCustomerIds = array_diff($customerIds, $existingRecipientIds);

            foreach ($newCustomerIds as $customerId) {
                BroadcastRecipient::create([
                    'broadcast_id' => $broadcast->id,
                    'customer_id' => $customerId,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Broadcast berhasil diupdate',
                    'redirect' => route('admin.broadcasts.view', $broadcast->id)
                ]);
            }

            return redirect()->route('admin.broadcasts.view', $broadcast->id)->with('success', 'Broadcast berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate broadcast: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal mengupdate broadcast: ' . $e->getMessage())->withInput();
        }
    }

    public function view($id)
    {
        $broadcast = Broadcast::with('creator')->findOrFail($id);
        
        $stats = $broadcast->recipients()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();
        
        $broadcast->stats = [
            'total' => $stats->total ?: 0,
            'sent' => $stats->sent ?: 0,
            'failed' => $stats->failed ?: 0,
            'pending' => $stats->pending ?: 0,
        ];

        $recipients = $broadcast->recipients()->with('customer')->paginate(20);
        
        // Load messages
        $customerIds = $recipients->pluck('customer_id')->toArray();
        if (!empty($customerIds)) {
            $messages = \App\Models\Message::whereIn('customer_id', $customerIds)
                ->where('user_id', $broadcast->created_by)
                ->where('direction', 'out')
                ->where('created_at', '>=', $broadcast->started_at ?? $broadcast->created_at)
                ->get()
                ->groupBy('customer_id');
                
            foreach ($recipients as $recipient) {
                if ($recipient->status == 'sent' && isset($messages[$recipient->customer_id]) && $recipient->sent_at) {
                    $recipientMsg = $messages[$recipient->customer_id]->first(function ($msg) use ($recipient) {
                        return abs($msg->created_at->diffInSeconds($recipient->sent_at)) < 120; // within 2 minutes
                    });
                    $recipient->sent_message_text = $recipientMsg ? $recipientMsg->content : '(Pesan tidak ditemukan)';
                } elseif ($recipient->status == 'failed') {
                    $recipient->sent_message_text = $recipient->error_message ?: '(Gagal)';
                } else {
                    $recipient->sent_message_text = '(Belum dikirim)';
                }
            }
        }

        return view('broadcasts.view', compact('broadcast', 'recipients'));
    }

    public function stats($id)
    {
        $broadcast = Broadcast::findOrFail($id);
        
        $stats = $broadcast->recipients()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent")
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();
        
        return response()->json([
            'status' => $broadcast->status,
            'stats' => [
                'total' => $stats->total ?: 0,
                'sent' => $stats->sent ?: 0,
                'failed' => $stats->failed ?: 0,
                'pending' => $stats->pending ?: 0,
            ]
        ]);
    }

    public function action($id, $action)
    {
        $broadcast = Broadcast::findOrFail($id);
        
        if ($action == 'start') {
            $broadcast->status = 'running';
            if (!$broadcast->started_at) {
                $broadcast->started_at = now();
            }
            $broadcast->save();

            // Dispatch Job
            \App\Jobs\ProcessBroadcastJob::dispatch($broadcast);
        } else {
            $broadcast->status = 'paused';
            $broadcast->save();
        }
        
        return redirect()->route('admin.broadcasts.view', $id);
    }

    public function destroy($id)
    {
        $broadcast = Broadcast::findOrFail($id);
        if (in_array($broadcast->status, ['draft', 'paused', 'cancelled', 'scheduled'])) {
            $broadcast->delete();
            return redirect()->route('admin.broadcasts.index')->with('success', 'Broadcast berhasil dihapus');
        }
        return back()->with('error', 'Tidak dapat menghapus broadcast yang sedang berjalan atau sudah selesai');
    }
}

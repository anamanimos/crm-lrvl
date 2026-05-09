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
        
        $globalStats = [
            'total_broadcasts' => $broadcasts->count(),
            'total_sent' => BroadcastRecipient::where('status', 'sent')->count(),
            'total_pending' => BroadcastRecipient::where('status', 'pending')->count(),
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
            'message_template' => 'required|string',
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
                'message_template' => $request->message_template,
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

        return view('broadcasts.view', compact('broadcast'));
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

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\DealActivity;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\HasCloudinary;
use Illuminate\Support\Facades\Storage;

class DealController extends Controller
{
    use HasCloudinary;

    public function index()
    {
        $stages = DealStage::where('is_active', true)->orderBy('sort_order')->get();
        $users = User::all(); // Simplified for now, adjust roles as needed
        return view('deals.index', compact('stages', 'users'));
    }

    public function getBoardData(Request $request)
    {
        $query = Deal::with(['customer', 'stage', 'assignedUser'])
            ->where('is_archived', false);

        // Role-based visibility
        if (auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin') {
            $query->where('assigned_user_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('user_id')) {
            $userIds = is_array($request->user_id) ? $request->user_id : [$request->user_id];
            $query->whereIn('assigned_user_id', $userIds);
        }

        if ($request->filled('source')) {
            $sources = is_array($request->source) ? $request->source : [$request->source];
            $query->whereIn('source', $sources);
        }

        $deals = $query->get();
        $stages = DealStage::where('is_active', true)->orderBy('sort_order')->get();

        $data = $stages->map(function ($stage) use ($deals) {
            $stageDeals = $deals->where('deal_stage_id', $stage->id)->values();
            return [
                'stage' => $stage,
                'deals' => $stageDeals,
                'total_value' => $stageDeals->sum('expected_value'),
                'count' => $stageDeals->count(),
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'expected_value' => 'nullable|numeric',
        ]);

        $defaultStage = DealStage::orderBy('sort_order')->first();

        $deal = Deal::create([
            'title' => $request->title,
            'customer_id' => $request->customer_id,
            'deal_stage_id' => $request->deal_stage_id ?? $defaultStage->id,
            'expected_value' => $request->expected_value ?? 0,
            'assigned_user_id' => $request->assigned_user_id ?? auth()->id(),
            'source' => $request->source,
            'next_followup_date' => $request->next_followup_date,
            'expected_close_date' => $request->expected_close_date,
        ]);

        DealActivity::create([
            'deal_id' => $deal->id,
            'activity_type' => 'system',
            'description' => 'Deal dibuat oleh ' . auth()->user()->name,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Deal berhasil dibuat', 'deal' => $deal]);
    }

    public function updateStage(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'stage_id' => 'required|exists:deal_stages,id',
        ]);

        $deal = Deal::findOrFail($request->deal_id);
        $oldStageName = $deal->stage->name;
        $newStage = DealStage::findOrFail($request->stage_id);

        if ($deal->deal_stage_id == $newStage->id) {
            return response()->json(['success' => true]);
        }

        $deal->update([
            'deal_stage_id' => $newStage->id,
            'lost_reason' => $request->lost_reason,
        ]);

        DealActivity::create([
            'deal_id' => $deal->id,
            'activity_type' => 'stage_change',
            'description' => "Berpindah dari $oldStageName ke {$newStage->name}" . ($request->lost_reason ? " (Alasan: {$request->lost_reason})" : ""),
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Tahapan diperbarui']);
    }

    public function detail($uuid)
    {
        $deal = Deal::with(['customer', 'stage', 'assignedUser', 'activities.creator'])->where('uuid', $uuid)->firstOrFail();
        
        // Security check
        if (auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin' && $deal->assigned_user_id !== auth()->id()) {
            abort(403);
        }

        $stages = DealStage::orderBy('sort_order')->get();
        $users = User::all();

        return view('deals.detail', compact('deal', 'stages', 'users'));
    }

    public function addActivity(Request $request, $id)
    {
        $request->validate([
            'description' => 'required_without:media_file|string|nullable',
            'media_file' => 'nullable|file|max:5120',
        ]);

        $deal = Deal::findOrFail($id);
        $fileData = null;

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid()) {
                try {
                    $cloudinary = $this->uploadToCloudinary($file);
                    if ($cloudinary) {
                        $fileData = [
                            'url' => $cloudinary['url'],
                            'name' => $file->getClientOriginalName(),
                            'type' => Str::contains($file->getMimeType(), 'image') ? 'image' : 'document'
                        ];
                    }
                } catch (\Exception $e) {
                    $path = $file->store('deals/attachments', 'public');
                    $fileData = [
                        'url' => asset('storage/' . $path),
                        'name' => $file->getClientOriginalName(),
                        'type' => Str::contains($file->getMimeType(), 'image') ? 'image' : 'document'
                    ];
                }
            }
        }

        DealActivity::create([
            'deal_id' => $deal->id,
            'activity_type' => $fileData ? 'file' : 'note',
            'description' => $request->description,
            'file_data' => $fileData,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Aktivitas berhasil ditambahkan');
    }

    public function archive($id)
    {
        $deal = Deal::findOrFail($id);
        $deal->update(['is_archived' => true]);
        return response()->json(['success' => true, 'message' => 'Deal diarsipkan']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSourceRule;

class ChatSourceRuleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            abort(403, 'Akses Ditolak');
        }

        $rules = ChatSourceRule::orderBy('created_at', 'desc')->get();
        return view('settings.chat-sources.index', compact('rules'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'keyword' => 'required|string|max:255',
            'source_name' => 'required|string|max:100',
            'match_type' => 'required|in:contains,exact,starts_with',
        ]);

        $rule = ChatSourceRule::create([
            'name' => $request->name,
            'keyword' => $request->keyword,
            'source_name' => $request->source_name,
            'match_type' => $request->match_type,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Aturan sumber chat berhasil ditambahkan!',
                'data' => [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'keyword' => $rule->keyword,
                    'source_name' => $rule->source_name,
                    'match_type' => $rule->match_type,
                    'is_active' => $rule->is_active,
                    'created_at' => $rule->created_at->format('d M Y'),
                    'update_url' => route('chat-sources.update', $rule->id),
                    'toggle_url' => route('chat-sources.toggle-status', $rule->id),
                    'destroy_url' => route('chat-sources.destroy', $rule->id),
                ]
            ]);
        }

        return back()->with('success', 'Aturan sumber chat berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        $rule = ChatSourceRule::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'keyword' => 'required|string|max:255',
            'source_name' => 'required|string|max:100',
            'match_type' => 'required|in:contains,exact,starts_with',
        ]);

        $rule->update([
            'name' => $request->name,
            'keyword' => $request->keyword,
            'source_name' => $request->source_name,
            'match_type' => $request->match_type,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Aturan sumber chat berhasil diperbarui!',
                'data' => [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'keyword' => $rule->keyword,
                    'source_name' => $rule->source_name,
                    'match_type' => $rule->match_type,
                    'is_active' => $rule->is_active,
                    'created_at' => $rule->created_at->format('d M Y'),
                ]
            ]);
        }

        return back()->with('success', 'Aturan sumber chat berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        $rule = ChatSourceRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $rule->is_active,
                'message' => 'Status aturan diperbarui.'
            ]);
        }

        return back()->with('success', 'Status aturan diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        ChatSourceRule::findOrFail($id)->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aturan berhasil dihapus.']);
        }

        return back()->with('success', 'Aturan berhasil dihapus.');
    }

    public function syncUnknown(Request $request)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        // Update all existing customers whose source is null, empty, or 'WhatsApp' to 'Unknown'
        $updatedCount = \App\Models\Customer::whereNull('source')
            ->orWhere('source', '')
            ->orWhere('source', 'WhatsApp')
            ->update(['source' => 'Unknown']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "Berhasil memperbarui {$updatedCount} customer menjadi 'Unknown'."
            ]);
        }

        return back()->with('success', "Berhasil memperbarui {$updatedCount} customer menjadi 'Unknown'.");
    }
}

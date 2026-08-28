<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiKey;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            abort(403, 'Akses Ditolak');
        }

        $apiKeys = ApiKey::orderBy('created_at', 'desc')->get();
        return view('settings.api-keys.index', compact('apiKeys'));
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
        ]);

        $key = 'ak_' . Str::random(40);

        $apiKey = ApiKey::create([
            'name' => $request->name,
            'key' => $key,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'API Key berhasil dibuat!',
                'data' => [
                    'id' => $apiKey->id,
                    'name' => $apiKey->name,
                    'key' => $apiKey->key,
                    'is_active' => $apiKey->is_active,
                    'created_at' => $apiKey->created_at->format('d M Y'),
                    'toggle_url' => route('api-keys.toggle-status', $apiKey->id),
                    'destroy_url' => route('api-keys.destroy', $apiKey->id),
                ]
            ]);
        }

        return back()->with('success', 'API Key berhasil dibuat.');
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        ApiKey::findOrFail($id)->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'API Key berhasil dihapus.']);
        }

        return back()->with('success', 'API Key dihapus.');
    }

    public function toggleStatus(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('settings.view')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak'], 403);
            }
            abort(403, 'Akses Ditolak');
        }

        $apiKey = ApiKey::findOrFail($id);
        $apiKey->is_active = !$apiKey->is_active;
        $apiKey->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $apiKey->is_active,
                'message' => 'Status API Key diperbarui.'
            ]);
        }

        return back()->with('success', 'Status API Key diperbarui.');
    }
}

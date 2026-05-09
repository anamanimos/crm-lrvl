<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AutoReply;
use Illuminate\Http\Request;
use App\Traits\HasCloudinary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AutoReplyController extends Controller
{
    use HasCloudinary;

    public function index()
    {
        $replies = AutoReply::latest()->get();
        $stats = [
            'total' => $replies->count(),
            'active' => $replies->where('is_active', true)->count(),
            'inactive' => $replies->where('is_active', false)->count(),
        ];
        return view('auto_replies.index', compact('replies', 'stats'));
    }

    public function create()
    {
        return view('auto_replies.form');
    }

    public function edit($id)
    {
        $reply = AutoReply::findOrFail($id);
        return view('auto_replies.form', compact('reply'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:keyword,first_chat,all',
            'keyword' => 'required_if:trigger_type,keyword',
            'response_messages' => 'required|array',
            'response_messages.*.content' => 'required|string',
            'media_file' => 'nullable|file|max:5120',
        ]);

        $data = $request->except(['media_file', 'active_days', 'active_times']);
        $data['is_active'] = $request->has('is_active');
        $data['active_days'] = $request->input('active_days', []);
        $data['active_times'] = array_values($request->input('active_times', [])); // Reset indices

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid()) {
                try {
                    $cloudinary = $this->uploadToCloudinary($file);
                    $data['media_path'] = $cloudinary ? $cloudinary['url'] : $file->store('auto_replies/media', 'public');
                } catch (\Exception $e) {
                    \Log::error('Cloudinary Store Error: ' . $e->getMessage());
                    $data['media_path'] = $file->store('auto_replies/media', 'public');
                }
                $data['media_type'] = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';
            }
        }

        AutoReply::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Auto reply berhasil ditambahkan', 'redirect' => route('admin.auto-replies.index')]);
        }

        return redirect()->route('admin.auto-replies.index')->with('success', 'Auto reply berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:keyword,first_chat,all',
            'keyword' => 'required_if:trigger_type,keyword',
            'response_messages' => 'required|array',
            'response_messages.*.content' => 'required|string',
            'media_file' => 'nullable|file|max:5120',
        ]);

        $reply = AutoReply::findOrFail($id);
        $data = $request->except(['media_file', 'active_days', 'active_times', 'remove_media']);
        $data['is_active'] = $request->has('is_active');
        $data['active_days'] = $request->input('active_days', []);
        $data['active_times'] = array_values($request->input('active_times', []));

        // Handle Media Removal
        if ($request->remove_media == '1') {
            $this->deleteMedia($reply->media_path);
            $data['media_path'] = null;
            $data['media_type'] = null;
        }

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid()) {
                if ($reply->media_path && $request->remove_media != '1') {
                    $this->deleteMedia($reply->media_path);
                }

                try {
                    $cloudinary = $this->uploadToCloudinary($file);
                    $data['media_path'] = $cloudinary ? $cloudinary['url'] : $file->store('auto_replies/media', 'public');
                } catch (\Exception $e) {
                    \Log::error('Cloudinary Update Error: ' . $e->getMessage());
                    $data['media_path'] = $file->store('auto_replies/media', 'public');
                }
                $data['media_type'] = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';
            }
        }

        $reply->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Auto reply berhasil diperbarui', 
                'redirect' => route('admin.auto-replies.index')
            ]);
        }

        return redirect()->route('admin.auto-replies.index')->with('success', 'Auto reply berhasil diperbarui');
    }

    public function destroy($id)
    {
        $reply = AutoReply::findOrFail($id);
        $this->deleteMedia($reply->media_path);
        $reply->delete();

        return response()->json(['success' => true, 'message' => 'Auto reply berhasil dihapus']);
    }

    public function toggleStatus($id)
    {
        $reply = AutoReply::findOrFail($id);
        $reply->update(['is_active' => !$reply->is_active]);

        return response()->json(['success' => true, 'is_active' => $reply->is_active]);
    }

    private function deleteMedia($path)
    {
        if (!$path) return;
        if (Str::contains($path, 'cloudinary.com')) {
            $publicId = $this->extractPublicId($path);
            $this->deleteFromCloudinary($publicId);
        } else {
            Storage::disk('public')->delete($path);
        }
    }
}

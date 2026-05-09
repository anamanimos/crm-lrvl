<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use App\Traits\HasCloudinary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    use HasCloudinary;

    public function index()
    {
        $templates = Template::orderBy('title', 'asc')->get();
        $stats = [
            'total' => $templates->count(),
            'active' => $templates->where('is_active', true)->count(),
            'with_media' => $templates->whereNotNull('media_path')->count(),
            'categories' => \App\Models\TemplateCategory::count(),
        ];
        return view('templates.index', compact('templates', 'stats'));
    }

    public function create()
    {
        $categories = \App\Models\TemplateCategory::all();
        return view('templates.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'shortcut' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'media_file' => 'nullable|file|max:5120', // 5MB max
        ]);

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'shortcut' => $request->shortcut,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            
            if ($file->isValid()) {
                try {
                    $cloudinary = $this->uploadToCloudinary($file);
                    if ($cloudinary) {
                        $data['media_path'] = $cloudinary['url'];
                    } else {
                        $path = $file->store('templates/media', 'public');
                        $data['media_path'] = $path;
                    }
                } catch (\Exception $e) {
                    \Log::error('Cloudinary Template Store Error: ' . $e->getMessage());
                    $path = $file->store('templates/media', 'public');
                    $data['media_path'] = $path;
                }
                
                $data['media_type'] = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';
            }
        }

        Template::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template berhasil ditambahkan', 'redirect' => route('admin.templates.index')]);
        }

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil ditambahkan');
    }

    public function edit($id)
    {
        $template = Template::findOrFail($id);
        $categories = \App\Models\TemplateCategory::all();
        return view('templates.form', compact('template', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'shortcut' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'media_file' => 'nullable|file|max:5120',
        ]);

        $template = Template::findOrFail($id);
        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'shortcut' => $request->shortcut,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
        ];

        // Handle Media Removal
        if ($request->remove_media == '1') {
            if ($template->media_path) {
                if (Str::contains($template->media_path, 'cloudinary.com')) {
                    $publicId = $this->extractPublicId($template->media_path);
                    $this->deleteFromCloudinary($publicId);
                } else {
                    Storage::disk('public')->delete($template->media_path);
                }
            }
            $data['media_path'] = null;
            $data['media_type'] = null;
        }

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid()) {
                // Delete old file if not already flagged for removal
                if ($template->media_path && $request->remove_media != '1') {
                    if (Str::contains($template->media_path, 'cloudinary.com')) {
                        $publicId = $this->extractPublicId($template->media_path);
                        $this->deleteFromCloudinary($publicId);
                    } else {
                        Storage::disk('public')->delete($template->media_path);
                    }
                }

                try {
                    $cloudinary = $this->uploadToCloudinary($file);
                    if ($cloudinary) {
                        $data['media_path'] = $cloudinary['url'];
                    } else {
                        $path = $file->store('templates/media', 'public');
                        $data['media_path'] = $path;
                    }
                } catch (\Exception $e) {
                    \Log::error('Cloudinary Template Update Error: ' . $e->getMessage());
                    $path = $file->store('templates/media', 'public');
                    $data['media_path'] = $path;
                }

                $data['media_type'] = Str::contains($file->getMimeType(), 'image') ? 'image' : 'document';
            }
        }

        $template->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template berhasil diperbarui', 'redirect' => route('admin.templates.index')]);
        }

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diperbarui');
    }

    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        
        if ($template->media_path) {
            if (Str::contains($template->media_path, 'cloudinary.com')) {
                $publicId = $this->extractPublicId($template->media_path);
                $this->deleteFromCloudinary($publicId);
            } else {
                Storage::disk('public')->delete($template->media_path);
            }
        }
        
        $template->delete();

        return response()->json(['success' => true, 'message' => 'Template berhasil dihapus']);
    }

    public function toggle_status($id)
    {
        $template = Template::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        return response()->json(['success' => true]);
    }
}

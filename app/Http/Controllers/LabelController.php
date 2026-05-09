<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::withCount('customers')->orderBy('name', 'asc')->get();
        return view('labels.index', compact('labels'));
    }

    public function create()
    {
        return view('labels.form');
    }

    public function edit($id)
    {
        $label = Label::findOrFail($id);
        return view('labels.form', compact('label'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        Label::create($request->all());

        return redirect()->route('admin.labels.index')->with('success', 'Label berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $label = Label::findOrFail($id);
        $label->update($request->all());

        return redirect()->route('admin.labels.index')->with('success', 'Label berhasil diperbarui');
    }

    public function destroy($id)
    {
        $label = Label::findOrFail($id);
        $label->delete();

        return response()->json(['success' => true, 'message' => 'Label berhasil dihapus']);
    }

    public function toggle_status($id)
    {
        $label = Label::findOrFail($id);
        $label->is_active = !$label->is_active;
        $label->save();

        return response()->json(['success' => true]);
    }
}

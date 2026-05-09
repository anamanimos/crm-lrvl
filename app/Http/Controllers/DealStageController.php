<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DealStage;
use Illuminate\Http\Request;

class DealStageController extends Controller
{
    public function index()
    {
        $stages = DealStage::orderBy('sort_order')->get();
        return view('deals.stages', compact('stages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:20',
        ]);

        $maxOrder = DealStage::max('sort_order') ?? 0;

        DealStage::create([
            'name' => $request->name,
            'color' => $request->color,
            'stage_type' => $request->stage_type ?? 'pipeline',
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Tahapan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $stage = DealStage::findOrFail($id);
        $stage->update($request->only(['name', 'color', 'stage_type', 'is_active']));
        
        return response()->json(['success' => true, 'message' => 'Tahapan diperbarui']);
    }

    public function reorder(Request $request)
    {
        $order = $request->order; // Array of IDs
        foreach ($order as $index => $id) {
            DealStage::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $stage = DealStage::findOrFail($id);
        if ($stage->deals()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus tahapan yang masih memiliki deals']);
        }
        $stage->delete();
        return response()->json(['success' => true, 'message' => 'Tahapan dihapus']);
    }
}

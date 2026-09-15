<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::withCount('customers')->orderBy('order_index', 'asc')->orderBy('name', 'asc')->get();
        return view('labels.index', compact('labels'));
    }

    public function create()
    {
        return redirect()->route('admin.labels.index')->with('info', 'Label dikelola dan disinkronisasikan otomatis dari WhatsApp Business.');
    }

    public function edit($id)
    {
        $label = Label::findOrFail($id);
        return redirect()->route('admin.labels.index')->with('info', 'Label dikelola dan disinkronisasikan otomatis dari WhatsApp Business.');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.labels.index')->with('info', 'Label dikelola dan disinkronisasikan otomatis dari WhatsApp Business.');
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

    public function reset(Request $request)
    {
        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \Illuminate\Support\Facades\DB::table('customer_labels')->delete();
            Label::query()->delete();
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->route('admin.labels.index')->with('success', 'Semua data label berhasil dikosongkan. Label baru akan otomatis terpetakan saat ada aktivitas label di WhatsApp.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mereset label: ' . $e->getMessage());
            return redirect()->route('admin.labels.index')->with('error', 'Gagal mengosongkan label: ' . $e->getMessage());
        }
    }
}

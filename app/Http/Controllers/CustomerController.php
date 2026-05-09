<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Label;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['labels', 'assignedUser', 'company']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('wa_number', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });

        }

        if ($request->filled('label')) {
            $query->whereHas('labels', function($q) use ($request) {
                $q->where('labels.id', $request->label);
            });
        }

        if ($request->filled('archive')) {
            $query->where('is_archived', $request->archive);
        } else {
            $query->where('is_archived', 0);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();
        $labels = Label::withCount('customers')->get();

        // Stats
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('is_archived', 0)->count(),
            'archived' => Customer::where('is_archived', 1)->count(),
            'labels' => $labels
        ];

        return view('customers.index', compact('customers', 'labels', 'stats'));
    }

    public function create()
    {
        $labels = Label::all();
        $users = User::whereIn('role', ['superadmin', 'admin', 'cs', 'sales'])->get();
        $companies = Company::all();
        $customer = null;

        return view('customers.form', compact('labels', 'users', 'companies', 'customer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'wa_number' => 'required',
            'name' => 'nullable|string|max:255',
        ]);

        $phone = format_phone($request->wa_number);

        // Check if exists
        $existing = Customer::where('wa_number', $phone)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Nomor WhatsApp sudah terdaftar.');
        }

        try {
            DB::beginTransaction();

            $company_id = null;
            if ($request->filled('company_id')) {
                // Check if numeric or new tag
                if (is_numeric($request->company_id)) {
                    $company_id = $request->company_id;
                } else {
                    $company_id = Company::findOrCreateByName($request->company_id);
                }
            }

            $customer = Customer::create([
                'uuid' => (string) Str::uuid(),
                'wa_number' => $phone,
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'notes' => $request->notes,
                'company_id' => $company_id,
                'assigned_user_id' => $request->assigned_user_id,
            ]);

            if ($request->has('labels')) {
                $customer->labels()->sync($request->labels);
            }

            DB::commit();
            return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $customer = Customer::with('labels')->findOrFail($id);
        $labels = Label::all();
        $users = User::whereIn('role', ['superadmin', 'admin', 'cs', 'sales'])->get();
        $companies = Company::all();

        return view('customers.form', compact('customer', 'labels', 'users', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'wa_number' => 'required',
            'name' => 'nullable|string|max:255',
        ]);

        $phone = format_phone($request->wa_number);

        // Check if exists other than this
        $existing = Customer::where('wa_number', $phone)->where('id', '!=', $id)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Nomor WhatsApp sudah terdaftar pada customer lain.');
        }

        try {
            DB::beginTransaction();

            $company_id = null;
            if ($request->filled('company_id')) {
                if (is_numeric($request->company_id)) {
                    $company_id = $request->company_id;
                } else {
                    $company_id = Company::findOrCreateByName($request->company_id);
                }
            }

            $customer->update([
                'wa_number' => $phone,
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'notes' => $request->notes,
                'company_id' => $company_id,
                'assigned_user_id' => $request->assigned_user_id,
            ]);

            $customer->labels()->sync($request->input('labels', []));

            DB::commit();
            return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer berhasil dihapus.');
    }

    public function archive($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->is_archived = !$customer->is_archived;
        $customer->save();

        $status = $customer->is_archived ? 'diarsipkan' : 'dipulihkan';
        return redirect()->route('admin.customers.index')->with('success', "Customer berhasil $status.");
    }

    public function show($id)
    {
        $customer = Customer::with(['labels', 'deals.stage', 'messages' => function($q) {
            $q->latest()->limit(50);
        }])->findOrFail($id);

        $labels = Label::all();

        return view('customers.show', compact('customer', 'labels'));
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $customers = Customer::where('name', 'like', "%$search%")
            ->orWhere('wa_number', 'like', "%$search%")
            ->limit(20)
            ->get(['id', 'name', 'wa_number']);

        $results = $customers->map(function($c) {
            return [
                'id' => $c->id,
                'text' => $c->name . ($c->wa_number ? " ({$c->wa_number})" : "")
            ];
        });

        return response()->json(['results' => $results]);
    }
}

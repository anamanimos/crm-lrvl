<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Label;
use App\Models\Company;
use App\Models\User;
use App\Models\ChatSourceRule;
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

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('archive')) {
            $query->where('is_archived', $request->archive);
        } else {
            $query->where('is_archived', 0);
        }

        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }

        $customers = $query->latest()->paginate($perPage)->withQueryString();
        $labels = Label::withCount('customers')->get();

        $sources = ChatSourceRule::pluck('source_name')
            ->concat(['TikTok', 'Instagram', 'Facebook Ads', 'Website', 'WhatsApp', 'Referral', 'Unknown'])
            ->unique()
            ->values();

        // Stats
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('is_archived', 0)->count(),
            'archived' => Customer::where('is_archived', 1)->count(),
            'labels' => $labels
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('customers._table', compact('customers'))->render(),
                'total' => $customers->total(),
                'first_item' => $customers->firstItem() ?? 0,
                'last_item' => $customers->lastItem() ?? 0,
            ]);
        }

        return view('customers.index', compact('customers', 'labels', 'sources', 'stats'));
    }

    public function create()
    {
        $labels = Label::all();
        $users = User::whereIn('role', ['superadmin', 'admin', 'cs', 'sales'])->get();
        $companies = Company::all();
        $sources = \App\Models\ChatSourceRule::pluck('source_name')
            ->concat(['TikTok', 'Instagram', 'Facebook Ads', 'Website', 'WhatsApp', 'Referral', 'Unknown'])
            ->unique()
            ->values();
        $customer = null;

        return view('customers.form', compact('labels', 'users', 'companies', 'sources', 'customer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'wa_number' => 'required',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
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
                'source' => $request->source,
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
        $sources = \App\Models\ChatSourceRule::pluck('source_name')
            ->concat(['TikTok', 'Instagram', 'Facebook Ads', 'Website', 'WhatsApp', 'Referral', 'Unknown'])
            ->unique()
            ->values();

        return view('customers.form', compact('customer', 'labels', 'users', 'companies', 'sources'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'wa_number' => 'required',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
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
                'source' => $request->source,
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

    /**
     * Public API: List customers with ID, WhatsApp, Source, and Created At.
     */
    public function apiCustomers(Request $request)
    {
        $query = Customer::query();

        // Optional filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Optional date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sorting
        $query->orderBy('id', 'asc');

        // Optional limit
        if ($request->filled('limit') && is_numeric($request->limit)) {
            $customers = $query->limit((int) $request->limit)->get();
        } else {
            $customers = $query->get();
        }

        $data = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'whatsapp' => $customer->wa_number,
                'source' => $customer->source ?: 'Unknown',
                'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d') : null,
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Public API: Get single customer detail by ID or WhatsApp number.
     */
    public function apiShow(Request $request, $id)
    {
        $phone = format_phone($id);
        $customer = Customer::with(['labels', 'company', 'assignedUser'])
            ->where('id', $id)
            ->orWhere('wa_number', $id)
            ->orWhere('wa_number', $phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'whatsapp' => $customer->wa_number,
                'email' => $customer->email,
                'source' => $customer->source ?: 'Unknown',
                'address' => $customer->address,
                'notes' => $customer->notes,
                'company' => $customer->company ? [
                    'id' => $customer->company->id,
                    'name' => $customer->company->name,
                ] : null,
                'assigned_user' => $customer->assignedUser ? [
                    'id' => $customer->assignedUser->id,
                    'name' => $customer->assignedUser->name,
                ] : null,
                'labels' => $customer->labels->map(function ($label) {
                    return [
                        'id' => $label->id,
                        'name' => $label->name,
                        'color' => $label->color,
                    ];
                }),
                'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $customer->updated_at ? $customer->updated_at->format('Y-m-d H:i:s') : null,
            ]
        ]);
    }

    /**
     * Public API: Create new customer.
     */
    public function apiStore(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'whatsapp' => 'required_without:wa_number',
            'wa_number' => 'required_without:whatsapp',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'company_id' => 'nullable',
            'labels' => 'nullable|array',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = format_phone($request->input('whatsapp', $request->input('wa_number')));
        $existing = Customer::where('wa_number', $phone)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp sudah terdaftar.',
                'customer_id' => $existing->id
            ], 422);
        }

        try {
            DB::beginTransaction();

            $company_id = null;
            if ($request->has('company') || $request->has('company_id')) {
                $companyVal = $request->input('company_id', $request->input('company'));
                if (is_numeric($companyVal)) {
                    $company_id = (int) $companyVal;
                } elseif (!empty($companyVal)) {
                    $company_id = Company::findOrCreateByName($companyVal);
                }
            }

            $customer = Customer::create([
                'uuid' => (string) Str::uuid(),
                'wa_number' => $phone,
                'name' => $request->name,
                'email' => $request->email,
                'source' => $request->source ?: 'Unknown',
                'address' => $request->address,
                'notes' => $request->notes,
                'company_id' => $company_id,
                'assigned_user_id' => $request->assigned_user_id,
            ]);

            if ($request->has('labels')) {
                $labelIds = [];
                foreach ($request->input('labels') as $l) {
                    if (is_numeric($l)) {
                        $labelIds[] = (int) $l;
                    } elseif (is_string($l) && !empty(trim($l))) {
                        $label = Label::firstOrCreate(
                            ['name' => trim($l)],
                            ['color' => '#' . substr(md5(trim($l)), 0, 6)]
                        );
                        $labelIds[] = $label->id;
                    }
                }
                $customer->labels()->sync($labelIds);
            }

            DB::commit();

            $customer->load(['labels', 'company', 'assignedUser']);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil dibuat.',
                'data' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'whatsapp' => $customer->wa_number,
                    'email' => $customer->email,
                    'source' => $customer->source,
                    'address' => $customer->address,
                    'notes' => $customer->notes,
                    'company' => $customer->company ? [
                        'id' => $customer->company->id,
                        'name' => $customer->company->name,
                    ] : null,
                    'assigned_user' => $customer->assignedUser ? [
                        'id' => $customer->assignedUser->id,
                        'name' => $customer->assignedUser->name,
                    ] : null,
                    'labels' => $customer->labels->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name,
                            'color' => $label->color,
                        ];
                    }),
                    'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : null,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public API: Update customer details by ID or WhatsApp number.
     */
    public function apiUpdate(Request $request, $id)
    {
        $phone = format_phone($id);
        $customer = Customer::where('id', $id)
            ->orWhere('wa_number', $id)
            ->orWhere('wa_number', $phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:50',
            'wa_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'company_id' => 'nullable',
            'labels' => 'nullable|array',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [];
        if ($request->has('name')) {
            $data['name'] = $request->name;
        }

        $newPhone = $request->input('whatsapp', $request->input('wa_number'));
        if ($newPhone !== null) {
            $formattedPhone = format_phone($newPhone);
            $existing = Customer::where('wa_number', $formattedPhone)->where('id', '!=', $customer->id)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp sudah digunakan oleh customer lain.'
                ], 422);
            }
            $data['wa_number'] = $formattedPhone;
        }

        if ($request->has('email')) {
            $data['email'] = $request->email;
        }
        if ($request->has('source')) {
            $data['source'] = $request->source;
        }
        if ($request->has('address')) {
            $data['address'] = $request->address;
        }
        if ($request->has('notes')) {
            $data['notes'] = $request->notes;
        }
        if ($request->has('assigned_user_id')) {
            $data['assigned_user_id'] = $request->assigned_user_id;
        }

        if ($request->has('company') || $request->has('company_id')) {
            $companyVal = $request->input('company_id', $request->input('company'));
            if ($companyVal === null || $companyVal === '') {
                $data['company_id'] = null;
            } elseif (is_numeric($companyVal)) {
                $data['company_id'] = (int) $companyVal;
            } else {
                $data['company_id'] = Company::findOrCreateByName($companyVal);
            }
        }

        try {
            DB::beginTransaction();

            if (!empty($data)) {
                $customer->update($data);
            }

            if ($request->has('labels')) {
                $labelInput = $request->input('labels');
                $labelIds = [];
                foreach ($labelInput as $l) {
                    if (is_numeric($l)) {
                        $labelIds[] = (int) $l;
                    } elseif (is_string($l) && !empty(trim($l))) {
                        $label = Label::firstOrCreate(
                            ['name' => trim($l)],
                            ['color' => '#' . substr(md5(trim($l)), 0, 6)]
                        );
                        $labelIds[] = $label->id;
                    }
                }
                $customer->labels()->sync($labelIds);
            }

            DB::commit();

            $customer->load(['labels', 'company', 'assignedUser']);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil diperbarui.',
                'data' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'whatsapp' => $customer->wa_number,
                    'email' => $customer->email,
                    'source' => $customer->source ?: 'Unknown',
                    'address' => $customer->address,
                    'notes' => $customer->notes,
                    'company' => $customer->company ? [
                        'id' => $customer->company->id,
                        'name' => $customer->company->name,
                    ] : null,
                    'assigned_user' => $customer->assignedUser ? [
                        'id' => $customer->assignedUser->id,
                        'name' => $customer->assignedUser->name,
                    ] : null,
                    'labels' => $customer->labels->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name,
                            'color' => $label->color,
                        ];
                    }),
                    'created_at' => $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $customer->updated_at ? $customer->updated_at->format('Y-m-d H:i:s') : null,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public API: List all customer sources with total counts.
     */
    public function apiSources(Request $request)
    {
        // Get customer count grouped by source
        $customerCounts = Customer::select('source', DB::raw('count(*) as total'))
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->toArray();

        // Get defined sources from ChatSourceRule and default list
        $ruleSources = ChatSourceRule::pluck('source_name')->toArray();
        $defaultSources = ['TikTok', 'Instagram', 'Facebook Ads', 'Website', 'WhatsApp', 'Referral', 'Unknown'];

        $allSourceNames = collect(array_keys($customerCounts))
            ->merge($ruleSources)
            ->merge($defaultSources)
            ->unique()
            ->values();

        $data = $allSourceNames->map(function ($name) use ($customerCounts) {
            return [
                'name' => $name,
                'total_customers' => $customerCounts[$name] ?? 0,
            ];
        })->sortByDesc('total_customers')->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'sources' => $data->pluck('name')->values(),
        ]);
    }
}

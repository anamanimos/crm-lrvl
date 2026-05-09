<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Label;
use App\Models\Company;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    protected $erp_api_url;
    protected $erp_api_key;

    public function __construct()
    {
        $this->erp_api_url = Setting::get('erp_api_url', '');
        $this->erp_api_key = Setting::get('erp_api_key', '');
    }

    public function index()
    {
        // Get counts from ERP
        $erp_count = 0;
        $erp_error = null;
        try {
            $response = $this->apiRequest('contacts/stats');
            $erp_count = $response['data']['total'] ?? 0;
        } catch (\Exception $e) {
            $erp_count = 0;
            $erp_error = "Gagal terhubung ke API ERP: " . $e->getMessage();
        }

        $crm_count = Customer::count();
        $labels = Label::all();

        return view('customers.import', compact('erp_count', 'crm_count', 'labels', 'erp_error'));
    }

    public function preview(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $limit = $request->get('limit', 50);

        try {
            $contacts = $this->apiGetContacts($search, $status, $limit);

            // Check which are already imported
            $existing = [];
            if (!empty($contacts)) {
                $phones = array_map(function($c) {
                    return format_phone($c['phone'] ?? '');
                }, $contacts);

                $existing = Customer::whereIn('wa_number', array_filter($phones))
                    ->pluck('wa_number')
                    ->toArray();
            }

            // Mark existing and format
            $result = [];
            foreach ($contacts as $c) {
                $formatted_phone = format_phone($c['phone'] ?? '');
                $result[] = [
                    'id' => $c['id'] ?? null,
                    'uuid' => $c['uuid'] ?? null,
                    'full_name' => $c['full_name'] ?? $c['name'] ?? '',
                    'phone' => $c['phone'] ?? '',
                    'formatted_phone' => $formatted_phone,
                    'company' => $c['company'] ?? '',
                    'email' => $c['email'] ?? '',
                    'address' => $c['address'] ?? '',
                    'status' => $c['status'] ?? '',
                    'already_exists' => in_array($formatted_phone, $existing)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'total' => count($result)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $contact_ids = $request->input('contact_ids');
        $label_ids = $request->input('labels', []);
        $check_conflicts = $request->input('check_conflicts') === '1';
        $resolutions = $request->input('resolutions', []);
        $skip_existing_default = $request->input('skip_existing') !== '0';

        if (empty($contact_ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada kontak dipilih']);
        }

        // Get contacts from ERP
        try {
            $erp_contacts = $this->apiGetByIds($contact_ids);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data dari ERP']);
        }

        // Conflict Detection
        if ($check_conflicts) {
            $phones = [];
            foreach ($erp_contacts as $c) {
                $phone = format_phone($c['phone'] ?? '');
                if ($phone) $phones[] = $phone;
            }

            $conflicts = [];
            if (!empty($phones)) {
                $existing_customers = Customer::with('company')
                    ->whereIn('wa_number', $phones)
                    ->get()
                    ->keyBy('wa_number');

                foreach ($erp_contacts as $c) {
                    $phone = format_phone($c['phone'] ?? '');
                    if ($existing_customers->has($phone)) {
                        $existing = $existing_customers->get($phone);
                        $conflicts[] = [
                            'erp_id' => $c['id'],
                            'phone' => $phone,
                            'new' => [
                                'name' => $c['full_name'] ?? $c['name'] ?? '',
                                'company' => $c['company'] ?? '-',
                                'email' => $c['email'] ?? '-'
                            ],
                            'existing' => [
                                'name' => $existing->name,
                                'company' => $existing->company->name ?? '-',
                                'email' => $existing->email ?: '-'
                            ]
                        ];
                    }
                }
            }

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => true,
                    'status' => 'conflict',
                    'conflicts' => $conflicts,
                    'message' => count($conflicts) . ' data duplikat ditemukan.'
                ]);
            }
        }

        // Import Execution
        $imported = 0;
        $skipped = 0;
        $updated = 0;

        foreach ($erp_contacts as $c) {
            $phone = format_phone($c['phone'] ?? '');
            if (empty($phone)) {
                $skipped++;
                continue;
            }

            $existing = Customer::where('wa_number', $phone)->first();

            if ($existing) {
                $action = 'skip';
                if (isset($resolutions[$c['id']])) {
                    $action = $resolutions[$c['id']];
                } elseif (!$check_conflicts && !$skip_existing_default) {
                    $action = 'overwrite';
                }

                if ($action === 'overwrite') {
                    $existing->update([
                        'name' => $c['full_name'] ?? $c['name'] ?? '',
                        'email' => $c['email'] ?? null,
                        'address' => $c['address'] ?? null,
                        'dob' => $c['dob'] ?? null,
                        'gender' => $c['gender'] ?? null,
                        'notes' => $this->buildNotes($c),
                        'company_id' => Company::findOrCreateByName($c['company'] ?? null)
                    ]);

                    if (!empty($label_ids)) {
                        $existing->labels()->sync($label_ids);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                $customer = Customer::create([
                    'uuid' => $c['uuid'] ?? null,
                    'wa_number' => $phone,
                    'name' => $c['full_name'] ?? $c['name'] ?? '',
                    'email' => $c['email'] ?? null,
                    'address' => $c['address'] ?? null,
                    'dob' => $c['dob'] ?? null,
                    'gender' => $c['gender'] ?? null,
                    'notes' => $this->buildNotes($c),
                    'company_id' => Company::findOrCreateByName($c['company'] ?? null)
                ]);

                if (!empty($label_ids)) {
                    $customer->labels()->sync($label_ids);
                }
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'status' => 'completed',
            'message' => "Proses Selesai: $imported baru, $updated diperbarui, $skipped dilewati.",
            'stats' => [
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped
            ]
        ]);
    }

    public function batch(Request $request)
    {
        $page = $request->get('page', 1);
        $strategy = $request->get('strategy', 'skip'); // 'skip' or 'overwrite'
        $limit = 50;

        try {
            $contacts = $this->apiGetContacts(null, null, $limit, $page);
            $affected = 0;

            if (!empty($contacts)) {
                foreach ($contacts as $c) {
                    $phone = format_phone($c['phone'] ?? '');
                    if (empty($phone)) continue;

                    $existing = Customer::where('wa_number', $phone)->first();

                    if ($existing) {
                        if ($strategy === 'overwrite') {
                            $existing->update([
                                'name' => $c['full_name'] ?? $c['name'] ?? '',
                                'email' => $c['email'] ?? null,
                                'address' => $c['address'] ?? null,
                                'dob' => $c['dob'] ?? null,
                                'gender' => $c['gender'] ?? null,
                                'notes' => $this->buildNotes($c),
                                'company_id' => Company::findOrCreateByName($c['company'] ?? null)
                            ]);
                            $affected++;
                        }
                    } else {
                        Customer::create([
                            'uuid' => $c['uuid'] ?? null,
                            'wa_number' => $phone,
                            'name' => $c['full_name'] ?? $c['name'] ?? '',
                            'email' => $c['email'] ?? null,
                            'address' => $c['address'] ?? null,
                            'dob' => $c['dob'] ?? null,
                            'gender' => $c['gender'] ?? null,
                            'notes' => $this->buildNotes($c),
                            'company_id' => Company::findOrCreateByName($c['company'] ?? null)
                        ]);
                        $affected++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'count' => count($contacts),
                'inserted' => $affected,
                'page' => $page,
                'has_more' => count($contacts) >= $limit
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resetTruncate()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            Customer::truncate();
            DB::table('customer_labels')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            return response()->json(['success' => true, 'message' => 'Database dibersihkan. Memulai import...']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Helper Methods

    private function apiRequest($endpoint, $params = [])
    {
        $url = rtrim($this->erp_api_url, '/') . '/' . ltrim($endpoint, '/');
        
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-API-Key' => $this->erp_api_key
        ])->get($url, $params);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('ERP API Error: ' . $response->body());
    }

    private function apiGetContacts($search, $status, $limit, $page = 1)
    {
        $params = ['per_page' => $limit, 'page' => $page];
        if ($search) $params['search'] = $search;
        if ($status) $params['status'] = $status;

        $result = $this->apiRequest('contacts', $params);
        return $result['data'] ?? [];
    }

    private function apiGetByIds($ids)
    {
        $result = $this->apiRequest('contacts/by-ids', ['ids' => implode(',', $ids)]);
        return $result['data'] ?? [];
    }

    private function buildNotes($contact)
    {
        $notes = [];
        $contact = (object)$contact;
        
        if (!empty($contact->id)) {
            $notes[] = "ERP ID: " . $contact->id;
        }
        if (!empty($contact->company)) {
            $notes[] = "Perusahaan: " . $contact->company;
        }
        if (!empty($contact->email)) {
            $notes[] = "Email: " . $contact->email;
        }
        if (!empty($contact->status)) {
            $notes[] = "Status ERP: " . ucfirst($contact->status);
        }
        
        return implode("\n", $notes);
    }
}

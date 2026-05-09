<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('name', 'asc')->get();
        $stats = [
            'total' => $companies->count(),
            'with_email' => $companies->whereNotNull('email')->count(),
            'with_phone' => $companies->whereNotNull('phone')->count(),
        ];
        return view('companies.index', compact('companies', 'stats'));
    }

    public function create()
    {
        return view('companies.form');
    }

    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.form', compact('company'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Company::create($request->all());

        return redirect()->route('admin.companies.index')->with('success', 'Perusahaan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::findOrFail($id);
        $company->update($request->all());

        return redirect()->route('admin.companies.index')->with('success', 'Perusahaan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Perusahaan berhasil dihapus');
    }
}

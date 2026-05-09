<x-metronic-layout>
    @php
        $title = isset($company) ? 'Edit Perusahaan' : 'Tambah Perusahaan';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $title }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.companies.index') }}" class="text-muted text-hover-primary">Perusahaan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">{{ isset($company) ? 'Edit' : 'Tambah' }}</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-sm fw-bold btn-light">
                    <i class="ki-outline ki-arrow-left fs-4"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <form action="{{ isset($company) ? route('admin.companies.update', $company->id) : route('admin.companies.store') }}" method="POST" id="kt_company_form">
                @csrf
                
                <div class="card">
                    <div class="card-body p-9">
                        
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Perusahaan</label>
                            <div class="col-lg-8">
                                <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror" placeholder="Nama Perusahaan" value="{{ old('name', $company->name ?? '') }}" required />
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Email</label>
                            <div class="col-lg-8">
                                <input type="email" name="email" class="form-control form-control-solid @error('email') is-invalid @enderror" placeholder="Email Perusahaan" value="{{ old('email', $company->email ?? '') }}" />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Nomor Telepon</label>
                            <div class="col-lg-8">
                                <input type="text" name="phone" class="form-control form-control-solid @error('phone') is-invalid @enderror" placeholder="021-xxxxxx" value="{{ old('phone', $company->phone ?? '') }}" />
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Website</label>
                            <div class="col-lg-8">
                                <input type="text" name="website" class="form-control form-control-solid @error('website') is-invalid @enderror" placeholder="https://example.com" value="{{ old('website', $company->website ?? '') }}" />
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Alamat</label>
                            <div class="col-lg-8">
                                <textarea name="address" class="form-control form-control-solid @error('address') is-invalid @enderror" rows="3" placeholder="Alamat lengkap...">{{ old('address', $company->address ?? '') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Reset</button>
                        <button type="submit" class="btn btn-primary" id="kt_company_submit">Simpan</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-metronic-layout>

<x-metronic-layout>
    @php
        $title = $customer ? 'Edit Customer' : 'Tambah Customer';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $customer ? 'Edit Customer' : 'Tambah Customer' }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.customers.index') }}" class="text-muted text-hover-primary">Customer</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ $customer ? 'Edit' : 'Tambah' }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ $customer ? route('admin.customers.update', $customer->id) : route('admin.customers.store') }}" method="POST" class="form">
                @csrf
                
                <div class="row g-5">
                    <!--begin::Main Column-->
                    <div class="col-xl-8">
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Informasi Customer</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="required form-label">Nomor WhatsApp</label>
                                    <input type="text" name="wa_number" class="form-control mb-2 @error('wa_number') is-invalid @enderror" 
                                           placeholder="Contoh: 081234567890" 
                                           value="{{ old('wa_number', $customer->wa_number ?? '') }}" required />
                                    @error('wa_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted fs-7">Format: 08xxx atau 628xxx</div>
                                </div>
                                <!--end::Input group-->
                                
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="form-label">Nama Customer</label>
                                    <input type="text" name="name" class="form-control mb-2 @error('name') is-invalid @enderror" 
                                           placeholder="Nama customer" 
                                           value="{{ old('name', $customer->name ?? '') }}" />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control mb-2" 
                                           placeholder="Email customer" 
                                           value="{{ old('email', $customer->email ?? '') }}" />
                                </div>
                                <!--end::Input group-->
                                
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="address" class="form-control mb-2" rows="2" 
                                              placeholder="Alamat customer...">{{ old('address', $customer->address ?? '') }}</textarea>
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="form-label">Catatan</label>
                                    <textarea name="notes" class="form-control mb-2" rows="4" 
                                              placeholder="Catatan tentang customer...">{{ old('notes', $customer->notes ?? '') }}</textarea>
                                </div>
                                <!--end::Input group-->
                            </div>
                        </div>
                    </div>
                    <!--end::Main Column-->
                    
                    <!--begin::Side Column-->
                    <div class="col-xl-4">
                        <!--begin::Status Card-->
                        <div class="card card-flush py-4 mb-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Assignment</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                
                                <!--begin::Select Company-->
                                <div class="mb-10">
                                    <label class="form-label">Perusahaan</label>
                                    <select name="company_id" class="form-select" data-control="select2" 
                                            data-placeholder="Pilih atau Ketik Perusahaan Baru" 
                                            data-allow-clear="true" data-tags="true">
                                        <option value="">-- Pilih Perusahaan --</option>
                                        @foreach ($companies as $comp)
                                        <option value="{{ $comp->id }}" 
                                                {{ (old('company_id', $customer->company_id ?? '') == $comp->id) ? 'selected' : '' }}>
                                            {{ $comp->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="text-muted fs-7 mt-2">Ketik nama baru untuk membuat perusahaan otomatis.</div>
                                </div>
                                <!--end::Select Company-->

                                <!--begin::Select User-->
                                <div class="mb-10">
                                    <label class="form-label">Assign ke</label>
                                    <select name="assigned_user_id" class="form-select" data-control="select2" 
                                            data-placeholder="Pilih user">
                                        <option value="">-- Pilih User --</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ (old('assigned_user_id', $customer->assigned_user_id ?? '') == $user->id) ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ ucfirst($user->role) }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Select User-->
                            </div>
                        </div>
                        <!--end::Status Card-->
                        
                        <!--begin::Labels Card-->
                        <div class="card card-flush py-4 mb-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Label</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="d-flex flex-wrap gap-3">
                                    @php
                                        $selected_labels = old('labels', $customer ? $customer->labels->pluck('id')->toArray() : []);
                                    @endphp
                                    @foreach ($labels as $label)
                                    <label class="form-check form-check-custom form-check-solid form-check-sm">
                                        <input class="form-check-input" type="checkbox" name="labels[]" 
                                               value="{{ $label->id }}" 
                                               {{ in_array($label->id, $selected_labels) ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            <span class="badge" style="background-color: {{ $label->color }}20; color: {{ $label->color }}">
                                                {{ $label->name }}
                                            </span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                                @if ($labels->isEmpty())
                                <div class="text-muted fs-7">
                                    Belum ada label. <a href="{{ url('admin/labels') }}">Buat label</a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <!--end::Labels Card-->
                        
                        <!--begin::Actions Card-->
                        <div class="card card-flush py-4">
                            <div class="card-body pt-0">
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
                                        Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!--end::Actions Card-->
                    </div>
                    <!--end::Side Column-->
                </div>
            </form>

        </div>
    </div>
    <!--end::Content-->
</x-metronic-layout>

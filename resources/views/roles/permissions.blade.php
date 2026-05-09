<x-metronic-layout>
    @php
        $title = 'Set Permissions: ' . $role->name;
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
                        <a href="{{ route('admin.roles.index') }}" class="text-muted text-hover-primary">Role</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Permissions</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-light">
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
            
            <!--begin::Role Info-->
            <div class="card mb-5">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-50px me-5">
                            <div class="symbol-label fs-2 fw-bold bg-light-primary text-primary">
                                <i class="ki-outline ki-shield-tick fs-1"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fs-4 fw-bold">{{ $role->name }}</span>
                            <span class="text-muted fs-7">{{ $role->description ?: 'Tidak ada deskripsi' }}</span>
                        </div>
                        @if ($role->is_system)
                        <span class="badge badge-light-warning ms-auto">Role Sistem</span>
                        @endif
                    </div>
                </div>
            </div>
            <!--end::Role Info-->

            <!--begin::Permissions Card-->
            <div class="card">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Hak Akses</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Centang permission yang ingin diberikan ke role ini</span>
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" id="btn-select-all">
                            <i class="ki-outline ki-check-square fs-4"></i>
                            Pilih Semua
                        </button>
                        <button type="button" class="btn btn-sm btn-light ms-2" id="btn-deselect-all">
                            <i class="ki-outline ki-cross-square fs-4"></i>
                            Hapus Semua
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form id="permissions-form">
                        @csrf
                        <input type="hidden" name="role_id" value="{{ $role->id }}">
                        
                        <div class="row">
                            @foreach ($permissions_grouped as $module => $permissions)
                            <div class="col-lg-4 col-md-6 mb-7">
                                <div class="card card-bordered h-100">
                                    <div class="card-header py-3 min-h-auto">
                                        <h5 class="card-title mb-0">
                                            <label class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input module-checkbox" type="checkbox" 
                                                       data-module="{{ $module }}">
                                                <span class="form-check-label fw-bold">{{ $module }}</span>
                                            </label>
                                        </h5>
                                    </div>
                                    <div class="card-body py-3">
                                        @foreach ($permissions as $permission)
                                        <div class="d-flex align-items-center mb-3">
                                            <label class="form-check form-check-custom form-check-solid flex-grow-1">
                                                <input class="form-check-input permission-checkbox" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       data-module="{{ $module }}"
                                                       {{ in_array($permission->id, $role_permission_ids) ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ $permission->name }}
                                                    <code class="ms-2 text-muted fs-8">{{ $permission->slug }}</code>
                                                </span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="separator my-5"></div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-light me-3">Batal</a>
                            <button type="submit" class="btn btn-primary" id="btn-save">
                                <i class="ki-outline ki-check fs-4"></i>
                                Simpan Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Permissions Card-->

        </div>
    </div>
    <!--end::Content-->
    @push('js')
    <script>
    $(document).ready(function() {
        var appUrl = "{{ url('/') }}/";

        // Select all button
        $('#btn-select-all').on('click', function(e) {
            e.preventDefault();
            $('.permission-checkbox').prop('checked', true);
            updateModuleCheckboxes();
        });
        
        // Deselect all button
        $('#btn-deselect-all').on('click', function(e) {
            e.preventDefault();
            $('.permission-checkbox').prop('checked', false);
            updateModuleCheckboxes();
        });
        
        // Module checkbox - select/deselect all in module
        $('.module-checkbox').on('change', function() {
            var module = $(this).data('module');
            var checked = $(this).prop('checked');
            
            $('.permission-checkbox').filter(function() {
                // Using .attr('data-module') is safer if the data attribute is dynamically modified,
                // but .data('module') works perfectly for reading the initial HTML data-attribute.
                return $(this).attr('data-module') === String(module);
            }).prop('checked', checked);
        });
        
        // Update module checkbox state when individual permissions change
        $('.permission-checkbox').on('change', function() {
            updateModuleCheckboxes();
        });
        
        function updateModuleCheckboxes() {
            $('.module-checkbox').each(function() {
                var moduleCheckbox = $(this);
                var module = moduleCheckbox.attr('data-module');
                
                var allCheckboxes = $('.permission-checkbox').filter(function() {
                    return $(this).attr('data-module') === String(module);
                });
                
                var checkedCount = allCheckboxes.filter(':checked').length;
                
                if (allCheckboxes.length > 0) {
                    moduleCheckbox.prop('checked', checkedCount === allCheckboxes.length);
                    moduleCheckbox.prop('indeterminate', checkedCount > 0 && checkedCount < allCheckboxes.length);
                }
            });
        }
        
        // Initialize module checkboxes
        updateModuleCheckboxes();
        
        // Form submit
        $('#permissions-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var btn = $('#btn-save');
            var originalBtnHtml = btn.html();
            
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
            
            $.ajax({
                url: appUrl + 'admin/roles/save-permissions',
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(data) {
                    btn.prop('disabled', false);
                    btn.html(originalBtnHtml);
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    btn.html(originalBtnHtml);
                    Swal.fire('Error!', 'Terjadi kesalahan koneksi', 'error');
                }
            });
        });
    });
    </script>
    @endpush
</x-metronic-layout>

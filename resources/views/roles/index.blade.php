<x-metronic-layout>
    @php
        $title = 'Kelola Role';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Kelola Role
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Role & Akses</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-4"></i>
                    Tambah Role
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            @if (session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!--begin::Card-->
            <div class="card">
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="roles_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-150px">Role</th>
                                <th class="min-w-250px">Deskripsi</th>
                                <th class="min-w-100px">User</th>
                                <th class="min-w-100px">Status</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach ($roles as $role)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold">{{ $role->name }}</span>
                                        <span class="text-muted fs-7">{{ $role->slug }}</span>
                                    </div>
                                </td>
                                <td>{{ $role->description ?: '-' }}</td>
                                <td>
                                    <span class="badge badge-light-primary fw-bold">{{ $role->users_count }} User</span>
                                </td>
                                <td>
                                    @if ($role->is_active)
                                    <span class="badge badge-light-success">Aktif</span>
                                    @else
                                    <span class="badge badge-light-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" 
                                       data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Aksi
                                        <i class="ki-outline ki-down fs-5 ms-1"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" 
                                         data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="{{ route('admin.roles.permissions', $role->id) }}" class="menu-link px-3 text-primary">
                                                Set Permissions
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="menu-link px-3">
                                                Edit Role
                                            </a>
                                        </div>
                                        @if (!$role->is_system)
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3 btn-delete text-danger" data-id="{{ $role->id }}">
                                                Hapus
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!--end::Card-->

        </div>
    </div>
    <!--end::Content-->

    @push('js')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete role
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                
                Swal.fire({
                    title: 'Hapus Role?',
                    text: 'Role yang dihapus tidak dapat dikembalikan',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ url('roles/delete') }}/" + id, { 
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        });
                    }
                });
            });
        });
    });
    </script>
    @endpush
</x-metronic-layout>

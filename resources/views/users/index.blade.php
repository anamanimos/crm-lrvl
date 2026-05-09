<x-metronic-layout>
    @php
        $title = 'Kelola Pengguna';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Kelola Pengguna
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Pengguna</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-4"></i>
                    Tambah Pengguna
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
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="users_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-200px">Pengguna</th>
                                <th class="min-w-100px">Role</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-100px">Last Login</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach ($users as $user)
                            <tr>
                                <td class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-45px me-3">
                                        <div class="symbol-label fs-6 fw-semibold bg-light-primary text-primary">
                                            {{ generate_initials($user->name) }}
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold">{{ $user->name }}</span>
                                        <span class="text-muted fs-7">@ {{ $user->username }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $role_badges = [
                                            'superadmin' => 'badge-light-danger',
                                            'admin' => 'badge-light-primary',
                                            'cs' => 'badge-light-success',
                                            'sales' => 'badge-light-info'
                                        ];
                                        $badge = $role_badges[$user->role] ?? 'badge-light';
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>
                                    @if ($user->is_active)
                                    <span class="badge badge-light-success">Aktif</span>
                                    @else
                                    <span class="badge badge-light-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $user->last_login ? time_ago($user->last_login) : '-' }}
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" 
                                       data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Aksi
                                        <i class="ki-outline ki-down fs-5 ms-1"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" 
                                         data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="menu-link px-3">
                                                Edit
                                            </a>
                                        </div>
                                        @if ($user->id != auth()->id())
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3 btn-delete" data-id="{{ $user->id }}">
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
        // Delete user
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                
                Swal.fire({
                    title: 'Hapus Pengguna?',
                    text: 'Data pengguna akan dihapus permanen',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ url('admin/users/delete') }}/" + id, { 
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

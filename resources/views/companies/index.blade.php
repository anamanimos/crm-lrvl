<x-metronic-layout>
    @php
        $title = 'Manajemen Perusahaan';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Manajemen Perusahaan
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Perusahaan</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.companies.create') }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-4"></i>
                    Tambah Perusahaan
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

            <div class="row g-5 g-xl-10">
                <!--begin::Col Stats-->
                <div class="col-xl-3">
                    <div class="card card-flush mb-5 mb-xl-8">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Statistik</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">Ringkasan data perusahaan</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <!--Stat Item-->
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-35px me-3">
                                        <div class="symbol-label bg-light-primary">
                                            <i class="ki-outline ki-abstract-26 fs-4 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="fs-6 fw-bold text-gray-800">Total Perusahaan</div>
                                </div>
                                <div class="badge badge-light-primary fs-7 fw-bold">{{ $stats['total'] }}</div>
                            </div>
                            
                            <!--Stat Item-->
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-35px me-3">
                                        <div class="symbol-label bg-light-success">
                                            <i class="ki-outline ki-sms fs-4 text-success"></i>
                                        </div>
                                    </div>
                                    <div class="fs-6 fw-bold text-gray-800">Dengan Email</div>
                                </div>
                                <div class="badge badge-light-success fs-7 fw-bold">{{ $stats['with_email'] }}</div>
                            </div>

                            <!--Stat Item-->
                            <div class="d-flex flex-stack">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-35px me-3">
                                        <div class="symbol-label bg-light-warning">
                                            <i class="ki-outline ki-phone fs-4 text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="fs-6 fw-bold text-gray-800">Dengan Telepon</div>
                                </div>
                                <div class="badge badge-light-warning fs-7 fw-bold">{{ $stats['with_phone'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col Stats-->

                <!--begin::Col Table-->
                <div class="col-xl-9">
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <!--begin::Card body-->
                        <div class="card-body py-4">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_companies">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-125px">Nama Perusahaan</th>
                                        <th class="min-w-125px">Email/Phone</th>
                                        <th class="min-w-125px">Alamat</th>
                                        <th class="min-w-100px text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    @forelse ($companies as $row)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('admin.companies.edit', $row->id) }}" class="text-gray-800 text-hover-primary mb-1">
                                                    {{ $row->name }}
                                                </a>
                                                <span class="fs-7 text-muted">{{ $row->website ?: '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $row->email ?: '-' }}</span>
                                                <span>{{ $row->phone ?: '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            {{ Str::limit($row->address, 50) }}
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                Aksi
                                                <i class="ki-outline ki-down fs-5 ms-1"></i>
                                            </a>
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('admin.companies.edit', $row->id) }}" class="menu-link px-3">Edit</a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <form action="{{ route('admin.companies.delete', $row->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <a href="#" class="menu-link px-3 text-danger btn-delete-company">Hapus</a>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-10 text-muted">Belum ada data perusahaan</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Col Table-->
            </div>
        </div>
    </div>
</x-metronic-layout>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete-company').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form');
                
                Swal.fire({
                    title: 'Hapus Perusahaan?',
                    text: 'Customer yang terkait akan kehilangan referensi perusahaan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush

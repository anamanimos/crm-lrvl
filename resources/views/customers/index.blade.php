<x-metronic-layout>
    @php
        $title = 'Data Customer';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Data Customer
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Customer</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ url('admin/customers/import') }}" class="btn btn-sm fw-bold btn-secondary">
                    <i class="ki-outline ki-cloud-download fs-4"></i>
                    Import
                </a>
                <a href="{{ url('admin/customers/create') }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-4"></i>
                    Tambah Customer
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

            <div class="row g-5">
                <!--begin::Col Left: Statistics-->
                <div class="col-xl-3">
                    <!--begin::Stats Card-->
                    <div class="card card-flush mb-5">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Ringkasan</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-7">Statistik customer saat ini</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-outline ki-people fs-2 text-primary"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Aktif</span>
                                        <span class="text-gray-400 fw-semibold fs-7">Daftar customer aktif</span>
                                    </div>
                                </div>
                                <span class="text-gray-800 fw-bold fs-4">{{ number_format($stats['active']) }}</span>
                            </div>

                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-outline ki-archive fs-2 text-warning"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Arsip</span>
                                        <span class="text-gray-400 fw-semibold fs-7">Customer yang diarsipkan</span>
                                    </div>
                                </div>
                                <span class="text-gray-800 fw-bold fs-4">{{ number_format($stats['archived']) }}</span>
                            </div>

                            <div class="separator separator-dashed my-5"></div>

                            <div class="d-flex flex-stack">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-outline ki-check-circle fs-2 text-success"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Total</span>
                                        <span class="text-gray-400 fw-semibold fs-7">Semua data customer</span>
                                    </div>
                                </div>
                                <span class="text-gray-800 fw-bold fs-4">{{ number_format($stats['total']) }}</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Stats Card-->

                    <!--begin::Labels Card-->
                    <div class="card card-flush">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Distribusi Label</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            @foreach($stats['labels'] as $label)
                            <div class="d-flex flex-stack mb-4">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-15px me-3">
                                        <span class="symbol-label" style="background-color: {{ $label->color }}"></span>
                                    </div>
                                    <span class="text-gray-800 fw-semibold fs-7">{{ $label->name }}</span>
                                </div>
                                <span class="badge badge-light fw-bold fs-8">{{ $label->customers_count }}</span>
                            </div>
                            @endforeach
                            @if($stats['labels']->isEmpty())
                                <div class="text-muted fs-7 italic text-center py-5">Belum ada label</div>
                            @endif
                        </div>
                    </div>
                    <!--end::Labels Card-->
                </div>
                <!--end::Col Left-->

                <!--begin::Col Right: Customer Table-->
                <div class="col-xl-9">
                    <!--begin::Card-->
                    <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" name="search" 
                                   class="form-control form-control-solid w-250px ps-13" 
                                   placeholder="Cari customer..." 
                                   value="{{ request('search') }}" />
                        </form>
                    </div>
                    <!--end::Card title-->
                    
                    <!--begin::Card toolbar-->
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end gap-3">
                            <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex gap-3">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                
                                <select name="per_page" class="form-select form-select-solid form-select-sm w-75px" onchange="this.form.submit()">
                                    @foreach ([10, 20, 50, 100] as $limit)
                                    <option value="{{ $limit }}" {{ (request('per_page', 20) == $limit) ? 'selected' : '' }}>
                                        {{ $limit }}
                                    </option>
                                    @endforeach
                                </select>

                                <select name="label" class="form-select form-select-solid w-150px" onchange="this.form.submit()">
                                    <option value="">Semua Label</option>
                                    @foreach ($labels as $label)
                                    <option value="{{ $label->id }}" {{ (request('label') == $label->id) ? 'selected' : '' }}>
                                        {{ $label->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <!--end::Card toolbar-->
                </div>
                <!--end::Card header-->
                
                <!--begin::Card body-->
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px">Customer</th>
                                    <th class="min-w-100px">Perusahaan</th>
                                    <th class="min-w-125px">Nomor WA</th>
                                    <th class="min-w-100px">Label</th>
                                    <th class="min-w-100px">Terakhir Chat</th>
                                    <th class="text-end min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse ($customers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-circle symbol-40px me-3">
                                                <div class="symbol-label fs-5 fw-semibold bg-light-primary text-primary">
                                                    {{ generate_initials($customer->name ?: $customer->wa_number) }}
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('admin.customers.show', $customer->id) }}" 
                                                   class="text-gray-800 text-hover-primary fw-bold">
                                                    {{ $customer->name ?: 'Tanpa Nama' }}
                                                </a>
                                                @if ($customer->assignedUser)
                                                <span class="text-muted fs-7">
                                                    <i class="ki-outline ki-user fs-7"></i> {{ $customer->assignedUser->name }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($customer->company)
                                        <span class="text-gray-800 fw-bold">{{ $customer->company->name }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="https://wa.me/{{ $customer->wa_number }}" target="_blank" 
                                           class="text-gray-600 text-hover-primary">
                                            {{ format_phone_display($customer->wa_number) }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($customer->labels->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($customer->labels as $label)
                                            <span class="badge" style="background-color: {{ $label->color }}20; color: {{ $label->color }}">
                                                {{ $label->name }}
                                            </span>
                                            @endforeach
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ time_ago($customer->last_chat_at) }}
                                    </td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" 
                                           data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            Aksi
                                            <i class="ki-outline ki-down fs-5 ms-1"></i>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" 
                                             data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                <a href="{{ url('chat?customer=' . $customer->id) }}" class="menu-link px-3">
                                                    <i class="ki-outline ki-message-text-2 fs-5 me-2"></i> Chat
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="menu-link px-3">
                                                    <i class="ki-outline ki-eye fs-5 me-2"></i> Detail
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="menu-link px-3">
                                                    <i class="ki-outline ki-pencil fs-5 me-2"></i> Edit
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3 btn-archive" 
                                                   data-id="{{ $customer->id }}" data-archived="{{ $customer->is_archived }}">
                                                    <i class="ki-outline ki-archive fs-5 me-2"></i> {{ $customer->is_archived ? 'Pulihkan' : 'Arsip' }}
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3 text-danger btn-delete" 
                                                   data-id="{{ $customer->id }}" data-name="{{ $customer->name ?: $customer->wa_number }}">
                                                    <i class="ki-outline ki-trash fs-5 me-2"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-muted">
                                        Belum ada data customer
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Card body-->
                
                <!--begin::Card footer-->
                <div class="card-footer d-flex justify-content-between align-items-center pt-4">
                    <div class="text-gray-600 fs-7">
                        Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
                    </div>
                    <div>
                        {{ $customers->links() }}
                    </div>
                </div>
                <!--end::Card footer-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Col Right-->
            </div>
            <!--end::Row-->

        </div>
    </div>
    <!--end::Content-->

    <form id="action-form" method="POST" style="display:none;">
        @csrf
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Delete
            document.querySelectorAll('.btn-delete').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    
                    Swal.fire({
                        title: 'Hapus Customer?',
                        text: "Apakah Anda yakin ingin menghapus " + name + "? Tindakan ini tidak dapat dibatalkan.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var form = document.getElementById('action-form');
                            form.action = "{{ url('admin/customers') }}/" + id + "/delete";
                            form.submit();
                        }
                    });
                });
            });

            // Handle Archive
            document.querySelectorAll('.btn-archive').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var id = this.getAttribute('data-id');
                    var isArchived = this.getAttribute('data-archived') == '1';
                    var title = isArchived ? 'Pulihkan Customer?' : 'Arsipkan Customer?';
                    var text = isArchived ? 'Customer akan dikembalikan ke daftar aktif.' : 'Customer akan dipindahkan ke daftar arsip.';
                    
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var form = document.getElementById('action-form');
                            form.action = "{{ url('admin/customers') }}/" + id + "/archive";
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    @endpush
</x-metronic-layout>

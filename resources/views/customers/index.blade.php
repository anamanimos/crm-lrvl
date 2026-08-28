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
                                <span class="text-gray-400 mt-1 fw-semibold fs-7">Klik untuk filter cepat</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-stack mb-5 cursor-pointer p-2 rounded hover-elevate-up filter-quick-status" data-status="0" title="Filter Customer Aktif">
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

                            <div class="d-flex flex-stack mb-5 cursor-pointer p-2 rounded hover-elevate-up filter-quick-status" data-status="1" title="Filter Customer Arsip">
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

                            <div class="d-flex flex-stack p-2">
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
                                <span class="text-gray-400 mt-1 fw-semibold fs-7">Klik label untuk filter</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            @foreach($stats['labels'] as $label)
                            <div class="d-flex flex-stack mb-3 p-2 rounded cursor-pointer hover-elevate-up filter-quick-label" data-label-id="{{ $label->id }}" title="Filter Label {{ $label->name }}">
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
                            <!--begin::Card title (Search)-->
                            <div class="card-title">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" id="filter-search" 
                                           class="form-control form-control-solid w-250px ps-12 pe-10" 
                                           placeholder="Cari nama, WA, email..." 
                                           value="{{ request('search') }}" />
                                    <span id="search-spinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 me-3 d-none"></span>
                                </div>
                            </div>
                            <!--end::Card title-->
                            
                            <!--begin::Card toolbar (Filters)-->
                            <div class="card-toolbar">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <!-- Label Filter -->
                                    <select id="filter-label" class="form-select form-select-solid form-select-sm w-140px" title="Filter Label">
                                        <option value="">Semua Label</option>
                                        @foreach ($labels as $label)
                                        <option value="{{ $label->id }}" {{ request('label') == $label->id ? 'selected' : '' }}>
                                            {{ $label->name }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <!-- Source Filter -->
                                    <select id="filter-source" class="form-select form-select-solid form-select-sm w-130px" title="Filter Sumber">
                                        <option value="">Semua Sumber</option>
                                        @foreach ($sources as $source)
                                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                            {{ $source }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <!-- Status Filter -->
                                    <select id="filter-archive" class="form-select form-select-solid form-select-sm w-110px" title="Status Customer">
                                        <option value="0" {{ request('archive', '0') === '0' ? 'selected' : '' }}>Aktif</option>
                                        <option value="1" {{ request('archive') === '1' ? 'selected' : '' }}>Arsip</option>
                                    </select>

                                    <!-- Limit Per Page -->
                                    <select id="filter-per-page" class="form-select form-select-solid form-select-sm w-70px" title="Data per Halaman">
                                        @foreach ([10, 20, 50, 100] as $limit)
                                        <option value="{{ $limit }}" {{ request('per_page', 20) == $limit ? 'selected' : '' }}>
                                            {{ $limit }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <!-- Reset Filters Button -->
                                    <button type="button" id="btn-reset-filters" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="Reset Semua Filter">
                                        <i class="ki-outline ki-arrows-circle fs-4"></i>
                                    </button>
                                </div>
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->
                        
                        <!--begin::Card body (Table)-->
                        <div class="card-body py-4" id="customer-table-container">
                            @include('customers._table')
                        </div>
                        <!--end::Card body-->
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
            let searchTimeout = null;
            let currentRequest = null;

            const searchInput = document.getElementById('filter-search');
            const labelSelect = document.getElementById('filter-label');
            const sourceSelect = document.getElementById('filter-source');
            const archiveSelect = document.getElementById('filter-archive');
            const perPageSelect = document.getElementById('filter-per-page');
            const resetBtn = document.getElementById('btn-reset-filters');
            const searchSpinner = document.getElementById('search-spinner');
            const tableContainer = document.getElementById('customer-table-container');

            function getFilterParams() {
                const params = new URLSearchParams();
                if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
                if (labelSelect.value) params.set('label', labelSelect.value);
                if (sourceSelect.value) params.set('source', sourceSelect.value);
                if (archiveSelect.value !== '') params.set('archive', archiveSelect.value);
                if (perPageSelect.value) params.set('per_page', perPageSelect.value);
                return params;
            }

            function loadCustomers(url = null) {
                let targetUrl = url;
                if (!targetUrl) {
                    const params = getFilterParams();
                    targetUrl = "{{ route('admin.customers.index') }}" + (params.toString() ? '?' + params.toString() : '');
                }

                // Show spinner & loading overlay
                if (searchSpinner) searchSpinner.classList.remove('d-none');
                const overlay = document.getElementById('table-loading-overlay');
                if (overlay) {
                    overlay.classList.remove('d-none');
                    overlay.classList.add('d-flex');
                }

                // Abort previous pending request if any
                if (currentRequest && currentRequest.readyState !== 4) {
                    currentRequest.abort();
                }

                currentRequest = $.ajax({
                    url: targetUrl,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.html) {
                            tableContainer.innerHTML = response.html;
                        }

                        // Reinitialize Metronic Menus
                        if (typeof KTMenu !== 'undefined') {
                            KTMenu.createInstances();
                        }

                        // Update Browser History URL
                        if (window.history && window.history.pushState) {
                            window.history.pushState(null, '', targetUrl);
                        }
                    },
                    error: function(xhr, status, error) {
                        if (status !== 'abort') {
                            console.error('AJAX Load Error:', error);
                        }
                    },
                    complete: function() {
                        if (searchSpinner) searchSpinner.classList.add('d-none');
                        const overlay = document.getElementById('table-loading-overlay');
                        if (overlay) {
                            overlay.classList.remove('d-flex');
                            overlay.classList.add('d-none');
                        }
                    }
                });
            }

            // Search input live typing (debounced)
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadCustomers();
                }, 350);
            });

            // Filter dropdowns change
            labelSelect.addEventListener('change', function() { loadCustomers(); });
            sourceSelect.addEventListener('change', function() { loadCustomers(); });
            archiveSelect.addEventListener('change', function() { loadCustomers(); });
            perPageSelect.addEventListener('change', function() { loadCustomers(); });

            // Reset filters
            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                labelSelect.value = '';
                sourceSelect.value = '';
                archiveSelect.value = '0';
                perPageSelect.value = '20';
                loadCustomers();
            });

            // Quick Filters from Left Sidebar
            document.querySelectorAll('.filter-quick-status').forEach(function(el) {
                el.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    archiveSelect.value = status;
                    loadCustomers();
                });
            });

            document.querySelectorAll('.filter-quick-label').forEach(function(el) {
                el.addEventListener('click', function() {
                    const labelId = this.getAttribute('data-label-id');
                    labelSelect.value = labelId;
                    loadCustomers();
                });
            });

            // AJAX Pagination click
            $(document).on('click', '.ajax-pagination a, #customer-table-container .pagination a', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                if (href && href !== '#') {
                    loadCustomers(href);
                    // Smooth scroll back to table top
                    document.getElementById('customer-table-container').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });

            // Delegated SweetAlert2 Delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                
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
                        const form = document.getElementById('action-form');
                        form.action = "{{ url('admin/customers') }}/" + id + "/delete";
                        form.submit();
                    }
                });
            });

            // Delegated SweetAlert2 Archive
            $(document).on('click', '.btn-archive', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const isArchived = $(this).data('archived') == '1';
                const title = isArchived ? 'Pulihkan Customer?' : 'Arsipkan Customer?';
                const text = isArchived ? 'Customer akan dikembalikan ke daftar aktif.' : 'Customer akan dipindahkan ke daftar arsip.';
                
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('action-form');
                        form.action = "{{ url('admin/customers') }}/" + id + "/archive";
                        form.submit();
                    }
                });
            });
        });
    </script>
    @endpush
</x-metronic-layout>

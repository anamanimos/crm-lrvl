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
                            <div class="d-flex flex-stack mb-4 cursor-pointer p-2 rounded hover-elevate-up filter-quick-status" data-status="0" title="Klik untuk filter customer aktif">
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

                            <div class="d-flex flex-stack mb-4 cursor-pointer p-2 rounded hover-elevate-up filter-quick-status" data-status="1" title="Klik untuk filter customer arsip">
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

                            <div class="separator separator-dashed my-4"></div>

                            <div class="d-flex flex-stack p-2 cursor-pointer rounded hover-elevate-up filter-quick-status" data-status="all" title="Klik untuk tampilkan semua">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-40px me-3">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-outline ki-check-circle fs-2 text-success"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Total</span>
                                        <span class="text-gray-400 fw-semibold fs-7">Semua customer</span>
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
                                           class="form-control form-control-solid w-250px w-md-300px ps-12 pe-10" 
                                           placeholder="Cari nama, WA, email..." 
                                           value="{{ request('search') }}" />
                                    <span id="search-spinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 me-3 d-none"></span>
                                </div>
                            </div>
                            <!--end::Card title-->
                            
                            <!--begin::Card toolbar (Filter Button & Reset)-->
                            <div class="card-toolbar">
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Filter Button (Opens Modal) -->
                                    <button type="button" class="btn btn-sm btn-light-primary fw-bold d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#kt_modal_customer_filter">
                                        <i class="ki-outline ki-filter fs-4 me-1"></i>
                                        <span>Filter</span>
                                        <span id="active-filter-badge" class="badge badge-circle badge-primary ms-2 d-none" style="width: 18px; height: 18px; font-size: 10px;">0</span>
                                    </button>

                                    <!-- Quick Reset Button -->
                                    <button type="button" id="btn-quick-reset" class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="Reset Semua Filter">
                                        <i class="ki-outline ki-arrows-circle fs-4"></i>
                                    </button>
                                </div>
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Active Filter Pills-->
                        <div class="px-7 pt-2 pb-0" id="active-filters-container" style="display: none;">
                            <div class="d-flex flex-wrap align-items-center gap-2" id="active-filters-pills">
                                <!-- Dynamic pills rendered via JS -->
                            </div>
                        </div>
                        <!--end::Active Filter Pills-->
                        
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

    <!--begin::Modal - Filter Customer-->
    <div class="modal fade" id="kt_modal_customer_filter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header pb-0 border-0 justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-40px bg-light-primary me-3">
                            <span class="symbol-label">
                                <i class="ki-outline ki-filter fs-2 text-primary"></i>
                            </span>
                        </div>
                        <div>
                            <h2 class="fw-bold fs-4 m-0 text-gray-900">Filter Data Customer</h2>
                            <span class="text-muted fs-7">Saring data customer sesuai kebutuhan</span>
                        </div>
                    </div>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body py-6 px-lg-8">
                    <!--begin::Form-->
                    <form id="filter-modal-form">
                        <!-- Label Filter -->
                        <div class="mb-5">
                            <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Label Customer:</label>
                            <select id="modal-filter-label" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                <option value="">Semua Label</option>
                                @foreach ($labels as $label)
                                <option value="{{ $label->id }}" {{ request('label') == $label->id ? 'selected' : '' }}>
                                    {{ $label->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Source Filter -->
                        <div class="mb-5">
                            <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Sumber (*Source*):</label>
                            <select id="modal-filter-source" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                <option value="">Semua Sumber</option>
                                @foreach ($sources as $source)
                                <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                    {{ $source }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-5">
                            <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Status Customer:</label>
                            <select id="modal-filter-archive" class="form-select form-select-solid">
                                <option value="0" {{ request('archive', '0') === '0' ? 'selected' : '' }}>Customer Aktif</option>
                                <option value="1" {{ request('archive') === '1' ? 'selected' : '' }}>Customer Diarsipkan</option>
                                <option value="all" {{ request('archive') === 'all' ? 'selected' : '' }}>Semua Status</option>
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-5">
                            <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Urutkan Berdasarkan:</label>
                            <select id="modal-filter-sort" class="form-select form-select-solid">
                                <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama Ditambahkan</option>
                                <option value="last_chat" {{ request('sort') == 'last_chat' ? 'selected' : '' }}>Terakhir Chat</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama (Z - A)</option>
                            </select>
                        </div>

                        <!-- Data per Page -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold fs-7 text-gray-700 mb-2">Jumlah per Halaman:</label>
                            <select id="modal-filter-per-page" class="form-select form-select-solid">
                                @foreach ([10, 20, 50, 100] as $limit)
                                <option value="{{ $limit }}" {{ request('per_page', 20) == $limit ? 'selected' : '' }}>
                                    {{ $limit }} entri per halaman
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Modal body-->

                <!--begin::Modal footer-->
                <div class="modal-footer flex-center border-0 pt-0 pb-6">
                    <button type="button" id="btn-modal-reset" class="btn btn-light me-3">
                        <i class="ki-outline ki-arrows-circle fs-4 me-1"></i> Reset
                    </button>
                    <button type="button" id="btn-modal-apply" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-4 me-1"></i> Terapkan Filter
                    </button>
                </div>
                <!--end::Modal footer-->
            </div>
        </div>
    </div>
    <!--end::Modal - Filter Customer-->

    <form id="action-form" method="POST" style="display:none;">
        @csrf
    </form>

    @push('js')
    <script>
        (function() {
            let searchTimeout = null;
            let currentRequest = null;

            // Elements
            const searchInput = document.getElementById('filter-search');
            const searchSpinner = document.getElementById('search-spinner');
            const modalLabel = document.getElementById('modal-filter-label');
            const modalSource = document.getElementById('modal-filter-source');
            const modalArchive = document.getElementById('modal-filter-archive');
            const modalSort = document.getElementById('modal-filter-sort');
            const modalPerPage = document.getElementById('modal-filter-per-page');
            const btnModalApply = document.getElementById('btn-modal-apply');
            const btnModalReset = document.getElementById('btn-modal-reset');
            const btnQuickReset = document.getElementById('btn-quick-reset');
            const activeFilterBadge = document.getElementById('active-filter-badge');
            const activeFiltersContainer = document.getElementById('active-filters-container');
            const activeFiltersPills = document.getElementById('active-filters-pills');
            const tableContainer = document.getElementById('customer-table-container');

            function getFilterParams() {
                const params = new URLSearchParams();
                
                const searchVal = searchInput ? searchInput.value.trim() : '';
                if (searchVal) params.set('search', searchVal);

                if (modalLabel && modalLabel.value) params.set('label', modalLabel.value);
                if (modalSource && modalSource.value) params.set('source', modalSource.value);
                if (modalArchive && modalArchive.value !== '' && modalArchive.value !== '0') params.set('archive', modalArchive.value);
                if (modalSort && modalSort.value && modalSort.value !== 'latest') params.set('sort', modalSort.value);
                if (modalPerPage && modalPerPage.value && modalPerPage.value !== '20') params.set('per_page', modalPerPage.value);

                return params;
            }

            function updateFilterBadges() {
                let count = 0;
                let pillsHtml = '';

                if (modalLabel && modalLabel.value) {
                    count++;
                    const labelText = modalLabel.options[modalLabel.selectedIndex].text;
                    pillsHtml += `<span class="badge badge-light-primary fw-semibold fs-8 py-2 px-3">
                        Label: ${labelText}
                        <i class="ki-outline ki-cross fs-7 ms-1 cursor-pointer remove-filter" data-filter="label"></i>
                    </span>`;
                }

                if (modalSource && modalSource.value) {
                    count++;
                    const sourceText = modalSource.options[modalSource.selectedIndex].text;
                    pillsHtml += `<span class="badge badge-light-info fw-semibold fs-8 py-2 px-3">
                        Sumber: ${sourceText}
                        <i class="ki-outline ki-cross fs-7 ms-1 cursor-pointer remove-filter" data-filter="source"></i>
                    </span>`;
                }

                if (modalArchive && modalArchive.value && modalArchive.value !== '0') {
                    count++;
                    const statusText = modalArchive.value === '1' ? 'Diarsipkan' : 'Semua Status';
                    pillsHtml += `<span class="badge badge-light-warning fw-semibold fs-8 py-2 px-3">
                        Status: ${statusText}
                        <i class="ki-outline ki-cross fs-7 ms-1 cursor-pointer remove-filter" data-filter="archive"></i>
                    </span>`;
                }

                if (modalSort && modalSort.value && modalSort.value !== 'latest') {
                    count++;
                    const sortText = modalSort.options[modalSort.selectedIndex].text;
                    pillsHtml += `<span class="badge badge-light-secondary fw-semibold fs-8 py-2 px-3 text-gray-700">
                        Urut: ${sortText}
                        <i class="ki-outline ki-cross fs-7 ms-1 cursor-pointer remove-filter" data-filter="sort"></i>
                    </span>`;
                }

                if (activeFilterBadge) {
                    if (count > 0) {
                        activeFilterBadge.textContent = count;
                        activeFilterBadge.classList.remove('d-none');
                    } else {
                        activeFilterBadge.classList.add('d-none');
                    }
                }

                if (activeFiltersContainer && activeFiltersPills) {
                    if (count > 0) {
                        activeFiltersPills.innerHTML = pillsHtml;
                        activeFiltersContainer.style.display = 'block';
                    } else {
                        activeFiltersPills.innerHTML = '';
                        activeFiltersContainer.style.display = 'none';
                    }
                }
            }

            function loadCustomers(url = null) {
                let targetUrl = url;
                if (!targetUrl) {
                    const params = getFilterParams();
                    targetUrl = "{{ url('customers') }}" + (params.toString() ? '?' + params.toString() : '');
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

                        updateFilterBadges();
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
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        loadCustomers();
                    }, 300);
                });
            }

            // Apply Filters from Modal
            if (btnModalApply) {
                btnModalApply.addEventListener('click', function() {
                    const modalEl = document.getElementById('kt_modal_customer_filter');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    loadCustomers();
                });
            }

            // Reset Filters inside Modal
            if (btnModalReset) {
                btnModalReset.addEventListener('click', function() {
                    if (modalLabel) modalLabel.value = '';
                    if (modalSource) modalSource.value = '';
                    if (modalArchive) modalArchive.value = '0';
                    if (modalSort) modalSort.value = 'latest';
                    if (modalPerPage) modalPerPage.value = '20';
                    
                    const modalEl = document.getElementById('kt_modal_customer_filter');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    loadCustomers();
                });
            }

            // Quick Reset Button on toolbar
            if (btnQuickReset) {
                btnQuickReset.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (modalLabel) modalLabel.value = '';
                    if (modalSource) modalSource.value = '';
                    if (modalArchive) modalArchive.value = '0';
                    if (modalSort) modalSort.value = 'latest';
                    if (modalPerPage) modalPerPage.value = '20';
                    loadCustomers();
                });
            }

            // Remove single filter pill
            $(document).on('click', '.remove-filter', function() {
                const filterType = $(this).data('filter');
                if (filterType === 'label' && modalLabel) modalLabel.value = '';
                if (filterType === 'source' && modalSource) modalSource.value = '';
                if (filterType === 'archive' && modalArchive) modalArchive.value = '0';
                if (filterType === 'sort' && modalSort) modalSort.value = 'latest';
                loadCustomers();
            });

            // Quick Filters from Left Sidebar
            document.querySelectorAll('.filter-quick-status').forEach(function(el) {
                el.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    if (modalArchive) {
                        modalArchive.value = status;
                    }
                    loadCustomers();
                });
            });

            document.querySelectorAll('.filter-quick-label').forEach(function(el) {
                el.addEventListener('click', function() {
                    const labelId = this.getAttribute('data-label-id');
                    if (modalLabel) {
                        modalLabel.value = labelId;
                    }
                    loadCustomers();
                });
            });

            // AJAX Pagination click
            $(document).on('click', '.ajax-pagination a, #customer-table-container .pagination a', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                if (href && href !== '#') {
                    loadCustomers(href);
                    const container = document.getElementById('customer-table-container');
                    if (container) {
                        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
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

            // Initialize badges on page load
            updateFilterBadges();
        })();
    </script>
    @endpush
</x-metronic-layout>

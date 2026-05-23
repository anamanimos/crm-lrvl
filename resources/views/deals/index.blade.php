<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Deals Pipeline
                    </h1>
                    <p class="text-muted fs-7 fw-semibold mt-1">Kelola semua peluang bisnis Anda dalam satu tampilan</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-light-primary fw-bold">
                        <i class="ki-outline ki-time fs-2"></i> Riwayat
                    </a>
                    <a href="{{ route('deals.stages.index') }}" class="btn btn-sm btn-icon btn-light-info fw-bold" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Pengaturan Tahapan">
                        <i class="ki-outline ki-setting-2 fs-2"></i>
                    </a>
                    <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_deal">
                        <i class="ki-outline ki-plus fs-2"></i> Deal Baru
                    </button>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <!-- Filters -->
                <div class="card card-flush mb-8">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                            <!-- Left Group: Filter Button & Refresh -->
                            <div class="d-flex align-items-center gap-3">
                                <!-- Filter Dropdown -->
                                <div class="dropdown">
                                    <button type="button" class="btn btn-light-primary btn-sm btn-flex fw-bold" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        <i class="ki-outline ki-filter fs-4 me-2"></i> Filter
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-start w-300px w-md-350px p-5">
                                        <div class="px-2 py-3">
                                            <div class="mb-5">
                                                <label class="form-label fw-bold fs-7 mb-2">Pilih PIC:</label>
                                                <select id="user-filter" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Semua PIC" multiple="multiple">
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-5">
                                                <label class="form-label fw-bold fs-7 mb-2">Pilih Sumber:</label>
                                                <select id="source-filter" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="Semua Sumber" multiple="multiple">
                                                    <option value="Instagram">Instagram</option>
                                                    <option value="Facebook">Facebook</option>
                                                    <option value="WhatsApp">WhatsApp</option>
                                                    <option value="Referral">Referral</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button id="btn-refresh" class="btn btn-icon btn-light-primary btn-sm" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Muat Ulang Data">
                                    <i class="ki-outline ki-arrows-circle fs-3"></i>
                                </button>
                            </div>

                            <!-- Right Group: Search Input & Count -->
                            <div class="d-flex align-items-center gap-4">
                                <span class="text-gray-600 fw-bold fs-7" id="total-deal-count">0 Deals</span>
                                <div class="position-relative">
                                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4" style="top: 50%; transform: translateY(-50%);"></i>
                                    <input type="text" id="deal-search" class="form-control form-control-solid w-250px w-md-350px ps-12" placeholder="Cari deal atau customer..." />
                                </div>
                            </div>
                        </div>

                        <!-- Active Filters Badges -->
                        <div id="active-filters-container" class="d-flex flex-wrap align-items-center gap-2 mt-4 d-none">
                            <span class="text-muted fs-8 fw-bold me-2">Filter Aktif:</span>
                            <div id="active-filters-list" class="d-flex flex-wrap gap-2"></div>
                            <button type="button" class="btn btn-sm btn-light-danger fw-bold ms-auto" id="btn-clear-all-filters">
                                <i class="ki-outline ki-trash fs-6"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div id="kanban-container" class="d-flex overflow-x-auto pb-10 gap-5" style="min-height: 70vh;">
                    @foreach($stages as $stage)
                        <div class="kanban-column flex-shrink-0 w-325px" data-stage-id="{{ $stage->id }}">
                            <div class="d-flex flex-stack mb-5 px-2">
                                <div class="d-flex align-items-center">
                                    <span class="bullet bullet-dot w-10px h-10px me-3" style="background-color: {{ $stage->color }}"></span>
                                    <span class="fw-bold text-gray-800 fs-6">{{ $stage->name }}</span>
                                    <span class="text-muted fs-8 ms-2 stage-count">0</span>
                                </div>
                                <span class="text-muted fw-bold fs-8 stage-value">Rp 0</span>
                            </div>
                            <div class="kanban-items-list min-h-100px" data-stage-id="{{ $stage->id }}">
                                <!-- Deals loaded here -->
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
        <!--end::Content-->
    </div>

    <!-- Modal: Add Deal -->
    <div class="modal fade" id="kt_modal_add_deal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-750px">
            <div class="modal-content border-0">
                <form id="add-deal-form">
                    <div class="modal-header pb-0 border-0">
                        <h2 class="fw-bold">Deal Baru</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Judul Deal</label>
                            <input type="text" name="title" class="form-control form-control-solid" placeholder="Contoh: Bordir Kaos Kelas 12 SMA..." required />
                        </div>
                        
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Customer</label>
                                <select name="customer_id" id="customer-select" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_add_deal" data-placeholder="Cari customer..." required>
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Estimasi Nilai (Rp)</label>
                                <input type="number" name="expected_value" class="form-control form-control-solid" value="0" />
                            </div>
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Sumber</label>
                                <input type="text" name="source" class="form-control form-control-solid" placeholder="WA, Instagram, Facebook, Referral..." />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fs-6 fw-semibold mb-2">PIC / Assigned To</label>
                                <select name="assigned_user_id" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_add_deal">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Tgl Follow-up Selanjutnya</label>
                                <input type="text" name="next_followup_date" class="form-control form-control-solid" placeholder="Pilih tanggal & waktu" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Perkiraan Closing</label>
                                <input type="text" name="expected_close_date" class="form-control form-control-solid" placeholder="Pilih tanggal" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-end border-0 pt-0">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-10" id="btn-save-deal">
                            <span class="indicator-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Offcanvas: Deal Detail -->
    <div id="kt_offcanvas_deal_detail" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="deal_detail" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'100%', 'md': '500px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_offcanvas_deal_detail_toggle" data-kt-drawer-close="#kt_offcanvas_deal_detail_close">
        <div class="card shadow-none w-100 rounded-0 overflow-auto h-100">
            <div class="card-header" id="kt_offcanvas_deal_detail_header">
                <h3 class="card-title fw-bold text-gray-900">Detail Deal</h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary me-n5" id="kt_offcanvas_deal_detail_close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </button>
                </div>
            </div>
            <div class="card-body position-relative" id="kt_offcanvas_deal_detail_body">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    @push('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        window.openDealDetail = function(uuid) {
            var offcanvasElement = document.getElementById('kt_offcanvas_deal_detail');
            var drawer = KTDrawer.getInstance(offcanvasElement);
            if (!drawer) {
                drawer = new KTDrawer(offcanvasElement);
            }
            
            $('#kt_offcanvas_deal_detail_body').html('<div class="d-flex justify-content-center mt-10"><div class="spinner-border text-primary" role="status"></div></div>');
            drawer.show();

            $.get('{{ url("deals/detail") }}/' + uuid, function(res) {
                $('#kt_offcanvas_deal_detail_body').html(res);
            }).fail(function() {
                $('#kt_offcanvas_deal_detail_body').html('<div class="alert alert-danger">Gagal memuat detail deal.</div>');
            });
        };

        $(document).ready(function() {
            const boardContainer = $('#kanban-container');
            const searchInput = $('#deal-search');
            const userFilter = $('#user-filter');

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
            }

            function loadBoard() {
                const search = searchInput.val();
                const userId = userFilter.val();
                const source = $('#source-filter').val();

                $.get('{{ route("deals.board-data") }}', { 
                    search: search, 
                    user_id: userId,
                    source: source
                }, function(res) {
                    if (res.success) {
                        let totalDeals = 0;
                        res.data.forEach(item => {
                            totalDeals += item.count;
                            const column = $(`.kanban-column[data-stage-id="${item.stage.id}"]`);
                            const list = column.find('.kanban-items-list');
                            column.find('.stage-count').text(item.count);
                            column.find('.stage-value').text(formatRupiah(item.total_value));
                            
                            list.empty();
                            item.deals.forEach(deal => {
                                const dateStr = deal.created_at ? new Date(deal.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : '-';
                                list.append(`
                                    <div class="card card-flush shadow-sm mb-4 cursor-grab kanban-deal-card border-0" data-id="${deal.id}">
                                        <div class="card-body p-5">
                                            <div class="d-flex flex-column gap-2">
                                                <a href="javascript:void(0)" onclick="openDealDetail('${deal.uuid}')" class="text-gray-800 text-hover-primary fw-boldest fs-6 mb-1">${deal.title}</a>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i class="ki-outline ki-profile-circle fs-6 text-muted opacity-50"></i>
                                                    <span class="text-muted fs-7">${deal.customer ? deal.customer.name : 'Unknown'} ${deal.customer && deal.customer.wa_number ? ' - ' + deal.customer.wa_number : ''}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <span class="fw-bold text-primary fs-7">${formatRupiah(deal.expected_value)}</span>
                                                        <div class="d-flex align-items-center gap-1 text-muted fs-8">
                                                            <i class="ki-outline ki-calendar fs-8"></i>
                                                            <span>${dateStr}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="text-muted fs-9">${deal.assigned_user ? deal.assigned_user.name : '-'}</span>
                                                        <div class="symbol symbol-20px symbol-circle">
                                                            <div class="symbol-label fs-10 bg-light-primary text-primary fw-bold">${deal.assigned_user ? deal.assigned_user.name.charAt(0) : '?'}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                        });
                        $('#total-deal-count').text(`${totalDeals} Deals`);
                        initSortable();
                    }
                });
            }

            function initSortable() {
                $('.kanban-items-list').each(function() {
                    new Sortable(this, {
                        group: 'deals',
                        animation: 150,
                        ghostClass: 'bg-light-primary',
                        onEnd: function(evt) {
                            const dealId = $(evt.item).data('id');
                            const newStageId = $(evt.to).data('stage-id');
                            
                            if (evt.from !== evt.to) {
                                $.post('{{ route("deals.update-stage") }}', {
                                    _token: '{{ csrf_token() }}',
                                    deal_id: dealId,
                                    stage_id: newStageId
                                }, function(res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                        loadBoard();
                                    } else {
                                        toastr.error(res.message);
                                        loadBoard();
                                    }
                                });
                            }
                        }
                    });
                });
            }

            $('#customer-select').select2({
                tags: true,
                ajax: {
                    url: '{{ route("admin.customers.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { search: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    }
                },
                minimumInputLength: 2,
                placeholder: "Cari customer..."
            });

            loadBoard();

            $('input[name="next_followup_date"]').flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });

            $('input[name="expected_close_date"]').flatpickr({
                dateFormat: "Y-m-d",
            });

            let searchTimer;
            searchInput.on('keyup', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadBoard, 500);
            });

            // Automatic Filter on Change
            userFilter.on('change', function() {
                renderFilterBadges();
                loadBoard();
            });

            $('#source-filter').on('change', function() {
                renderFilterBadges();
                loadBoard();
            });

            function renderFilterBadges() {
                const list = $('#active-filters-list');
                const container = $('#active-filters-container');
                list.empty();

                const users = $('#user-filter').select2('data');
                const sources = $('#source-filter').select2('data');

                if (users.length === 0 && sources.length === 0) {
                    container.addClass('d-none');
                    return;
                }

                container.removeClass('d-none');

                users.forEach(item => {
                    list.append(`
                        <span class="badge badge-light-primary d-flex align-items-center gap-2">
                            PIC: ${item.text}
                            <i class="ki-outline ki-cross fs-9 cursor-pointer remove-filter" data-type="user" data-val="${item.id}"></i>
                        </span>
                    `);
                });

                sources.forEach(item => {
                    list.append(`
                        <span class="badge badge-light-info d-flex align-items-center gap-2">
                            Sumber: ${item.text}
                            <i class="ki-outline ki-cross fs-9 cursor-pointer remove-filter" data-type="source" data-val="${item.id}"></i>
                        </span>
                    `);
                });
            }

            // Remove single filter badge
            $(document).on('click', '.remove-filter', function() {
                const type = $(this).data('type');
                const val = $(this).data('val');
                const select = type === 'user' ? $('#user-filter') : $('#source-filter');
                
                const currentVals = select.val();
                const newVals = currentVals.filter(v => v != val);
                select.val(newVals).trigger('change');
                
                renderFilterBadges();
                loadBoard();
            });

            // Clear All
            $('#btn-clear-all-filters').on('click', function() {
                $('#user-filter').val(null).trigger('change');
                $('#source-filter').val(null).trigger('change');
                renderFilterBadges();
                loadBoard();
            });

            $('#btn-refresh').on('click', loadBoard);

            $('#add-deal-form').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btn-save-deal');
                btn.attr('disabled', true);
                
                $.post('{{ route("deals.store") }}', $(this).serialize() + '&_token={{ csrf_token() }}', function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#kt_modal_add_deal').modal('hide');
                        $('#add-deal-form')[0].reset();
                        loadBoard();
                    } else {
                        toastr.error(res.message);
                    }
                    btn.attr('disabled', false);
                });
            });
        });
    </script>
    <style>
        .kanban-items-list::-webkit-scrollbar { width: 4px; }
        .kanban-items-list::-webkit-scrollbar-thumb { background: #e1e3ea; border-radius: 10px; }
        .kanban-column { display: flex; flex-direction: column; }
        .kanban-items-list { flex-grow: 1; overflow-y: auto; max-height: 65vh; padding: 2px; }
        .cursor-grab { cursor: grab; }
        .cursor-grab:active { cursor: grabbing; }
        .kanban-deal-card:hover { transform: translateY(-2px); transition: all 0.2s ease-in-out; box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075) !important; }
        .fw-boldest { font-weight: 800 !important; }
    </style>
    @endpush
</x-metronic-layout>

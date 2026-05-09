<div class="row g-5">
    <div class="col-12">
        <!-- Filter Header (Static) -->
        <div class="card card-flush shadow-sm mb-5">
            <div class="card-body py-5">
                <div class="d-flex flex-stack flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Add Filter Dropdown -->
                        <div class="me-0">
                            <button class="btn btn-primary btn-sm d-flex align-items-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start">
                                <i class="ki-outline ki-plus fs-4 me-2"></i> Tambah Filter
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-300px w-md-350px p-7" data-kt-menu="true">
                                <div class="fs-5 text-gray-900 fw-bold mb-5">Tambah Filter</div>
                                
                                <div id="filter-container" data-action="{{ route('settings.section', ['section' => $section, 'subsection' => $subsection]) }}">
                                    <!-- Kategori Section -->
                                    <div class="mb-5">
                                        <label class="form-label fw-bold text-gray-600 text-uppercase fs-8 ls-1">Kategori</label>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @foreach($categories as $key => $label)
                                                <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array($key, (array)request('category')) ? 'btn-light-primary active' : '' }}" 
                                                        data-key="category[]" data-val="{{ $key }}">
                                                    {{ $label }}
                                                </button>
                                                @if(in_array($key, (array)request('category')))
                                                    <input type="hidden" class="filter-input" name="category[]" value="{{ $key }}">
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Event Type Section -->
                                    <div class="mb-5">
                                        <label class="form-label fw-bold text-gray-600 text-uppercase fs-8 ls-1">Event Type</label>
                                        <div class="d-flex flex-wrap gap-2 mt-2" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($event_types as $type)
                                                <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array($type, (array)request('event_type')) ? 'btn-light-primary active' : '' }}" 
                                                        data-key="event_type[]" data-val="{{ $type }}">
                                                    {{ $type }}
                                                </button>
                                                @if(in_array($type, (array)request('event_type')))
                                                    <input type="hidden" class="filter-input" name="event_type[]" value="{{ $type }}">
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Status Section -->
                                    <div class="mb-5">
                                        <label class="form-label fw-bold text-gray-600 text-uppercase fs-8 ls-1">Status</label>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array('1', (array)request('processed')) ? 'btn-light-success active' : '' }}" 
                                                    data-key="processed[]" data-val="1">
                                                Processed
                                            </button>
                                            @if(in_array('1', (array)request('processed')))
                                                <input type="hidden" class="filter-input" name="processed[]" value="1">
                                            @endif

                                            <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array('0', (array)request('processed')) ? 'btn-light-danger active' : '' }}" 
                                                    data-key="processed[]" data-val="0">
                                                Unprocessed
                                            </button>
                                            @if(in_array('0', (array)request('processed')))
                                                <input type="hidden" class="filter-input" name="processed[]" value="0">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('settings.section', ['section' => $section, 'subsection' => $subsection]) }}" class="btn btn-sm btn-light d-flex align-items-center" id="btn-reset-filters">
                            <i class="ki-outline ki-arrows-circle fs-4 me-2"></i> Reset
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="position-relative w-250px">
                            <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                            <input type="text" id="table-search" class="form-control form-control-solid ps-12 form-control-sm" placeholder="Cari..." value="{{ request('search') }}">
                        </div>
                        <button type="button" class="btn btn-sm btn-light-danger" id="btn-clear-webhook-logs">
                            <i class="ki-outline ki-trash fs-4"></i> Hapus Log
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="webhook-logs-container">
            @include('settings.sections.whatsapp.webhook_table')
        </div>
    </div>
</div>

<!-- Modal View Payload -->
<div class="modal fade" id="modal_view_payload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Webhook Payload</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <pre id="payload_content" class="bg-light p-5 rounded" style="max-height: 500px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

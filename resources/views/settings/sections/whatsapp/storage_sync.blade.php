<div class="row g-5">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card card-flush h-md-100 shadow-sm border-0 bg-light-primary">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">{{ $stats['total'] }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Media</span>
                </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-end pe-0">
                <span class="fs-6 fw-bold text-gray-800 pb-3 pe-7">Media dari pesan WA</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-flush h-md-100 shadow-sm border-0 bg-light-success">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2">{{ $stats['synced'] }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Berhasil Sinkron</span>
                </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-end pe-0">
                <div class="d-flex align-items-center mb-3">
                    <div class="progress h-6px w-100 me-2 bg-success bg-opacity-10">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['total'] > 0 ? ($stats['synced'] / $stats['total'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-gray-500 fw-bold fs-7">{{ $stats['total'] > 0 ? round($stats['synced'] / $stats['total'] * 100) : 0 }}%</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-flush h-md-100 shadow-sm border-0 bg-light-warning">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2">{{ $stats['pending'] }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Menunggu</span>
                </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-end pe-0">
                <span class="fs-6 fw-bold text-gray-800 pb-3 pe-7">Antrian sinkronisasi</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-flush h-md-100 shadow-sm border-0 bg-light-danger">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2">{{ $stats['failed'] }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Gagal</span>
                </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-end pe-0">
                <span class="fs-6 fw-bold text-gray-800 pb-3 pe-7">Perlu perhatian khusus</span>
            </div>
        </div>
    </div>

    <!-- Sync Control Card -->
    <div class="col-12">
        <div class="card card-flush shadow-sm mb-5">
            <div class="card-body py-5">
                <div class="d-flex flex-stack flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-sync-config">
                            <i class="ki-outline ki-arrows-circle fs-2 me-2"></i> Sinkronisasi Massal
                        </button>
                        
                        <button type="button" class="btn btn-light-success btn-sm d-none" id="btn-bulk-sync">
                            <i class="ki-outline ki-check-circle fs-2 me-2"></i> Sinkron Terpilih (<span id="selected-count">0</span>)
                        </button>

                        <div class="me-0">
                            <button class="btn btn-light-primary btn-sm d-flex align-items-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start">
                                <i class="ki-outline ki-filter fs-4 me-2"></i> Filter
                            </button>
                            
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-300px p-7" data-kt-menu="true">
                                <div class="fs-5 text-gray-900 fw-bold mb-5">Filter Sinkronisasi</div>
                                
                                <div id="filter-container" data-action="{{ route('settings.section', ['section' => $section, 'subsection' => $subsection]) }}">
                                    <div class="mb-5">
                                        <label class="form-label fw-bold text-gray-600 text-uppercase fs-8 ls-1">Status Sinkron</label>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array('uploaded', (array)request('media_status')) ? 'btn-light-success active' : '' }}" 
                                                    data-key="media_status[]" data-val="uploaded">Synced</button>
                                            <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array('pending', (array)request('media_status')) ? 'btn-light-warning active' : '' }}" 
                                                    data-key="media_status[]" data-val="pending">Pending</button>
                                            <button type="button" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-default filter-toggle {{ in_array('failed', (array)request('media_status')) ? 'btn-light-danger active' : '' }}" 
                                                    data-key="media_status[]" data-val="failed">Failed</button>
                                            
                                            @foreach((array)request('media_status') as $s)
                                                <input type="hidden" class="filter-input" name="media_status[]" value="{{ $s }}">
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('settings.section', ['section' => $section, 'subsection' => $subsection]) }}" class="btn btn-sm btn-light" id="btn-reset-filters">
                            <i class="ki-outline ki-arrows-circle fs-4 me-2"></i> Reset
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="position-relative w-250px">
                            <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                            <input type="text" id="table-search" class="form-control form-control-solid ps-12 form-control-sm" placeholder="Cari ID Pesan..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="webhook-logs-container">
            @include('settings.sections.whatsapp.storage_sync_table')
        </div>
    </div>
</div>

<!-- Sync Config Modal -->
<div class="modal fade" id="modal-sync-config" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Konfigurasi Sinkronisasi</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <div class="mb-5">
                    <label class="form-label fw-bold">Jumlah Media</label>
                    <input type="number" id="sync-limit" class="form-control form-control-solid" value="100" min="1" step="1">
                    <div class="text-muted fs-7 mt-2">Masukkan jumlah item yang ingin disinkronkan dari antrian.</div>
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btn-start-sync" class="btn btn-primary">
                    <span class="indicator-label">Mulai Sinkron Sekarang</span>
                </button>
            </div>
        </div>
    </div>
</div>

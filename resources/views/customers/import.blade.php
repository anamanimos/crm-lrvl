<x-metronic-layout>
    @php
        $title = 'Import Kontak ERP';
    @endphp

<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Import Kontak ERP
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.customers.index') }}" class="text-muted text-hover-primary">Customer</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Import ERP</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm fw-bold btn-secondary">
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
            
            @if (isset($erp_error) && $erp_error)
            <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row p-5 mb-10">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"></i>
                <div class="d-flex flex-column pe-0 pe-sm-10">
                    <h4 class="fw-bold">ERP API Connection Error</h4>
                    <span>{{ $erp_error }}</span>
                </div>
                <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                    <i class="ki-outline ki-cross fs-1 text-danger"></i>
                </button>
            </div>
            @endif
            <!--begin::Stats-->
            <div class="row g-5 mb-5">
                <div class="col-md-4">
                    <div class="card card-flush">
                        <div class="card-body py-5">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-people fs-2x text-primary"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-400 fw-semibold">Kontak di ERP</span>
                                    <span class="fs-2 fw-bold text-gray-800">{{ number_format($erp_count) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-flush">
                        <div class="card-body py-5">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-outline ki-check-circle fs-2x text-success"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-400 fw-semibold">Sudah di CRM</span>
                                    <span class="fs-2 fw-bold text-gray-800">{{ number_format($crm_count) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-flush">
                        <div class="card-body py-5">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-info">
                                        <i class="ki-outline ki-message-text-2 fs-2x text-info"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-400 fw-semibold">Sumber</span>
                                    <span class="fs-6 fw-bold text-gray-800">app.damaijaya.my.id</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Stats-->

            <div class="row g-5">
                <!--begin::Import Settings-->
                <div class="col-xl-4">
                    <div class="card card-flush h-100">
                        <div class="card-header">
                            <h3 class="card-title">Pengaturan Import</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-5">
                                <label class="form-label">Filter Status ERP</label>
                                <select id="filter_status" class="form-select form-select-solid">
                                    <option value="">Semua Status</option>
                                    <option value="contact">Contact</option>
                                    <option value="prospect">Prospect</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            
                            <div class="mb-5">
                                <label class="form-label">Cari Kontak</label>
                                <input type="text" id="search_contact" class="form-control form-control-solid" 
                                       placeholder="Nama, telepon, atau perusahaan...">
                            </div>
                            
                            <div class="mb-5">
                                <label class="form-label">Limit</label>
                                <select id="limit" class="form-select form-select-solid">
                                    <option value="20">20 kontak</option>
                                    <option value="50" selected>50 kontak</option>
                                    <option value="100">100 kontak</option>
                                    <option value="500">500 kontak</option>
                                </select>
                            </div>
                            
                            <hr class="my-5">
                            
                            <div class="mb-5">
                                <label class="form-label">Assign Label</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($labels as $label)
                                    <label class="form-check form-check-custom form-check-sm">
                                        <input type="checkbox" class="form-check-input label-check" value="{{ $label->id }}">
                                        <span class="form-check-label">
                                            <span class="badge" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mb-5">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="skip_existing" checked>
                                    <span class="form-check-label fw-semibold text-muted">Lewati yang sudah ada</span>
                                </label>
                            </div>
                            
                            <button type="button" id="btn-load" class="btn btn-light-primary w-100 mb-3">
                                <i class="ki-outline ki-arrow-down fs-4"></i>
                                Muat Kontak
                            </button>
                            
                            <button type="button" id="btn-import" class="btn btn-primary w-100 mb-3" disabled>
                                <i class="ki-outline ki-check fs-4"></i>
                                Import Terpilih (<span id="selected-count">0</span>)
                            </button>

                            <div class="dropdown">
                                <button class="btn btn-light-primary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ki-outline ki-arrows-circle fs-4"></i>
                                    Sync / Import Masal
                                </button>
                                <ul class="dropdown-menu w-100">
                                    <li>
                                        <a class="dropdown-item px-3 py-2 cursor-pointer" id="btn-sync-skip">
                                            <div class="fw-bold">Sync Data Baru</div>
                                            <div class="text-muted fs-8">Hanya tambah yang belum ada (Skip Duplikat)</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item px-3 py-2 cursor-pointer" id="btn-sync-overwrite">
                                            <div class="fw-bold">Sync & Update Full</div>
                                            <div class="text-muted fs-8">Update data lama & tambah baru</div>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item px-3 py-2 cursor-pointer text-danger" id="btn-reset">
                                            <div class="fw-bold"><i class="ki-outline ki-trash text-danger me-2"></i>Reset Total</div>
                                            <div class="text-muted fs-8">Hapus SEMUA data CRM & Import Ulang</div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Import Settings-->
                
                <!--begin::Contact Preview-->
                <div class="col-xl-8">
                    <div class="card card-flush">
                        <div class="card-header">
                            <h3 class="card-title">Preview Kontak</h3>
                            <div class="card-toolbar">
                                <label class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                    <span class="form-check-label">Pilih Semua</span>
                                </label>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div id="contacts-loading" class="text-center py-15 d-none">
                                <span class="spinner-border text-primary"></span>
                                <div class="mt-3 text-gray-600">Memuat kontak...</div>
                            </div>
                            
                            <div id="contacts-empty" class="text-center py-15">
                                <i class="ki-outline ki-people fs-3x text-gray-300 mb-5"></i>
                                <div class="fw-semibold text-gray-600">Klik "Muat Kontak" untuk melihat preview</div>
                            </div>
                            
                            <div id="contacts-table" class="d-none">
                                <table class="table align-middle table-row-dashed fs-6 gy-3">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-25px"></th>
                                            <th>Nama</th>
                                            <th>No. WA</th>
                                            <th>Perusahaan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contacts-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Contact Preview-->
            </div>

        </div>
    </div>
    <!--end::Content-->
    <!--begin::Conflict Modal-->
    <div class="modal fade" tabindex="-1" id="modal_conflicts">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Konflik Data Duplikat</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                        <i class="ki-outline ki-information-2 fs-2hx text-warning me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">Beberapa kontak sudah ada di CRM.</span>
                            <span>Silakan putuskan tindakan untuk setiap kontak yang duplikat.</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-150px rounded-start">Data Baru (ERP)</th>
                                    <th class="min-w-150px">Data Lama (CRM)</th>
                                    <th class="min-w-150px text-end pe-4 rounded-end">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="conflict-list">
                                <!-- Dynamic Content -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btn-resolve-save" class="btn btn-primary">
                        Simpan & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Conflict Modal-->
</div>
<!--end::Content wrapper-->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var contacts = [];
    var baseUrl = "{{ route('admin.customers.import') }}/";
    
    // Load contacts
    document.getElementById('btn-load').addEventListener('click', function() {
        loadContacts();
    });
    
    function loadContacts() {
        var search = document.getElementById('search_contact').value;
        var status = document.getElementById('filter_status').value;
        var limit = document.getElementById('limit').value;
        
        document.getElementById('contacts-loading').classList.remove('d-none');
        document.getElementById('contacts-empty').classList.add('d-none');
        document.getElementById('contacts-table').classList.add('d-none');
        
        var params = new URLSearchParams({ search: search, status: status, limit: limit });
        
        fetch(baseUrl + 'preview?' + params.toString())
            .then(response => response.json())
            .then(data => {
                document.getElementById('contacts-loading').classList.add('d-none');
                
                if (data.success && data.data.length > 0) {
                    contacts = data.data;
                    renderContacts(contacts);
                    document.getElementById('contacts-table').classList.remove('d-none');
                } else {
                    document.getElementById('contacts-empty').classList.remove('d-none');
                    document.getElementById('contacts-empty').innerHTML = 
                        '<i class="ki-outline ki-people fs-3x text-gray-300 mb-5"></i>' +
                        '<div class="fw-semibold text-gray-600">Tidak ada kontak ditemukan</div>';
                }
            })
            .catch(err => {
                document.getElementById('contacts-loading').classList.add('d-none');
                document.getElementById('contacts-empty').classList.remove('d-none');
                console.error(err);
            });
    }
    
    function renderContacts(contacts) {
        var tbody = document.getElementById('contacts-body');
        var skipExisting = document.getElementById('skip_existing').checked;
        
        var html = '';
        contacts.forEach(function(c) {
            var disabled = skipExisting && c.already_exists ? 'disabled' : '';
            var existsBadge = c.already_exists ? '<span class="badge badge-light-warning">Sudah ada</span>' : '';
            
            html += '<tr>' +
                '<td><input type="checkbox" class="form-check-input contact-check" value="' + c.id + '" ' + disabled + '></td>' +
                '<td><span class="fw-bold">' + (escapeHtml(c.full_name) || '-') + '</span></td>' +
                '<td>' + c.formatted_phone + ' ' + existsBadge + '</td>' +
                '<td>' + (escapeHtml(c.company) || '-') + '</td>' +
                '<td><span class="badge badge-light">' + (escapeHtml(c.status) || '-') + '</span></td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
        
        updateSelectedCount();
        
        // Add listeners to checkboxes
        document.querySelectorAll('.contact-check').forEach(function(cb) {
            cb.addEventListener('change', updateSelectedCount);
        });
    }
    
    function updateSelectedCount() {
        var count = document.querySelectorAll('.contact-check:checked').length;
        document.getElementById('selected-count').textContent = count;
        document.getElementById('btn-import').disabled = count === 0;
    }
    
    // Select all
    document.getElementById('select-all').addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.contact-check:not(:disabled)').forEach(function(cb) {
            cb.checked = checked;
        });
        updateSelectedCount();
    });
    
    // Skip existing toggle - re-render
    document.getElementById('skip_existing').addEventListener('change', function() {
        if (contacts.length > 0) {
            renderContacts(contacts);
        }
    });
    
    // Import with Conflict Check
    document.getElementById('btn-import').addEventListener('click', function() {
        startImportProcess(true);
    });

    // Conflict Modal Save
    document.getElementById('btn-resolve-save').addEventListener('click', function() {
        startImportProcess(false, getResolutions());
    });
    
    function startImportProcess(checkConflicts, resolutions = {}) {
        var selectedIds = [];
        document.querySelectorAll('.contact-check:checked').forEach(function(cb) {
            selectedIds.push(cb.value);
        });
        
        if (selectedIds.length === 0) {
            Swal.fire('Peringatan', 'Pilih minimal satu kontak', 'warning');
            return;
        }
        
        var labels = [];
        document.querySelectorAll('.label-check:checked').forEach(function(cb) {
            labels.push(cb.value);
        });
        
        var formData = new FormData();
        selectedIds.forEach(function(id) {
            formData.append('contact_ids[]', id);
        });
        labels.forEach(function(id) {
            formData.append('labels[]', id);
        });

        formData.append('skip_existing', document.getElementById('skip_existing').checked ? '1' : '0');
        formData.append('_token', '{{ csrf_token() }}');
        
        // Conflict Parameters
        if (checkConflicts) {
             formData.append('check_conflicts', '1');
        }
        
        // Add resolutions
        Object.keys(resolutions).forEach(key => {
            formData.append(`resolutions[${key}]`, resolutions[key]);
        });
        
        // UI Handling
        if (checkConflicts) {
             Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memeriksa data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }
        
        fetch(baseUrl + 'import', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.status === 'conflict') {
                showConflictModal(data.conflicts);
            } else if (data.success) {
                // Determine icon based on result
                $('#modal_conflicts').modal('hide');
                Swal.fire('Selesai!', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        });
    }

    function showConflictModal(conflicts) {
        var tbody = document.getElementById('conflict-list');
        var html = '';
        
        conflicts.forEach(c => {
            html += `<tr>
                <td class="ps-4">
                    <div class="fw-bold text-gray-800">${escapeHtml(c.new.name)}</div>
                    <div class="text-muted fs-7">${c.phone}</div>
                    <div class="badge badge-light-primary fontsize-7 mt-1">${escapeHtml(c.new.company)}</div>
                </td>
                <td>
                    <div class="fw-bold text-gray-800">${escapeHtml(c.existing.name)}</div>
                    <div class="text-muted fs-7">${c.phone}</div>
                    <div class="badge badge-light-secondary fontsize-7 mt-1">${escapeHtml(c.existing.company)}</div>
                </td>
                <td class="pe-4 text-end">
                    <div class="d-flex flex-column gap-2 align-items-end">
                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-2 d-flex align-items-center w-100">
                            <input type="radio" class="form-check-input me-2" name="resolve_${c.erp_id}" value="skip" checked>
                            <span class="fs-7 fw-bold">Biarkan Lama</span>
                        </label>
                        <label class="btn btn-outline btn-outline-dashed btn-active-light-success p-2 d-flex align-items-center w-100">
                            <input type="radio" class="form-check-input me-2" name="resolve_${c.erp_id}" value="overwrite">
                            <span class="fs-7 fw-bold">Update Baru</span>
                        </label>
                    </div>
                </td>
            </tr>`;
        });
        
        tbody.innerHTML = html;
        $('#modal_conflicts').modal('show');
    }

    function getResolutions() {
        var resolutions = {};
        document.querySelectorAll('[name^="resolve_"]:checked').forEach(radio => {
            // name is resolve_123
            var id = radio.name.replace('resolve_', '');
            resolutions[id] = radio.value;
        });
        return resolutions;
    }

    // =========================================================================
    // BULK IMPORT / SYNC LOGIC
    // =========================================================================

    // 1. Sync Skip (Safe)
    document.getElementById('btn-sync-skip').addEventListener('click', function() {
        confirmBatchImport(false, 'skip', 'Sync Data Baru', 'Hanya kontak yang belum ada akan ditambahkan. Data lama AMAN.');
    });

    // 2. Sync Overwrite (Update)
    document.getElementById('btn-sync-overwrite').addEventListener('click', function() {
        confirmBatchImport(false, 'overwrite', 'Sync & Update Full', 'Data CRM yang duplikat akan DI-UPDATE mengikuti data ERP. Kontak baru juga ditambahkan.');
    });

    // 3. Reset (Destructive)
    document.getElementById('btn-reset').addEventListener('click', function() {
        confirmBatchImport(true, 'skip', 'RESET TOTAL', 'PERINGATAN: SEMUA data Customer di CRM akan DIHAPUS sebelum import. Tindakan tidak bisa dibatalkan!');
    });

    function confirmBatchImport(isTruncate, strategy, title, text) {
        Swal.fire({
            title: title + '?',
            text: text,
            icon: isTruncate ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: isTruncate ? '#d33' : '#3085d6',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showProgressModal();
                startBatchImport(isTruncate, strategy);
            }
        });
    }

    function showProgressModal() {
        Swal.fire({
            title: 'Sedang Memproses...',
            html: 'Mempersiapkan...<br><br><div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div></div><br><span id="import-status">Memulai...</span>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    async function startBatchImport(doTruncate, strategy) {
        try {
            const baseUrl = "{{ route('admin.customers.import') }}/";
            
            // STEP 1: TRUNCATE (Optional)
            if (doTruncate) {
                document.getElementById('import-status').innerText = 'Mengosongkan database...';
                const truncateRes = await fetch(baseUrl + 'reset_truncate').then(res => res.json());
                
                if (!truncateRes.success) {
                    throw new Error(truncateRes.message || 'Gagal mengosongkan database.');
                }
            }
            
            // STEP 2: LOOP IMPORT PAGINATION
            let page = 1;
            let totalImported = 0;
            let hasMore = true;
            const statusEl = document.getElementById('import-status');
            const progressBar = document.querySelector('.swal2-html-container .progress-bar');
            
            while (hasMore) {
                statusEl.innerText = `Memproses halaman ${page}... (Total: ${totalImported})`;
                
                // Fetch Batch with Strategy
                const batchRes = await fetch(`${baseUrl}batch?page=${page}&strategy=${strategy}`).then(res => res.json());
                
                if (!batchRes.success) {
                    throw new Error('Gagal mengambil data halaman ' + page);
                }
                
                if (batchRes.count > 0) {
                    totalImported += (batchRes.inserted !== undefined ? batchRes.inserted : batchRes.count);
                    
                    let percent = Math.min((page * 5), 95); 
                    if(progressBar) progressBar.style.width = percent + '%';
                }
                
                hasMore = batchRes.has_more;
                page++;
                
                if (page > 2000) hasMore = false; // Safety
            }
            
            if(progressBar) progressBar.style.width = '100%';
            
            Swal.fire({
                icon: 'success',
                title: 'Selesai!',
                text: `Proses Selesai! Menambahkan/Mengupdate ${totalImported} kontak.`,
            }).then(() => {
                location.reload();
            });

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.message || 'Terjadi kesalahan.',
            });
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
@endpush
</x-metronic-layout>

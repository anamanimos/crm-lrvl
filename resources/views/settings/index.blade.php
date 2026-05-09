<x-metronic-layout>
    @php
        $title = 'Pengaturan: ' . ($sections[$section] ?? 'Sistem');
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Pengaturan Sistem
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('settings.index') }}" class="text-muted text-hover-primary">Pengaturan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ $sections[$section] ?? 'Umum' }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    @if($section == 'whatsapp' && $subsection == 'webhook')
    @push('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    @endpush
    @push('js')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    @endpush
    @endif

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            @if (session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="d-flex flex-column flex-lg-row">
                <!--begin::Aside column-->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-300px mb-10 mb-lg-0 me-lg-7 me-xl-10">
                    <!--begin::Navigation-->
                    <div class="card card-flush py-4 sticky-lg-top" style="top: 80px">
                        <div class="card-header">
                            <div class="card-title">
                                <h2 class="fs-4 fw-bold text-gray-800">Kategori</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('settings.section', 'general') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'general' ? 'active' : '' }}">
                                    <i class="ki-outline ki-gear fs-3 me-2"></i> Umum
                                </a>
                                <a href="{{ route('settings.section', 'whatsapp') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'whatsapp' ? 'active' : '' }}">
                                    <i class="ki-outline ki-whatsapp fs-3 me-2"></i> WhatsApp Gateway
                                </a>
                                <a href="{{ route('settings.section', 'erp') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'erp' ? 'active' : '' }}">
                                    <i class="ki-outline ki-cube-2 fs-3 me-2"></i> Sistem ERP
                                </a>
                                <a href="{{ route('settings.section', 'katalog') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'katalog' ? 'active' : '' }}">
                                    <i class="ki-outline ki-shop fs-3 me-2"></i> Katalog Produk
                                </a>
                                <a href="{{ route('settings.section', 'google') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'google' ? 'active' : '' }}">
                                    <i class="ki-outline ki-google fs-3 me-2"></i> Google Contact
                                </a>
                                <a href="{{ route('settings.section', 'ai') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'ai' ? 'active' : '' }}">
                                    <i class="ki-outline ki-abstract-26 fs-3 me-2"></i> AI Assistant
                                </a>
                                <a href="{{ route('settings.section', 'storage') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'storage' ? 'active' : '' }}">
                                    <i class="ki-outline ki-folder-down fs-3 me-2"></i> Cloud Storage
                                </a>
                                <a href="{{ route('settings.section', 'backup') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ $section == 'backup' ? 'active' : '' }}">
                                    <i class="ki-outline ki-cloud-change fs-3 me-2"></i> Backup & Maintenance
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Navigation-->
                </div>
                <!--end::Aside column-->

                <!--begin::Main column-->
                <div class="flex-row-fluid">
                    <form action="{{ route('settings.store') }}" method="POST" id="settings_form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="{{ $section }}">
                        <input type="hidden" name="subsection" value="{{ $subsection }}">
                        
                        @if (view()->exists('settings.sections.' . $section))
                            @include('settings.sections.' . $section)
                        @else
                            <div class="card card-flush py-4">
                                <div class="card-body pt-0">
                                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                        <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
                                        <div class="d-flex flex-stack flex-grow-1">
                                            <div class="fw-semibold">
                                                <h4 class="text-gray-900 fw-bold">Halaman Belum Tersedia</h4>
                                                <div class="fs-6 text-gray-700">Modul pengaturan untuk <strong>{{ $sections[$section] ?? $section }}</strong> sedang dalam tahap migrasi.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($subsection != 'koneksi' && $subsection != 'webhook')
                        <div class="d-flex justify-content-end mt-7">
                            <button type="submit" class="btn btn-primary" id="kt_settings_submit">
                                <span class="indicator-label">Simpan Perubahan</span>
                                <span class="indicator-progress">Please wait... 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
                <!--end::Main column-->
            </div>
        </div>
    </div>
    <!--end::Content-->

    @push('js')
    <script>
    (function() {
        "use strict";
        
        const copyToClipboard = (text) => {
            if (!navigator.clipboard) {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Tersalin!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } catch (err) {
                    console.error('Gagal menyalin:', err);
                }
                document.body.removeChild(textArea);
                return;
            }
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Tersalin!',
                    showConfirmButton: false,
                    timer: 1500
                });
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        };
        window.copyToClipboard = copyToClipboard;

        const initSettingsPage = () => {
            // Global Vars
            let statusCheckInterval = null;
            let qrUpdateInterval = null;
            let countdownInterval = null;
            let isPairing = false;

            // --- WhatsApp Methods ---
            const checkWaStatus = (btnInfoClick = false) => {
                fetch("{{ route('settings.whatsapp.status') }}")
                    .then(response => response.json())
                    .then(data => {
                        let isConnected = false;
                        let user = '';
                        
                        // Handle Go-WA API response structure
                        if (data.success && data.data) {
                            const results = data.data.results || data.data.data?.results;
                            if (results) {
                                isConnected = results.is_connected && results.is_logged_in;
                                user = results.device_id || '';
                            } else if (data.data.status === 'connected' || data.data.status === 'ready') {
                                // Fallback for other versions
                                isConnected = true;
                                user = data.data.user || data.data.number || '';
                            }
                        }
                        
                        if (isConnected) {
                            showConnectedUI(user);
                            if (btnInfoClick) Swal.fire('OK', 'Koneksi ke gateway lancar.', 'success');
                        } else {
                            showDisconnectedUI();
                        }
                    })
                    .catch(() => showErrorUI());
            };

            const showConnectedUI = (userData) => {
                const statusContainer = document.getElementById('wa-status-container');
                const actions = document.getElementById('wa-actions');
                const btnPair = document.getElementById('btn-pair-wa');
                const btnLogout = document.getElementById('btn-logout-wa');
                
                if (!statusContainer) return;

                statusContainer.innerHTML = `
                    <div class="mb-3"><i class="ki-outline ki-check-circle fs-5x text-success"></i></div>
                    <h3 class="text-success fw-bold">TERKONEKSI PADA JARINGAN</h3>
                    <p class="text-muted">Gateway API beroperasi secara normal.</p>
                    ${userData ? `<div class="badge badge-light-success fw-bold fs-6 mt-2">${userData}</div>` : ''}
                `;
                
                actions.classList.remove('d-none');
                btnPair.classList.add('d-none');
                btnLogout.classList.remove('d-none');
                document.getElementById('qr-container-wrapper').classList.add('d-none');
                
                if (!statusCheckInterval) {
                    statusCheckInterval = setInterval(() => checkWaStatus(), 30000);
                }
            };

            const showDisconnectedUI = () => {
                const statusContainer = document.getElementById('wa-status-container');
                const actions = document.getElementById('wa-actions');
                const btnPair = document.getElementById('btn-pair-wa');
                const btnLogout = document.getElementById('btn-logout-wa');
                
                if (!statusContainer) return;

                statusContainer.innerHTML = `
                    <div class="mb-3"><i class="ki-outline ki-disconnect fs-5x text-warning"></i></div>
                    <h3 class="text-warning fw-bold">TERPUTUS</h3>
                    <p class="text-muted">Perangkat WhatsApp belum ditautkan atau sesi telah berakhir.</p>
                `;
                
                actions.classList.remove('d-none');
                btnPair.classList.remove('d-none');
                btnLogout.classList.add('d-none');
                
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                    statusCheckInterval = null;
                }
            };

            const showErrorUI = () => {
                const statusContainer = document.getElementById('wa-status-container');
                if (!statusContainer) return;

                statusContainer.innerHTML = `
                    <div class="mb-3"><i class="ki-outline ki-cross-circle fs-5x text-danger"></i></div>
                    <h3 class="text-danger fw-bold">GAGAL TERHUBUNG KE GATEWAY SERVER</h3>
                    <p class="text-muted">Pastikan server gateway sedang berjalan normal dan URL Gateway diatur dengan benar.</p>
                `;
                document.getElementById('wa-actions')?.classList.add('d-none');
            };

            const startPairing = () => {
                isPairing = true;
                const btnPair = document.getElementById('btn-pair-wa');
                const qrWrapper = document.getElementById('qr-container-wrapper');
                const qrContainer = document.getElementById('qr-container');
                
                btnPair.classList.add('d-none');
                qrWrapper.classList.remove('d-none');
                qrContainer.innerHTML = '<div class="spinner-border text-primary my-5" role="status"></div>';
                
                updateQR();
                qrUpdateInterval = setInterval(updateQR, 20000);
            };

            const updateQR = () => {
                const qrContainer = document.getElementById('qr-container');
                fetch("{{ route('settings.whatsapp.pairing') }}")
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data && data.data.qr_link) {
                            qrContainer.innerHTML = '';
                            new QRCode(qrContainer, {
                                text: data.data.qr_link,
                                width: 256,
                                height: 256
                            });
                            startCountdown(20);
                        } else {
                            // Check if already connected
                            checkWaStatus();
                        }
                    });
            };

            const startCountdown = (seconds) => {
                if (countdownInterval) clearInterval(countdownInterval);
                let timeLeft = seconds;
                const display = document.getElementById('qr-countdown');
                countdownInterval = setInterval(() => {
                    timeLeft--;
                    if (display) display.innerHTML = `QR Code berubah dalam: ${timeLeft} detik`;
                    if (timeLeft <= 0) clearInterval(countdownInterval);
                }, 1000);
            };

            const cancelPairing = () => {
                isPairing = false;
                if (qrUpdateInterval) clearInterval(qrUpdateInterval);
                if (countdownInterval) clearInterval(countdownInterval);
                document.getElementById('qr-container-wrapper').classList.add('d-none');
                document.getElementById('btn-pair-wa').classList.remove('d-none');
            };

            const logoutWa = () => {
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Sesi WhatsApp akan diputus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Logout!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('settings.whatsapp.logout') }}")
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Berhasil!', 'Koneksi diputus.', 'success');
                                    showDisconnectedUI();
                                }
                            });
                    }
                });
            };

            // --- Init Events ---
            if (document.getElementById('wa-status-container')) {
                checkWaStatus();
                document.getElementById('btn-refresh-status')?.addEventListener('click', () => checkWaStatus(true));
                document.getElementById('btn-pair-wa')?.addEventListener('click', startPairing);
                document.getElementById('btn-cancel-pair')?.addEventListener('click', cancelPairing);
                document.getElementById('btn-logout-wa')?.addEventListener('click', logoutWa);
            }

            // Webhook Logs Logic
            const modalEl = document.getElementById('modal_view_payload');
            if (modalEl) {
                const modalPayload = new bootstrap.Modal(modalEl);
                const payloadContent = document.getElementById('payload_content');

                document.querySelectorAll('.btn-view-payload').forEach(btn => {
                    btn.addEventListener('click', function() {
                        payloadContent.innerText = this.getAttribute('data-payload');
                        modalPayload.show();
                    });
                });
            }

            document.querySelectorAll('.btn-delete-log').forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    Swal.fire({
                        title: 'Hapus Log?',
                        text: "Log webhook ini akan dihapus permanen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(url, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(() => location.reload());
                        }
                    });
                });
            });

            const btnClearOldLogs = document.getElementById('btn-clear-webhook-logs');
            if (btnClearOldLogs) {
                btnClearOldLogs.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Bersihkan Log?',
                        text: "Log yang lebih lama dari 7 hari akan dihapus.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Bersihkan!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("{{ route('settings.whatsapp.webhook.clear') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(response => response.json()).then(data => {
                                if (data.success) Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                            });
                        }
                    });
                });
            }

            // Initialize DataTables
            const webhookTable = document.getElementById('kt_webhook_logs_table');
            if (webhookTable && $.fn.DataTable && webhookTable.querySelectorAll('tbody tr td[colspan]').length === 0) {
                const datatable = $(webhookTable).DataTable({
                    "info": false, 'order': [], 'pageLength': 50, 'lengthChange': false, 'searching': true, 'paging': false,
                    'dom': "<'row'<'col-sm-12'tr>>",
                    'columnDefs': [
                        { orderable: false, targets: 0 },
                        { orderable: false, targets: 6 },
                        { orderable: false, targets: 8 },
                    ]
                });

                document.getElementById('table-search')?.addEventListener('keyup', (e) => datatable.search(e.target.value).draw());
                document.getElementById('table-search')?.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        const url = new URL(window.location.href);
                        url.searchParams.set('search', e.target.value);
                        window.location.href = url.href;
                    }
                });
            }

            // Bulk Action Handlers
            $(document).on('change', '[data-kt-check="true"]', function() {
                const target = $(this).attr('data-kt-check-target');
                const checked = $(this).is(':checked');
                $(target).prop('checked', checked).trigger('change');
            });

            $(document).on('change', '.sync-checkbox', function() {
                const checkedCount = $('.sync-checkbox:checked').length;
                $('#selected-count').text(checkedCount);
                if (checkedCount > 0) {
                    $('#btn-bulk-sync').removeClass('d-none');
                } else {
                    $('#btn-bulk-sync').addClass('d-none');
                }
            });

            // Bulk Sync Button Click
            $(document).on('click', '#btn-bulk-sync', function() {
                const selectedIds = $('.sync-checkbox:checked').map(function() { return $(this).val(); }).get();
                
                Swal.fire({
                    title: 'Sinkron Terpilih',
                    text: `Apakah Anda yakin ingin menyinkronkan ${selectedIds.length} item terpilih?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Sinkron!',
                    cancelButtonText: 'Batal',
                    customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        performSync({ ids: selectedIds });
                    }
                });
            });

            // Manual Storage Sync (Massal via Modal)
            $(document).on('click', '#btn-start-sync', function() {
                const totalLimit = parseInt($('#sync-limit').val()) || 100;
                $('#modal-sync-config').modal('hide');
                performSync({ totalLimit: totalLimit });
            });

            const performSync = (options) => {
                const totalLimit = options.ids ? options.ids.length : (options.totalLimit || 100);
                const ids = options.ids || null;
                const chunkSize = 20;
                let syncedCount = 0;
                
                Swal.fire({
                    title: 'Sinkronisasi Media',
                    html: `Memproses <b id="sync-current">0</b> dari <b>${totalLimit}</b> item...<br><div class="progress h-10px mt-5"><div id="sync-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div></div>`,
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        const processChunk = () => {
                            const remaining = totalLimit - syncedCount;
                            const currentBatchSize = Math.min(chunkSize, remaining);
                            
                            if (currentBatchSize <= 0) {
                                Swal.fire({ text: `Sinkronisasi selesai! Total ${syncedCount} item diproses.`, icon: "success", buttonsStyling: false, confirmButtonText: "Ok, mengerti!", customClass: { confirmButton: "btn btn-primary" } })
                                    .then(() => window.location.reload());
                                return;
                            }

                            const payload = { _token: "{{ csrf_token() }}" };
                            if (ids) {
                                payload.ids = ids.slice(syncedCount, syncedCount + currentBatchSize);
                            } else {
                                payload.limit = currentBatchSize;
                            }

                            $.post("{{ route('settings.whatsapp.storage_sync.start') }}", payload)
                            .done(function(res) {
                                syncedCount += currentBatchSize;
                                const percent = Math.round((syncedCount / totalLimit) * 100);
                                $('#sync-current').text(syncedCount);
                                $('#sync-progress').css('width', percent + '%');
                                setTimeout(processChunk, 500);
                            })
                            .fail(function(err) {
                                Swal.fire({ text: "Terjadi kesalahan saat sinkronisasi.", icon: "error", confirmButtonText: "Tutup" });
                            });
                        };
                        processChunk();
                    }
                });
            };

            // --- AJAX Loading Helper ---
            const loadWebhookLogs = (url) => {
                const container = $('#webhook-logs-container');
                const target = document.getElementById('webhook-table-card') || 
                               document.getElementById('storage-sync-table-card') || 
                               container[0];
                
                const blockUI = new KTBlockUI(target, { 
                    message: '<div class="blockui-message"><span class="spinner-border text-primary"></span> Loading...</div>',
                    overlayClass: 'bg-white bg-opacity-75'
                });
                blockUI.block();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        container.html(html);
                        // Update URL in browser
                        window.history.pushState({}, '', url);
                        
                        // Re-init Tooltips
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                        
                        blockUI.release();
                        blockUI.destroy();
                    });
            };

            // Filter Toggle Logic
            $(document).on('click', '.filter-toggle', function(e) {
                const btn = $(this);
                const container = btn.closest('#filter-container');
                const key = btn.attr('data-key');
                const val = btn.attr('data-val');
                
                btn.toggleClass('active');
                btn.toggleClass('btn-light-primary');
                
                if (key.includes('processed')) {
                    if (val === '1') btn.toggleClass('btn-light-success');
                    else btn.toggleClass('btn-light-danger');
                }

                if (btn.hasClass('active')) {
                    if (!container.find(`input[name="${key}"][value="${val}"]`).length) {
                        $('<input>').addClass('filter-input').attr({ type: 'hidden', name: key, value: val }).appendTo(container);
                    }
                } else {
                    container.find(`input[name="${key}"][value="${val}"]`).remove();
                }

                const action = container.data('action');
                const url = new URL(action);
                const params = new URLSearchParams();
                container.find('.filter-input').each(function() { params.append($(this).attr('name'), $(this).val()); });
                
                const searchVal = $('#table-search').val();
                if (searchVal) params.set('search', searchVal);

                loadWebhookLogs(url.pathname + '?' + params.toString());
            });

            // Remove Filter Badge (AJAX)
            $(document).on('click', '.remove-filter', function() {
                const key = $(this).data('key');
                const val = $(this).data('val');
                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);
                
                let values = params.getAll(key);
                params.delete(key);
                values.forEach(v => { if (v != val) params.append(key, v); });
                
                // Update active state in dropdown buttons
                $(`.filter-toggle[data-key="${key}"][data-val="${val}"]`).removeClass('active btn-light-primary btn-light-success btn-light-danger');
                $(`#filter-container input[name="${key}"][value="${val}"]`).remove();

                loadWebhookLogs(url.pathname + '?' + params.toString());
            });

            // Search (AJAX)
            let searchTimer;
            $(document).on('keyup', '#table-search', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', this.value);
                    loadWebhookLogs(url.pathname + '?' + url.searchParams.toString());
                }, 500);
            });

            // Pagination (AJAX)
            $(document).on('click', '.ajax-pagination a', function(e) {
                e.preventDefault();
                loadWebhookLogs($(this).attr('href'));
            });

            // Sorting (AJAX)
            $(document).on('click', 'th.sortable', function() {
                const column = $(this).data('column');
                const url = new URL(window.location.href);
                let direction = 'asc';
                
                if (url.searchParams.get('order_by') === column) {
                    direction = url.searchParams.get('order_dir') === 'asc' ? 'desc' : 'asc';
                }
                
                url.searchParams.set('order_by', column);
                url.searchParams.set('order_dir', direction);
                loadWebhookLogs(url.pathname + '?' + url.searchParams.toString());
            });

            // Per Page (AJAX)
            $(document).on('change', '#per-page-selector', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', this.value);
                url.searchParams.set('page', 1); // Reset to first page
                loadWebhookLogs(url.pathname + '?' + url.searchParams.toString());
            });

            // Reset (AJAX)
            $(document).on('click', '#btn-reset-filters', function(e) {
                e.preventDefault();
                $('#filter-container .filter-input').remove();
                $('.filter-toggle').removeClass('active btn-light-primary btn-light-success btn-light-danger');
                $('#table-search').val('');
                loadWebhookLogs($(this).attr('href'));
            });

            // --- ERP & Storage Tests ---
            const setupTest = (btnId, route, inputs) => {
                const btn = document.getElementById(btnId);
                if (!btn) return;
                btn.addEventListener('click', () => {
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Testing...';
                    
                    const params = new URLSearchParams();
                    inputs.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) params.append(id.replace('cloudinary_', '').replace('minio_', ''), el.value);
                    });
                    
                    fetch(route + "?" + params.toString())
                        .then(r => r.json())
                        .then(d => Swal.fire(d.success ? 'Berhasil!' : 'Gagal!', d.message, d.success ? 'success' : 'error'))
                        .catch(() => Swal.fire('Error!', 'Gagal menghubungi server', 'error'))
                        .finally(() => { btn.disabled = false; btn.innerHTML = originalHtml; });
                });
            };

            setupTest('btn-test-erp', "{{ route('settings.test.erp') }}", ['erp_api_url', 'erp_api_key']);
            setupTest('btn-test-cloudinary', "{{ route('settings.test.cloudinary') }}", ['cloudinary_cloud_name', 'cloudinary_api_key', 'cloudinary_api_secret']);
            setupTest('btn-test-minio', "{{ route('settings.test.minio') }}", ['minio_endpoint', 'minio_access_key', 'minio_secret_key']);

            // Cloudinary & MinIO Upload Tests
            const setupUpload = (btnId, route, inputs, resultId, downloadBtnId, deleteBtnId) => {
                const btn = document.getElementById(btnId);
                if (!btn) return;
                btn.addEventListener('click', () => {
                    const fileInput = document.getElementById(btnId.replace('btn-upload-', '') + '_test_file');
                    if (!fileInput?.files.length) return Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');

                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    inputs.forEach(id => formData.append(id.replace('cloudinary_', '').replace('minio_', ''), document.getElementById(id).value));
                    formData.append('_token', '{{ csrf_token() }}');

                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';

                    fetch(route, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(d => {
                        const resEl = document.getElementById(resultId);
                        if (d.success) {
                            resEl.innerHTML = `<div class="alert alert-success py-2"><strong>Berhasil!</strong><br><small><a href="${d.data.url}" target="_blank">${d.data.url}</a></small></div><img src="${d.data.url}" class="img-thumbnail" style="max-height:100px;" onerror="this.style.display='none'">`;
                            document.getElementById(downloadBtnId).style.display = 'inline-block';
                            document.getElementById(deleteBtnId).style.display = 'inline-block';
                        } else resEl.innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`;
                    })
                    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ki-outline ki-cloud-add fs-4"></i> Upload Test'; });
                });
            };

            setupUpload('btn-upload-cloudinary', "{{ route('settings.test.upload-cloudinary') }}", ['cloudinary_cloud_name', 'cloudinary_api_key', 'cloudinary_api_secret'], 'cloudinary_test_result', 'btn-download-cloudinary', 'btn-delete-cloudinary');
            setupUpload('btn-upload-minio', "{{ route('settings.test.upload-minio') }}", ['minio_endpoint', 'minio_access_key', 'minio_secret_key', 'minio_bucket', 'minio_region'], 'minio_test_result', 'btn-download-minio', 'btn-delete-minio');

            // --- AJAX Form Submission ---
            const settingsForm = document.getElementById('settings_form');
            if (settingsForm) {
                settingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const btn = document.getElementById('kt_settings_submit');
                    const originalHtml = btn.innerHTML;
                    const formData = new FormData(this);
                    
                    Swal.fire({
                        title: 'Mohon Tunggu',
                        text: 'Sedang menyimpan perubahan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    btn.disabled = true;
                    btn.setAttribute('data-kt-indicator', 'on');

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message || 'Terjadi kesalahan saat menyimpan.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan koneksi ke server.'
                        });
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.removeAttribute('data-kt-indicator');
                    });
                });
            }
        };

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSettingsPage);
        else initSettingsPage();
    })();
    </script>
    @endpush
</x-metronic-layout>

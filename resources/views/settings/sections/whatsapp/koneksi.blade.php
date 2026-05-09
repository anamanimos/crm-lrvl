<div class="row g-5">
    <div class="col-lg-6">
        <div class="card card-flush shadow-sm border-0 mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Status Koneksi Perangkat</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">Sesi ID: {{ \App\Models\Setting::get('gowa_device_id', 'crm-session') }}</span>
                </h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-primary" id="btn-refresh-status">
                        <i class="ki-outline ki-arrows-circle fs-4"></i> Refresh Status
                    </button>
                </div>
            </div>
            <div class="card-body text-center pt-5 pb-10">
                <div id="wa-status-container" class="mb-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memeriksa koneksi ke WhatsApp Gateway...</p>
                </div>

                <!-- Action Buttons & In-line QR -->
                <div id="wa-actions" class="d-none">
                    <button id="btn-pair-wa" class="btn btn-lg btn-primary fw-bold px-6 rounded-pill d-none">
                        <i class="ki-outline ki-scan-barcode fs-2 me-2"></i> Tautkan Perangkat
                    </button>
                    
                    <button id="btn-logout-wa" class="btn btn-light-danger fw-bold px-6 rounded-pill d-none">
                        <i class="ki-outline ki-exit-right fs-2 me-2"></i> Keluar / Lepas Tautan
                    </button>
                    
                    <!-- Inline QR Container -->
                    <div id="qr-container-wrapper" class="d-none mt-5">
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-5 p-4">
                            <i class="ki-outline ki-information fs-2tx text-primary me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold text-start">
                                    <div class="fs-6 text-gray-700">Buka WhatsApp lalu pilih <strong>Perangkat Tertaut</strong>. Arahkan kamera ke QR ini:</div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="qr-container" class="border border-gray-300 rounded p-3 d-inline-block shadow-sm mb-2 bg-white"></div>
                        <div class="text-danger small fw-bold mt-2" id="qr-countdown"></div>
                        <div class="mt-5">
                            <button type="button" id="btn-cancel-pair" class="btn btn-sm btn-light-secondary">Batal Tautkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-flush shadow-sm border-0 border-primary border-dashed rounded bg-light-primary">
            <div class="card-body">
                <h2 class="mb-5 text-primary"><i class="ki-outline ki-information-4 text-primary fs-2 me-2"></i> Petunjuk Koneksi</h2>
                <p class="fs-6 text-gray-700">Modul ini menangani koneksi perangkat Anda ke Gateway WhatsApp Go-WA yang berjalan di <code>{{ \App\Models\Setting::get('gowa_api_url', 'https://wag.anam.ch') }}</code>.</p>
                <ul class="fs-6 text-gray-700">
                    <li><strong>Status Terkoneksi</strong>: Perangkat dapat mengirim dan menerima pesan.</li>
                    <li><strong>Status Terputus</strong>: Anda perlu menautkan ulang dengan memindai kode QR.</li>
                    <li>Jika Gateway URL salah, lakukan perubahan di tab <a href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'pengaturan']) }}">Gateway</a>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
@endpush

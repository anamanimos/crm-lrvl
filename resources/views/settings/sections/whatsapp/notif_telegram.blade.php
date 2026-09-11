<div class="row g-7">
    <div class="col-xl-8">
        <div class="card card-flush shadow-sm py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="fw-bold text-gray-800">
                        <i class="ki-outline ki-send fs-2 me-2 text-primary"></i> Notifikasi Telegram Status WhatsApp
                    </h2>
                </div>
            </div>
            
            <div class="card-body pt-0">
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                    <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Pemantauan Koneksi WhatsApp 24/7</h4>
                            <div class="fs-6 text-gray-700">
                                Sistem akan memonitor gateway secara berkala dan mengirim peringatan instan ke Telegram saat koneksi WhatsApp terputus atau tersambung kembali.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Master Toggle -->
                <div class="d-flex flex-stack mb-8 p-5 bg-light rounded border border-gray-200">
                    <div class="me-5">
                        <label class="fs-6 fw-bold text-gray-800 d-block">Aktifkan Notifikasi Telegram</label>
                        <div class="fs-7 text-muted">Nyalakan sakelar ini untuk mengaktifkan pengiriman notifikasi status WhatsApp ke Telegram.</div>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input h-30px w-50px" type="checkbox" name="telegram_wa_alert_enabled" value="1" id="telegram_wa_alert_enabled" {{ \App\Models\Setting::get('telegram_wa_alert_enabled') == '1' ? 'checked' : '' }} />
                    </div>
                </div>

                <!-- Bot Configuration -->
                <div class="row g-7 mb-7">
                    <div class="col-md-6">
                        <label class="fs-6 fw-semibold mb-2">
                            Telegram Bot Token
                            <i class="ki-outline ki-information-2 fs-7 ms-1" data-bs-toggle="tooltip" title="Dapat menggunakan token khusus atau dikosongkan untuk memakai token default dari menu Backup"></i>
                        </label>
                        <input type="password" name="telegram_wa_bot_token" id="telegram_wa_bot_token" class="form-control form-control-solid" value="{{ \App\Models\Setting::get('telegram_wa_bot_token') }}" placeholder="{{ \App\Models\Setting::get('telegram_bot_token') ? 'Menggunakan bot global (Default)' : '123456789:ABCDefgh...' }}" />
                        <div class="text-muted fs-7 mt-2">
                            @if(\App\Models\Setting::get('telegram_bot_token'))
                                <span class="badge badge-light-success fs-8">Bot Global Aktif</span> Jika dikosongkan, sistem memakai bot dari menu Backup.
                            @else
                                Masukkan token dari <strong>@BotFather</strong>.
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="fs-6 fw-semibold mb-2">Telegram Chat ID / Group ID</label>
                        <input type="text" name="telegram_wa_chat_id" id="telegram_wa_chat_id" class="form-control form-control-solid" value="{{ \App\Models\Setting::get('telegram_wa_chat_id') }}" placeholder="{{ \App\Models\Setting::get('telegram_chat_id') ?: '-100123456789 atau ID User' }}" />
                        <div class="text-muted fs-7 mt-2">
                            ID User / Grup tujuan. Jika kosong, memakai chat ID global: <code>{{ \App\Models\Setting::get('telegram_chat_id') ?: '-' }}</code>
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-7"></div>

                <!-- Notification Triggers -->
                <h4 class="fw-bold text-gray-800 mb-5">Pilihan Kondisi Notifikasi</h4>
                <div class="d-flex flex-column gap-5 mb-8">
                    <div class="d-flex flex-stack">
                        <div class="d-flex align-items-center me-5">
                            <span class="bullet bullet-vertical bg-danger h-30px me-4"></span>
                            <div>
                                <label class="fs-6 fw-bold text-gray-800 mb-0">Notifikasi WhatsApp Terputus (Disconnect / Logout)</label>
                                <div class="fs-7 text-muted">Kirim peringatan darurat saat perangkat terputus atau sesi gateway keluar.</div>
                            </div>
                        </div>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="telegram_wa_notify_disconnect" value="1" id="telegram_wa_notify_disconnect" {{ \App\Models\Setting::get('telegram_wa_notify_disconnect', '1') == '1' ? 'checked' : '' }} />
                        </div>
                    </div>

                    <div class="d-flex flex-stack">
                        <div class="d-flex align-items-center me-5">
                            <span class="bullet bullet-vertical bg-success h-30px me-4"></span>
                            <div>
                                <label class="fs-6 fw-bold text-gray-800 mb-0">Notifikasi WhatsApp Terhubung Kembali (Connected)</label>
                                <div class="fs-7 text-muted">Kirim informasi saat koneksi WhatsApp berhasil ditautkan atau kembali aktif.</div>
                            </div>
                        </div>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="telegram_wa_notify_connect" value="1" id="telegram_wa_notify_connect" {{ \App\Models\Setting::get('telegram_wa_notify_connect', '1') == '1' ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-7"></div>

                <!-- Test Dispatcher -->
                <div class="d-flex flex-stack bg-light-primary p-5 rounded border border-primary border-opacity-25">
                    <div class="me-5">
                        <label class="fs-6 fw-bold text-gray-800">Uji Coba Pengiriman Pesan</label>
                        <div class="fs-7 text-muted">Kirim pesan uji coba langsung ke Telegram untuk memastikan konfigurasi bot dan chat ID valid.</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm flex-shrink-0" id="btn-test-wa-telegram">
                        <i class="ki-outline ki-send fs-3 me-1"></i> Test Kirim Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Guide & Preview -->
    <div class="col-xl-4">
        <div class="card card-flush shadow-sm py-4 mb-7 bg-light-info border-info border-dashed">
            <div class="card-body">
                <h3 class="card-title text-info mb-4">
                    <i class="ki-outline ki-information-4 text-info fs-2 me-2"></i> Panduan Konfigurasi
                </h3>
                <ol class="fs-7 text-gray-700 ps-4 mb-0">
                    <li class="mb-2">Buka bot <strong>@BotFather</strong> di Telegram untuk membuat bot baru atau mengambil <strong>HTTP API Token</strong>.</li>
                    <li class="mb-2">Buat grup Telegram (jika ingin notifikasi masuk ke grup), lalu tambahkan bot Anda sebagai <strong>Administrator</strong>.</li>
                    <li class="mb-2">Untuk mendapatkan <strong>Chat ID</strong>, forward pesan atau tambahkan bot <strong>@userinfobot</strong> ke dalam chat/grup.</li>
                    <li class="mb-0">Masukkan token & chat ID di samping, lalu klik <strong>Test Kirim Sekarang</strong>.</li>
                </ol>
            </div>
        </div>

        <div class="card card-flush shadow-sm py-4">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold text-gray-800 fs-5">
                        <i class="ki-outline ki-eye fs-4 me-2 text-primary"></i> Contoh Tampilan Notifikasi
                    </h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <!-- Preview Disconnect -->
                <div class="p-4 rounded bg-light-danger border border-danger border-opacity-25 mb-4 font-monospace fs-8 text-gray-800">
                    <div class="fw-bold text-danger mb-1">🔴 PERINGATAN: KONEKSI WHATSAPP TERPUTUS!</div>
                    <div class="text-muted">━━━━━━━━━━━━━━━━━━━━━</div>
                    <div>📱 <b>Sesi Perangkat:</b> <code>{{ \App\Models\Setting::get('gowa_device_id', 'crm-session') }}</code></div>
                    <div>🌐 <b>Gateway:</b> <code>{{ \App\Models\Setting::get('gowa_api_url', 'https://wag.nams.my.id') }}</code></div>
                    <div>⚠️ <b>Status:</b> <b>TERPUTUS (Disconnected)</b></div>
                    <div>🕒 <b>Waktu:</b> <code>{{ now()->translatedFormat('d M Y, H:i') }} WIB</code></div>
                </div>

                <!-- Preview Connect -->
                <div class="p-4 rounded bg-light-success border border-success border-opacity-25 font-monospace fs-8 text-gray-800">
                    <div class="fw-bold text-success mb-1">🟢 INFORMASI: WHATSAPP TERHUBUNG KEMBALI</div>
                    <div class="text-muted">━━━━━━━━━━━━━━━━━━━━━</div>
                    <div>📱 <b>Sesi Perangkat:</b> <code>{{ \App\Models\Setting::get('gowa_device_id', 'crm-session') }}</code></div>
                    <div>🌐 <b>Gateway:</b> <code>{{ \App\Models\Setting::get('gowa_api_url', 'https://wag.nams.my.id') }}</code></div>
                    <div>✅ <b>Status:</b> <b>TERHUBUNG & AKTIF</b></div>
                    <div>🕒 <b>Waktu:</b> <code>{{ now()->translatedFormat('d M Y, H:i') }} WIB</code></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTest = document.getElementById('btn-test-wa-telegram');
    if (btnTest) {
        btnTest.addEventListener('click', function(e) {
            e.preventDefault();
            const originalHtml = btnTest.innerHTML;
            
            const token = document.getElementById('telegram_wa_bot_token')?.value || '';
            const chatId = document.getElementById('telegram_wa_chat_id')?.value || '';

            btnTest.disabled = true;
            btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...';

            fetch("{{ route('settings.whatsapp.test.telegram') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    chat_id: chatId
                })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: d.message || 'Pesan notifikasi berhasil dikirim ke Telegram.',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal Mengirim!',
                        text: d.message || 'Gagal mengirim pesan ke Telegram. Periksa kembali Bot Token dan Chat ID.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem saat menghubungi server.',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' }
                });
            })
            .finally(() => {
                btnTest.disabled = false;
                btnTest.innerHTML = originalHtml;
            });
        });
    }
});
</script>
@endpush

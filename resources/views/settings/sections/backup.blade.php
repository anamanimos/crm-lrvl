<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <h2>Backup Database ke Telegram</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
            <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
            <div class="d-flex flex-stack flex-grow-1">
                <div class="fw-semibold">
                    <div class="fs-6 text-gray-700">Fitur ini akan mengirimkan file backup database (.sql) secara otomatis setiap hari ke grup atau chat Telegram yang ditentukan.</div>
                </div>
            </div>
        </div>

        <div class="row g-9 mb-7">
            <div class="col-md-12">
                <div class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" name="backup_enabled" value="1" id="backup_enabled" {{ \App\Models\Setting::get('backup_enabled') ? 'checked' : '' }} />
                    <label class="form-check-label fw-bold text-gray-700" for="backup_enabled">
                        Aktifkan Backup Harian Otomatis
                    </label>
                </div>
            </div>
        </div>

        <div class="row g-9 mb-7">
            <div class="col-md-6">
                <label class="fs-6 fw-semibold mb-2">Telegram Bot Token</label>
                <input type="password" name="telegram_bot_token" id="telegram_bot_token" class="form-control form-control-solid" value="{{ \App\Models\Setting::get('telegram_bot_token') }}" placeholder="123456789:ABCDefgh..." />
                <div class="text-muted fs-7 mt-2">Token didapat dari @BotFather.</div>
            </div>
            <div class="col-md-6">
                <label class="fs-6 fw-semibold mb-2">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" id="telegram_chat_id" class="form-control form-control-solid" value="{{ \App\Models\Setting::get('telegram_chat_id') }}" placeholder="-100123456789" />
                <div class="text-muted fs-7 mt-2">ID grup atau chat tujuan. Gunakan @userinfobot untuk cari ID Anda.</div>
            </div>
        </div>

        <div class="row g-9 mb-7">
            <div class="col-md-6">
                <label class="fs-6 fw-semibold mb-2">Waktu Backup (HH:mm)</label>
                <input type="time" name="backup_time" class="form-control form-control-solid" value="{{ \App\Models\Setting::get('backup_time', '01:00') }}" />
                <div class="text-muted fs-7 mt-2">Waktu server saat proses backup dijalankan.</div>
            </div>
        </div>

        <div class="separator separator-dashed my-10"></div>

        <div class="d-flex flex-stack">
            <div class="me-5">
                <label class="fs-6 fw-semibold">Test Kirim Backup</label>
                <div class="fs-7 fw-semibold text-muted">Klik untuk mencoba mengirim backup manual sekarang ke Telegram.</div>
            </div>
            <button type="button" class="btn btn-light-primary btn-sm" id="btn-test-backup">
                <i class="ki-outline ki-send fs-3"></i> Test Sekarang
            </button>
        </div>
    </div>
</div>

@push('js')
<script>
document.getElementById('btn-test-backup')?.addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Sistem akan memproses dump database dan mengirimnya ke Telegram. Lanjutkan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
            
            fetch("{{ route('settings.backup.test') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: document.getElementById('telegram_bot_token').value,
                    chat_id: document.getElementById('telegram_chat_id').value
                })
            })
            .then(r => r.json())
            .then(d => {
                Swal.fire(d.success ? 'Berhasil!' : 'Gagal!', d.message, d.success ? 'success' : 'error');
            })
            .catch(() => {
                Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        }
    });
});
</script>
@endpush

<div class="row g-5">
    <div class="col-lg-6">
        <div class="card card-flush shadow-sm border-0 mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <div class="card-title"><h2>Keamanan Broadcast</h2></div>
            </div>
            <div class="card-body pt-0">
                <div class="row mt-5">
                    <div class="col-md-6 mb-5 fv-row">
                        <label class="form-label fw-bold">Delay Min (detik)</label>
                        <input type="number" name="broadcast_delay_min" class="form-control" value="{{ $settings['broadcast_delay_min'] ?? '5' }}">
                        <div class="form-text">Jeda minimal antar pesan.</div>
                    </div>
                    <div class="col-md-6 mb-5 fv-row">
                        <label class="form-label fw-bold">Delay Max (detik)</label>
                        <input type="number" name="broadcast_delay_max" class="form-control" value="{{ $settings['broadcast_delay_max'] ?? '15' }}">
                        <div class="form-text">Jeda maksimal antar pesan.</div>
                    </div>
                    <div class="col-md-6 mb-5 fv-row">
                        <label class="form-label fw-bold">Max/Jam</label>
                        <input type="number" name="broadcast_max_per_hour" class="form-control" value="{{ $settings['broadcast_max_per_hour'] ?? '30' }}">
                        <div class="form-text">Batas pesan kirim per jam.</div>
                    </div>
                    <div class="col-md-6 mb-5 fv-row">
                        <label class="form-label fw-bold">Max/Hari</label>
                        <input type="number" name="broadcast_max_per_day" class="form-control" value="{{ $settings['broadcast_max_per_day'] ?? '200' }}">
                        <div class="form-text">Batas pesan kirim per hari.</div>
                    </div>
                </div>
                <div class="notice bg-light-warning rounded border-warning border border-dashed p-4">
                    <div class="d-flex">
                        <i class="ki-outline ki-shield-cross fs-2tx text-warning me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold fs-7 text-gray-700 text-start">Penting: Gunakan jeda waktu yang cukup lama (disarankan minimal 10-30 detik) untuk menghindari deteksi spam dan pemblokiran akun oleh WhatsApp.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-flush shadow-sm border-0 border-info border-dashed rounded bg-light-info h-lg-100">
            <div class="card-body">
                <h2 class="mb-5 text-info"><i class="ki-outline ki-information-4 text-info fs-2 me-2"></i> Detail Penjelasan</h2>
                
                <div class="mb-5">
                    <h4 class="fw-bold text-gray-800">Randomized Delay</h4>
                    <p class="fs-6 text-gray-700">Sistem menggunakan jeda acak antara nilai <strong>Delay Min</strong> dan <strong>Delay Max</strong> di setiap pengiriman pesan. Hal ini bertujuan untuk mensimulasikan perilaku manusia saat mengetik dan mengirim pesan, sehingga mengurangi risiko akun ditandai sebagai bot.</p>
                </div>

                <div class="mb-5">
                    <h4 class="fw-bold text-gray-800">Rate Limiting</h4>
                    <p class="fs-6 text-gray-700"><strong>Max per Jam/Hari</strong> adalah batasan keamanan untuk mengontrol volume pesan yang keluar dari satu nomor. Jika batas ini tercapai, pengiriman pesan broadcast akan ditunda secara otomatis hingga jendela waktu berikutnya terbuka.</p>
                </div>

                <div class="mb-0">
                    <h4 class="fw-bold text-gray-800">Rekomendasi Aman</h4>
                    <ul class="fs-6 text-gray-700">
                        <li>Akun Baru: Delay 30-60 detik, Max 50/hari.</li>
                        <li>Akun Lama: Delay 10-20 detik, Max 200-500/hari.</li>
                        <li>Gunakan variabel nama pelanggan di setiap pesan agar isi pesan tidak identik 100%.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

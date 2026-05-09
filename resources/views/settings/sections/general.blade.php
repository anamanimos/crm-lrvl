<div class="card card-flush py-4">
    <div class="card-header"><div class="card-title"><h2>Informasi Umum</h2></div></div>
    <div class="card-body pt-0">
        <div class="mb-10 fv-row">
            <label class="required form-label">Nama Perusahaan</label>
            <input type="text" name="company_name" class="form-control mb-2" value="{{ $settings['company_name'] ?? '' }}" />
        </div>
        <div class="mb-10 fv-row">
            <label class="form-label">No. WhatsApp Bisnis</label>
            <input type="text" name="company_phone" class="form-control mb-2" placeholder="628xxxx" value="{{ $settings['company_phone'] ?? '' }}" />
        </div>
    </div>
</div>

<div class="card card-flush py-4 mt-5">
    <div class="card-header"><div class="card-title"><h2>Pengaturan Jam Kerja</h2></div></div>
    <div class="card-body pt-0">
        <div class="mb-10 fv-row">
            <label class="form-label d-block mb-3">Hari Kerja Aktif</label>
            @php
                $activeDays = json_decode($settings['business_hours_days'] ?? '[]', true) ?: ['1','2','3','4','5','6'];
                $days = [
                    1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
                    5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
                ];
            @endphp
            <div class="d-flex flex-wrap gap-5">
                @foreach($days as $val => $label)
                <label class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" name="business_hours_days[]" value="{{ $val }}" {{ in_array($val, $activeDays) ? 'checked' : '' }} />
                    <span class="form-check-label">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            <div class="text-muted fs-7 mt-2">Pilih hari-hari dimana CS aktif membalas chat. Laporan Missed Chat dan FRT hanya akan dihitung pada hari-hari ini.</div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 mb-10">
            <div class="col fv-row">
                <label class="required form-label">Jam Mulai (Start)</label>
                <input type="time" name="business_hours_start" class="form-control mb-2" value="{{ $settings['business_hours_start'] ?? '07:30' }}" />
            </div>
            <div class="col fv-row">
                <label class="required form-label">Jam Selesai (End)</label>
                <input type="time" name="business_hours_end" class="form-control mb-2" value="{{ $settings['business_hours_end'] ?? '16:30' }}" />
            </div>
        </div>
    </div>
</div>

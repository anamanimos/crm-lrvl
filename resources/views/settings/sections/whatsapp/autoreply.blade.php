<div class="row g-5">
    <div class="col-12">
        <div class="card card-flush shadow-sm border-0 mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <div class="card-title"><h2>Auto Reply</h2></div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5 mt-5 fv-row">
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="auto_reply_enabled" value="1"
                                {{ ($settings['auto_reply_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                        <span class="form-check-label fw-bold text-gray-800">Aktifkan Auto Reply Global</span>
                    </label>
                </div>
                <div class="notice bg-light-info rounded border-info border border-dashed p-4">
                    <div class="d-flex">
                        <i class="ki-outline ki-messages fs-2tx text-info me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold fs-7 text-gray-700 text-start">
                                Kelola template auto reply pada menu master: <a href="{{ url('admin/auto-replies') }}" class="fw-bold">Auto Reply</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

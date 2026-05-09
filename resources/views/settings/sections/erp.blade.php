<div class="row g-5">
    <div class="col-12">
        <div class="card card-flush shadow-sm border-0 mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <div class="card-title"><h2>Konfigurasi ERP</h2></div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-warning" id="btn-test-erp">
                        <i class="ki-outline ki-check-circle fs-4"></i> Test Koneksi ERP
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5 mt-5 fv-row">
                    <label class="form-label fw-bold">API URL ERP</label>
                    <input type="text" name="erp_api_url" id="erp_api_url" class="form-control" 
                            placeholder="https://app.damaijaya.my.id/api"
                            value="{{ $settings['erp_api_url'] ?? '' }}">
                </div>
                <div class="mb-5 fv-row">
                    <label class="form-label fw-bold">API Key ERP</label>
                    <input type="password" name="erp_api_key" id="erp_api_key" class="form-control" 
                            placeholder="Masukkan API Key dari ERP"
                            value="{{ $settings['erp_api_key'] ?? '' }}">
                    <div class="form-text">API Key untuk autentikasi dengan ERP API (header: X-API-Key)</div>
                </div>
            </div>
        </div>
    </div>
</div>

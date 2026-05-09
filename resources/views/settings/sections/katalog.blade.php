<div class="card card-flush py-4">
    <div class="card-header"><div class="card-title"><h2>Integrasi Katalog Produk</h2></div></div>
    <div class="card-body pt-0">
        <div class="mb-10 fv-row">
            <label class="required form-label">Catalogue API URL</label>
            <input type="text" name="catalogue_api_url" class="form-control mb-2" value="{{ $settings['catalogue_api_url'] ?? '' }}" />
        </div>
        <div class="mb-10 fv-row">
            <label class="form-label">Catalogue API Key</label>
            <input type="password" name="catalogue_api_key" class="form-control mb-2" value="{{ $settings['catalogue_api_key'] ?? '' }}" />
        </div>
    </div>
</div>

<div class="row g-5">
    <div class="col-12">
        <div class="card card-flush shadow-sm border-0 mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <div class="card-title"><h2>Pengaturan Gateway</h2></div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5 mt-5 fv-row">
                    <label class="form-label fw-bold">API URL</label>
                    <input type="text" name="gowa_api_url" class="form-control" 
                           placeholder="https://wag.anam.ch" 
                           value="{{ $settings['gowa_api_url'] ?? 'https://wag.anam.ch' }}">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-5 fv-row">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="gowa_username" class="form-control" 
                               placeholder="Username admin Go-WA"
                               value="{{ $settings['gowa_username'] ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-5 fv-row">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="gowa_password" class="form-control" 
                               placeholder="Password"
                               value="{{ $settings['gowa_password'] ?? '' }}">
                    </div>
                </div>
                <div class="mb-5 fv-row">
                    <label class="form-label fw-bold">Webhook Endpoint <span class="badge badge-light-primary">Read-only</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" id="webhook_url" value="{{ url('webhook/wa') }}" readonly>
                        <button class="btn btn-light-primary" type="button" onclick="window.copyToClipboard(document.getElementById('webhook_url').value)">
                            <i class="ki-outline ki-copy fs-4"></i>
                        </button>
                    </div>
                    <div class="form-text">Set callback URL ini di aplikasi Go-WA Gateway Anda untuk menerima pesan masuk.</div>
                </div>

                <div class="separator separator-dashed my-5"></div>
                
                <div class="mb-5 fv-row">
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="webhook_forward_enabled" value="1" {{ ($settings['webhook_forward_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                        <span class="form-check-label fw-bold text-gray-800">Aktifkan Webhook Forwarder (Multiplexer)</span>
                    </label>
                    <div class="form-text mt-2">Gunakan ini jika Anda ingin menyalin data webhook ke Localhost / Development server.</div>
                </div>

                @if (($settings['webhook_forward_enabled'] ?? '0') == '1')
                <div class="mb-5 fv-row" id="forwarder_config">
                    <label class="form-label fw-bold">Forward URL</label>
                    <input type="text" name="webhook_forward_url" class="form-control" 
                           placeholder="https://your-localhost.ngrok.io/webhook"
                           value="{{ $settings['webhook_forward_url'] ?? '' }}">
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

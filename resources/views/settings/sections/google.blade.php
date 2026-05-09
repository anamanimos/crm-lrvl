<div class="row g-5">
    <div class="col-12">
        <div class="card card-flush shadow-sm mb-5">
            <div class="card-header pt-5">
                <div class="card-title"><h2>Google API Credentials</h2></div>
            </div>
            <div class="card-body pt-0">
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-7">
                    <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Petunjuk Pengaturan</h4>
                            <div class="fs-6 text-gray-700">
                                Gunakan Redirect URI: <code class="bg-light px-1">{{ url('integrasi/google/callback') }}</code> pada Google Cloud Console Anda.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-5">
                    <label class="form-check form-switch form-check-custom form-check-solid mb-5">
                        <input class="form-check-input" type="checkbox" name="google_sync_enabled" value="1" id="google_sync_enabled" {{ ($settings['google_sync_enabled'] ?? '0') == '1' ? 'checked' : '' }} />
                        <span class="form-check-label fw-bold text-gray-800" for="google_sync_enabled">Aktifkan Sinkronisasi Google</span>
                    </label>
                </div>

                <div class="mb-5 fv-row">
                    <label class="form-label fw-bold">Client ID</label>
                    <input type="text" name="google_client_id" class="form-control" 
                           placeholder="Google OAuth Client ID"
                           value="{{ $settings['google_client_id'] ?? '' }}">
                </div>
                <div class="mb-5 fv-row">
                    <label class="form-label fw-bold">Client Secret</label>
                    <input type="password" name="google_client_secret" class="form-control" 
                           placeholder="Google OAuth Client Secret"
                           value="{{ $settings['google_client_secret'] ?? '' }}">
                </div>
            </div>
        </div>

        @if (!empty($settings['google_client_id']))
        <div class="card card-flush shadow-sm">
            <div class="card-header pt-5">
                <div class="card-title"><h2>Status Koneksi</h2></div>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex flex-stack">
                    <div class="me-5">
                        <label class="fs-6 fw-bold">Akun Google</label>
                        <div class="fs-7 fw-semibold text-muted">Hubungkan akun Google Anda untuk memulai sinkronisasi kontak.</div>
                    </div>
                    <div class="d-flex">
                        @if (empty($settings['google_token']))
                            <a href="{{ url('integrasi/google') }}" class="btn btn-light-primary btn-sm">
                                <i class="ki-outline ki-google fs-4"></i> Hubungkan Akun Google
                            </a>
                        @else
                            <span class="badge badge-light-success fs-7 fw-bold me-2 px-4 py-3">Terhubung</span>
                            <a href="{{ url('integrasi/google/disconnect') }}" class="btn btn-light-danger btn-sm">
                                <i class="ki-outline ki-exit-right fs-4"></i> Putuskan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

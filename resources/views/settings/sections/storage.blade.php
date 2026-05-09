<div class="row g-5">
    <!-- Cloudinary -->
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <div class="card-title"><h2>Cloudinary</h2></div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-primary" id="btn-test-cloudinary">
                        <i class="ki-outline ki-check-circle fs-4"></i> Test
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5">
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="cloudinary_enabled" value="1"
                               {{ ($settings['cloudinary_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                        <span class="form-check-label fw-semibold text-muted">Aktifkan Cloudinary</span>
                    </label>
                </div>
                <div class="mb-5">
                    <label class="form-label">Cloud Name</label>
                    <input type="text" name="cloudinary_cloud_name" id="cloudinary_cloud_name" class="form-control" 
                           value="{{ $settings['cloudinary_cloud_name'] ?? '' }}">
                </div>
                <div class="mb-5">
                    <label class="form-label">API Key</label>
                    <input type="text" name="cloudinary_api_key" id="cloudinary_api_key" class="form-control" 
                           value="{{ $settings['cloudinary_api_key'] ?? '' }}">
                </div>
                <div class="mb-5">
                    <label class="form-label">API Secret</label>
                    <input type="password" name="cloudinary_api_secret" id="cloudinary_api_secret" class="form-control" 
                           value="{{ $settings['cloudinary_api_secret'] ?? '' }}">
                </div>
                <div class="mb-5">
                    <label class="form-label">Default Folder</label>
                    <input type="text" name="cloudinary_folder" class="form-control" placeholder="crm"
                           value="{{ $settings['cloudinary_folder'] ?? 'crm' }}">
                </div>
                <div class="separator separator-dashed my-5"></div>
                <div class="mb-5">
                    <label class="form-label fw-bold">Test Upload & Download</label>
                    <input type="file" class="form-control mb-3" id="cloudinary_test_file" accept="image/*">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-upload-cloudinary">
                            <i class="ki-outline ki-cloud-add fs-4"></i> Upload Test
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="btn-download-cloudinary" style="display:none;">
                            <i class="ki-outline ki-cloud-download fs-4"></i> Download
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="btn-delete-cloudinary" style="display:none;">
                            <i class="ki-outline ki-trash fs-4"></i> Hapus
                        </button>
                    </div>
                    <div id="cloudinary_test_result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- MinIO -->
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <div class="card-title"><h2>MinIO</h2></div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-success" id="btn-test-minio">
                        <i class="ki-outline ki-check-circle fs-4"></i> Test
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5">
                    <label class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="minio_enabled" value="1"
                               {{ ($settings['minio_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                        <span class="form-check-label fw-semibold text-muted">Aktifkan MinIO</span>
                    </label>
                </div>
                <div class="mb-5">
                    <label class="form-label">Endpoint URL</label>
                    <input type="text" name="minio_endpoint" id="minio_endpoint" class="form-control" 
                           placeholder="https://minio.example.com"
                           value="{{ $settings['minio_endpoint'] ?? '' }}">
                </div>
                <div class="mb-5">
                    <label class="form-label">Access Key</label>
                    <input type="text" name="minio_access_key" id="minio_access_key" class="form-control" 
                           value="{{ $settings['minio_access_key'] ?? '' }}">
                </div>
                <div class="mb-5">
                    <label class="form-label">Secret Key</label>
                    <input type="password" name="minio_secret_key" id="minio_secret_key" class="form-control" 
                           value="{{ $settings['minio_secret_key'] ?? '' }}">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Bucket Name</label>
                        <input type="text" name="minio_bucket" id="minio_bucket" class="form-control" placeholder="crm"
                               value="{{ $settings['minio_bucket'] ?? 'crm' }}">
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Region</label>
                        <input type="text" name="minio_region" id="minio_region" class="form-control" placeholder="us-east-1"
                               value="{{ $settings['minio_region'] ?? 'us-east-1' }}">
                    </div>
                </div>
                <div class="separator separator-dashed my-5"></div>
                <div class="mb-5">
                    <label class="form-label fw-bold">Test Upload & Download</label>
                    <input type="file" class="form-control mb-3" id="minio_test_file" accept="image/*">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-success" id="btn-upload-minio">
                            <i class="ki-outline ki-cloud-add fs-4"></i> Upload Test
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="btn-download-minio" style="display:none;">
                            <i class="ki-outline ki-cloud-download fs-4"></i> Download
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="btn-delete-minio" style="display:none;">
                            <i class="ki-outline ki-trash fs-4"></i> Hapus
                        </button>
                    </div>
                    <div id="minio_test_result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

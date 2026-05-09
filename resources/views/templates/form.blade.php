<x-metronic-layout>
    @php
        $isEdit = isset($template);
        $title = ($isEdit ? 'Edit' : 'Tambah') . ' Template Chat';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $title }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.templates.index') }}" class="text-muted text-hover-primary">Template</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ $isEdit ? 'Edit' : 'Tambah' }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <div class="row g-5 g-xl-10">
                <!--begin::Col - Form (9 Columns)-->
                <div class="col-lg-9">
                    <div class="card shadow-sm h-100">
                        <form action="{{ $isEdit ? route('admin.templates.update', $template->id) : route('admin.templates.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row g-9 mb-8">
                                    <div class="col-md-4 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Judul Template</label>
                                        <input type="text" id="input-title" class="form-control form-control-solid @error('title') is-invalid @enderror" 
                                               placeholder="Contoh: Greeting Pagi" name="title" value="{{ old('title', $template->title ?? '') }}" required />
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Shortcut (Tanpa /)</label>
                                        <div class="input-group input-group-solid">
                                            <span class="input-group-text">/</span>
                                            <input type="text" id="input-shortcut" class="form-control form-control-solid @error('shortcut') is-invalid @enderror" 
                                                   placeholder="halo" name="shortcut" value="{{ old('shortcut', $template->shortcut ?? '') }}" />
                                        </div>
                                        @error('shortcut')
                                            <div class="invalid-feedback text-danger fs-7">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Kategori</label>
                                        <select class="form-select form-select-solid" name="category" data-control="select2" data-placeholder="Pilih Kategori">
                                            <option value="">Tanpa Kategori</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->name }}" {{ old('category', $template->category ?? '') == $cat->name ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex flex-column mb-8">
                                    <label class="required fs-6 fw-semibold mb-2">Konten Pesan</label>
                                    <textarea id="input-content" class="form-control form-control-solid @error('content') is-invalid @enderror" 
                                              rows="10" name="content" placeholder="Tulis pesan template di sini..." required>{{ old('content', $template->content ?? '') }}</textarea>
                                    <div class="text-muted fs-7 mt-2">
                                        Gunakan variabel seperti <code>{name}</code>, <code>{company}</code> untuk personalisasi.
                                    </div>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="fv-row mb-8">
                                    <label class="fs-6 fw-semibold mb-2">Lampiran (Gambar/Dokumen)</label>
                                    <div class="d-flex flex-column">
                                        <input type="file" id="input-media" name="media_file" class="form-control form-control-solid" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" />
                                        <div class="text-muted fs-7 mt-2">Maksimal 5MB. Format: Image, PDF, DOC, XLS.</div>
                                        
                                        <!-- Real-time Input Preview Area -->
                                        <div id="input-preview-area" class="mt-4 d-none">
                                            <div class="position-relative d-inline-block">
                                                <img id="input-preview-image" src="" class="img-thumbnail w-150px h-150px object-fit-cover d-none" />
                                                <div id="input-preview-doc" class="d-none border rounded p-3 bg-light d-flex align-items-center w-250px">
                                                    <i class="ki-outline ki-file fs-2x text-primary me-3"></i>
                                                    <span id="input-preview-doc-name" class="fs-7 fw-bold text-truncate"></span>
                                                </div>
                                                <!-- Cancel Selection Button -->
                                                <button type="button" id="btn-cancel-selection" class="btn btn-icon btn-sm btn-danger position-absolute top-0 end-0 translate-middle" title="Batalkan Pilihan">
                                                    <i class="ki-outline ki-cross fs-2"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Hidden field to flag media removal -->
                                        <input type="hidden" name="remove_media" id="remove-media-flag" value="0" />

                                        @if(isset($template) && $template->media_path)
                                            <div id="existing-media-info" class="mt-4 p-4 border border-dashed border-danger rounded d-flex align-items-center bg-light-danger position-relative">
                                                @php
                                                    $mediaUrl = Str::startsWith($template->media_path, ['http://', 'https://']) 
                                                        ? $template->media_path 
                                                        : asset('storage/' . $template->media_path);
                                                @endphp
                                                @if($template->media_type == 'image')
                                                    <div class="symbol symbol-50px me-4">
                                                        <img src="{{ $mediaUrl }}" alt="Preview" class="object-fit-cover shadow-sm rounded" />
                                                    </div>
                                                @else
                                                    <div class="symbol symbol-50px me-4">
                                                        <div class="symbol-label bg-white">
                                                            <i class="ki-outline ki-file fs-2x text-danger"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="d-flex flex-column flex-grow-1">
                                                    <span class="text-gray-800 fw-bold fs-6">Lampiran Saat Ini</span>
                                                    <a href="{{ $mediaUrl }}" target="_blank" class="text-danger fs-7 fw-bold">Lihat File</a>
                                                </div>
                                                
                                                <!-- Remove Button -->
                                                <button type="button" id="btn-remove-media" class="btn btn-danger btn-sm" title="Hapus Lampiran">
                                                    <i class="ki-outline ki-trash fs-2 me-1"></i> Hapus
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex flex-stack">
                                    <div class="me-5">
                                        <label class="fs-6 fw-semibold">Status Aktif</label>
                                        <div class="fs-7 fw-semibold text-muted">Template dapat digunakan saat membuat pesan</div>
                                    </div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                               {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }} />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <a href="{{ route('admin.templates.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2 me-1"></i>
                                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Template' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Preview (3 Columns)-->
                <div class="col-lg-3">
                    <div class="sticky-lg-top" style="top: 100px; z-index: 100">
                        <div class="card shadow-sm border-0" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-size: 400px;">
                            <div class="card-header border-0 bg-success py-4 min-h-auto">
                                <h3 id="preview-header-title" class="card-title text-white fs-6 fw-bold text-truncate">{{ old('title', $template->title ?? 'Pratinjau Pesan') }}</h3>
                            </div>
                            <div class="card-body p-4">
                                <!-- WhatsApp Bubble -->
                                <div class="d-flex flex-column gap-3">
                                    <div class="bg-white p-3 rounded-3 shadow-sm position-relative" style="max-width: 100%;">
                                        <!-- Media Preview -->
                                        <div id="preview-media-container" class="mb-3 d-none">
                                            <img id="preview-image" src="" class="img-fluid rounded-2 w-100 d-none" style="max-height: 250px; object-fit: cover;">
                                            <div id="preview-doc" class="p-3 bg-light rounded-2 d-none d-flex align-items-center border">
                                                <i class="ki-outline ki-file fs-1 text-primary me-2"></i>
                                                <span id="preview-doc-name" class="fs-7 fw-semibold text-truncate">document.pdf</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Message Content -->
                                        <div id="preview-content" class="fs-7 text-gray-800 lh-base" style="white-space: pre-wrap;">{{ old('content', $template->content ?? 'Tulis pesan di sini...') }}</div>
                                        
                                        <!-- Timestamp Style -->
                                        <div class="text-end mt-2">
                                            <span class="fs-9 text-muted" style="font-size: 0.65rem !important;">{{ date('H:i') }} <i class="ki-outline ki-double-check text-primary fs-9 ms-1"></i></span>
                                        </div>

                                        <!-- Bubble Tail -->
                                        <div class="position-absolute start-0 top-0 translate-middle-x mt-4" style="width: 0; height: 0; border-top: 12px solid white; border-left: 12px solid transparent; transform: scaleX(-1);"></div>
                                    </div>

                                    <!-- Shortcut Preview -->
                                    <div class="text-center">
                                        <span class="badge badge-light-dark fs-8 px-3 py-2">Shortcut: /<span id="preview-shortcut">{{ old('shortcut', $template->shortcut ?? '...') }}</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>

        </div>
    </div>
    <script>
        (function() {
            console.log('Template Form Script Initialized...');
            
            function initPreview() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(initPreview, 100);
                    return;
                }
                
                const $ = jQuery;
                console.log('Template Form JS Ready (Polling Success)');

                const inputTitle = $('#input-title');
                const inputShortcut = $('#input-shortcut');
                const inputContent = $('#input-content');
                const inputMedia = $('#input-media');

                // Bubble Preview Elements
                const previewHeaderTitle = $('#preview-header-title');
                const previewContent = $('#preview-content');
                const previewShortcut = $('#preview-shortcut');
                const previewMediaContainer = $('#preview-media-container');
                const previewImage = $('#preview-image');
                const previewDoc = $('#preview-doc');
                const previewDocName = $('#preview-doc-name');

                // Form Preview Elements
                const inputPreviewArea = $('#input-preview-area');
                const inputPreviewImage = $('#input-preview-image');
                const inputPreviewDoc = $('#input-preview-doc');
                const inputPreviewDocName = $('#input-preview-doc-name');
                const existingMediaInfo = $('#existing-media-info');

                function updateContentPreview(text) {
                    if (!text || text.trim() === '') {
                        previewContent.text("Tulis pesan di sini...");
                        return;
                    }
                    
                    let simulated = text
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;")
                        .replace(/{name}/g, '<span class="text-primary fw-bold">Budi Santoso</span>')
                        .replace(/{company}/g, '<span class="text-primary fw-bold">PT Maju Jaya</span>');
                    
                    previewContent.html(simulated);
                }

                // 1. Title Update
                inputTitle.on('input keyup change', function() {
                    const val = $(this).val();
                    previewHeaderTitle.text(val.trim() === '' ? "Pratinjau Pesan" : val);
                });

                // 2. Content Update
                inputContent.on('input keyup change', function() {
                    updateContentPreview($(this).val());
                });

                // 3. Shortcut Update
                inputShortcut.on('input keyup change', function() {
                    const val = $(this).val();
                    previewShortcut.text(val === '' ? '...' : val);
                });

                // 4. Media Update
                inputMedia.on('change', function() {
                    const file = this.files[0];
                    if (file) {
                        $('#remove-media-flag').val('0'); // Reset flag if new file selected
                        if(existingMediaInfo.length) existingMediaInfo.addClass('d-none');
                        inputPreviewArea.removeClass('d-none');
                        previewMediaContainer.removeClass('d-none');
                        
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImage.attr('src', e.target.result).removeClass('d-none');
                                previewDoc.addClass('d-none');
                                inputPreviewImage.attr('src', e.target.result).removeClass('d-none');
                                inputPreviewDoc.addClass('d-none');
                            }
                            reader.readAsDataURL(file);
                        } else {
                            previewImage.addClass('d-none');
                            previewDoc.removeClass('d-none');
                            previewDocName.text(file.name);
                            inputPreviewImage.addClass('d-none');
                            inputPreviewDoc.removeClass('d-none');
                            inputPreviewDocName.text(file.name);
                        }
                    } else {
                        inputPreviewArea.addClass('d-none');
                        if(existingMediaInfo.length && $('#remove-media-flag').val() === '0') {
                            existingMediaInfo.removeClass('d-none');
                        } else {
                            previewMediaContainer.addClass('d-none');
                        }
                    }
                });

                // 5. Remove Existing Media
                $('#btn-remove-media').on('click', function() {
                    if(confirm('Hapus lampiran ini?')) {
                        $('#remove-media-flag').val('1');
                        existingMediaInfo.addClass('d-none');
                        previewMediaContainer.addClass('d-none');
                        inputMedia.val(''); // Clear file input
                        inputPreviewArea.addClass('d-none');
                        console.log('Media marked for removal');
                    }
                });

                // 6. Cancel New Selection
                $('#btn-cancel-selection').on('click', function() {
                    inputMedia.val(''); // Reset input file
                    inputPreviewArea.addClass('d-none');
                    
                    // Restore existing media preview if it exists and wasn't removed
                    if(existingMediaInfo.length && $('#remove-media-flag').val() === '0') {
                        existingMediaInfo.removeClass('d-none');
                        previewMediaContainer.removeClass('d-none');
                    } else {
                        previewMediaContainer.addClass('d-none');
                    }
                    console.log('File selection cancelled');
                });

                // --- Form Submission (AJAX) ---
                const form = $('form');
                form.on('submit', function(e) {
                    e.preventDefault();
                    
                    const btnSubmit = form.find('button[type="submit"]');
                    const originalHtml = btnSubmit.html();
                    
                    // Show Loading
                    btnSubmit.prop('disabled', true);
                    btnSubmit.html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');
                    
                    const formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json().then(data => ({ status: response.status, ok: response.ok, body: data })))
                    .then(res => {
                        if (res.ok) {
                            Swal.fire({
                                text: res.body.message || "Berhasil menyimpan data",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, mengerti!",
                                customClass: { confirmButton: "btn btn-primary" }
                            }).then(() => {
                                if (res.body.redirect) window.location.href = res.body.redirect;
                                else window.location.reload();
                            });
                        } else {
                            // Handle Validation Errors
                            let errorMsg = res.body.message || "Terjadi kesalahan saat menyimpan data.";
                            if (res.status === 422 && res.body.errors) {
                                errorMsg = Object.values(res.body.errors).flat().join('<br>');
                            }
                            
                            Swal.fire({
                                title: 'Gagal!',
                                html: errorMsg,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Tutup",
                                customClass: { confirmButton: "btn btn-danger" }
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            text: "Terjadi kesalahan koneksi atau server.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Tutup",
                            customClass: { confirmButton: "btn btn-danger" }
                        });
                    })
                    .finally(() => {
                        btnSubmit.prop('disabled', false);
                        btnSubmit.html(originalHtml);
                    });
                });

                // Initial Trigger
                if(inputContent.val()) updateContentPreview(inputContent.val());
            }

            initPreview();
        })();
    </script>
</x-metronic-layout>

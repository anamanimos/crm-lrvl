<x-metronic-layout>
    @php
        $isEdit = isset($reply);
        $title = ($isEdit ? 'Edit' : 'Tambah') . ' Auto Reply';
        $activeDays = old('active_days', $reply->active_days ?? ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
        $activeTimes = old('active_times', $reply->active_times ?? [['start' => '00:00', 'end' => '23:59']]);
        
        $days = [
            'mon' => 'Senin',
            'tue' => 'Selasa',
            'wed' => 'Rabu',
            'thu' => 'Kamis',
            'fri' => 'Jumat',
            'sat' => 'Sabtu',
            'sun' => 'Minggu'
        ];
    @endphp

    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ $title }}
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.auto-replies.index') }}" class="text-muted text-hover-primary">Auto Reply</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">{{ $isEdit ? 'Edit' : 'Tambah' }}</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.auto-replies.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ki-outline ki-arrow-left fs-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <form action="{{ $isEdit ? route('admin.auto-replies.update', $reply->id) : route('admin.auto-replies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-5">
                        <!--begin::Main Column-->
                        <div class="col-lg-8">
                            <div class="card card-flush mb-5">
                                <div class="card-header pt-7">
                                    <h3 class="card-title fw-bold">Konfigurasi Aturan & Konten</h3>
                                </div>
                                <div class="card-body">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Nama Aturan</label>
                                        <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror" 
                                               placeholder="Contoh: Balasan Salam" value="{{ old('name', $reply->name ?? '') }}" required />
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="row mb-7">
                                        <div class="col-md-6 fv-row">
                                            <label class="required fs-6 fw-semibold mb-2">Tipe Trigger</label>
                                            <select name="trigger_type" id="trigger-type" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                                <option value="keyword" {{ old('trigger_type', $reply->trigger_type ?? '') == 'keyword' ? 'selected' : '' }}>Kata Kunci</option>
                                                <option value="first_chat" {{ old('trigger_type', $reply->trigger_type ?? '') == 'first_chat' ? 'selected' : '' }}>Chat Pertama Kali</option>
                                                <option value="all" {{ old('trigger_type', $reply->trigger_type ?? '') == 'all' ? 'selected' : '' }}>Semua Pesan (Default)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 fv-row">
                                            <label class="fs-6 fw-semibold mb-2">Jeda Pengiriman (Detik)</label>
                                            <input type="number" name="delay_seconds" class="form-control form-control-solid" 
                                                   value="{{ old('delay_seconds', $reply->delay_seconds ?? 0) }}" min="0" />
                                        </div>
                                    </div>

                                    <div id="keyword-wrapper" class="fv-row mb-7 {{ old('trigger_type', $reply->trigger_type ?? 'keyword') != 'keyword' ? 'd-none' : '' }}">
                                        <label class="required fs-6 fw-semibold mb-2">Kata Kunci</label>
                                        <input type="text" name="keyword" class="form-control form-control-solid @error('keyword') is-invalid @enderror" 
                                               placeholder="halo, p, assalamualaikum (pisahkan dengan koma)" 
                                               value="{{ old('keyword', $reply->keyword ?? '') }}" />
                                        <div class="text-muted fs-7 mt-2">Sistem akan membalas jika pesan mengandung salah satu kata kunci di atas.</div>
                                        @error('keyword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="separator separator-dashed my-10"></div>

                                    <div class="d-flex flex-stack mb-5">
                                        <h4 class="fw-bold m-0">Konten Balasan</h4>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" id="btn-emoji" title="Emoji">
                                                <i class="fas fa-smile fs-4"></i>
                                            </button>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-icon btn-light-info" data-bs-toggle="dropdown" title="Formatting">
                                                    <i class="ki-outline ki-text-bold fs-3"></i>
                                                </button>
                                                <div class="dropdown-menu p-4 w-200px">
                                                    <div class="d-flex flex-column gap-2">
                                                        <a href="javascript:;" class="format-btn text-gray-800 fs-7" data-format="bold"><b>Bold</b> <code class="float-end text-muted">*text*</code></a>
                                                        <a href="javascript:;" class="format-btn text-gray-800 fs-7" data-format="italic"><i>Italic</i> <code class="float-end text-muted">_text_</code></a>
                                                        <a href="javascript:;" class="format-btn text-gray-800 fs-7" data-format="strike"><strike>Strike</strike> <code class="float-end text-muted">~text~</code></a>
                                                        <a href="javascript:;" class="format-btn text-gray-800 fs-7" data-format="mono"><code>Mono</code> <code class="float-end text-muted">```text```</code></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Emoji Container (Hidden by default) -->
                                    <div id="emoji-container" class="card card-bordered p-4 mb-5 d-none bg-light-dark bg-opacity-5">
                                        <div class="d-flex flex-wrap gap-2 fs-3" id="emoji-list">
                                            @foreach(['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹','👺','🤡','💩','👻','💀','☠️','👽','👾','🤖','🎃','😺','😸','😻','😼','😽','🙀','😿','😾','🤲','👐','🙌','👏','🤝','👍','👎','👊','✊','🤛','🤜','🤞','✌️','🤟','🤘','👌','🤏','👈','👉','👆','👇','✋','🤚','🖐','🖖','👋','🤙','💪','🦾','🖕','✍️','🙏','🦶','🦵','🦿','💄','💋','👄','🦷','👅','👂','🦻','👃','👣','👁','👀','🧠','🗣','👤','👥'] as $emoji)
                                                <span class="emoji-item cursor-pointer hover-scale">{{ $emoji }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="fv-row mb-7">
                                        <textarea name="response_messages[0][content]" id="input-content" class="form-control form-control-solid @error('response_messages.0.content') is-invalid @enderror" 
                                                  rows="8" placeholder="Tulis pesan balasan otomatis di sini..." required>{{ old('response_messages.0.content', $reply->response_messages[0]['content'] ?? '') }}</textarea>
                                        <input type="hidden" name="response_messages[0][type]" value="text">
                                        <div class="text-muted fs-7 mt-2">Gunakan spintax <code>{Halo|Hai}</code> untuk variasi balasan.</div>
                                        @error('response_messages.0.content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="fv-row mb-0">
                                        <label class="fs-6 fw-semibold mb-2">Lampiran (Gambar/Dokumen)</label>
                                        <input type="file" name="media_file" id="input-media" class="form-control form-control-solid" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" />
                                        <div class="text-muted fs-7 mt-2">Maksimal 5MB. Format: Image, PDF, DOC, XLS.</div>
                                        
                                        <input type="hidden" name="remove_media" id="remove-media-flag" value="0" />
                                        @if($isEdit && $reply->media_path)
                                            <div id="existing-media-info" class="mt-4 p-3 border border-dashed border-danger rounded d-flex align-items-center bg-light-danger">
                                                @php
                                                    $mediaUrl = Str::startsWith($reply->media_path, ['http://', 'https://']) ? $reply->media_path : asset('storage/' . $reply->media_path);
                                                @endphp
                                                <div class="symbol symbol-40px me-4">
                                                    @if($reply->media_type == 'image')
                                                        <img src="{{ $mediaUrl }}" class="object-fit-cover" />
                                                    @else
                                                        <div class="symbol-label bg-white"><i class="ki-outline ki-file fs-2 text-danger"></i></div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1"><span class="fs-7 fw-bold text-gray-800">Media Terlampir</span></div>
                                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" id="btn-remove-media"><i class="ki-outline ki-trash fs-3"></i></button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card card-flush">
                                <div class="card-header pt-7">
                                    <h3 class="card-title fw-bold">Jadwal Aktif</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-10">
                                        <label class="fs-6 fw-semibold mb-5">Hari Aktif</label>
                                        <div class="d-flex flex-wrap gap-5">
                                            @foreach($days as $key => $name)
                                                <label class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input h-20px w-20px" type="checkbox" name="active_days[]" value="{{ $key }}" {{ in_array($key, $activeDays) ? 'checked' : '' }} />
                                                    <span class="form-check-label fw-bold text-gray-700">{{ $name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <label class="fs-6 fw-semibold mb-5 d-flex align-items-center">
                                            Jam Aktif
                                            <button type="button" class="btn btn-icon btn-sm btn-light-primary ms-3" id="btn-add-time">
                                                <i class="ki-outline ki-plus fs-2"></i>
                                            </button>
                                        </label>
                                        <div id="time-repeater" class="d-flex flex-column gap-3">
                                            @foreach($activeTimes as $index => $time)
                                                <div class="time-item d-flex align-items-center gap-3">
                                                    <div class="flex-grow-1">
                                                        <input type="time" name="active_times[{{ $index }}][start]" class="form-control form-control-solid" value="{{ $time['start'] }}" />
                                                    </div>
                                                    <div class="text-muted fw-bold">s/d</div>
                                                    <div class="flex-grow-1">
                                                        <input type="time" name="active_times[{{ $index }}][end]" class="form-control form-control-solid" value="{{ $time['end'] }}" />
                                                    </div>
                                                    <button type="button" class="btn btn-icon btn-light-danger btn-remove-time {{ count($activeTimes) <= 1 ? 'd-none' : '' }}">
                                                        <i class="ki-outline ki-trash fs-3"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-muted fs-7 mt-3">Sistem hanya akan membalas otomatis pada hari dan jam yang dicentang/diatur di atas.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Main Column-->

                        <!--begin::Aside Column (Preview)-->
                        <div class="col-lg-4">
                            <div class="sticky-lg-top" style="top: 100px; z-index: 100">
                                <div class="card shadow-sm border-0" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-size: 400px;">
                                    <div class="card-header border-0 bg-primary py-4 min-h-auto">
                                        <h3 class="card-title text-white fs-6 fw-bold text-truncate">Auto Reply Preview</h3>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-column gap-5">
                                            <div class="d-flex flex-column align-items-end">
                                                <div class="bg-light-success p-3 rounded-3 shadow-sm position-relative" style="max-width: 80%;">
                                                    <div class="fs-7 text-gray-800 lh-base" id="preview-incoming">Halo admin...</div>
                                                    <div class="text-end mt-1"><span class="fs-9 text-muted">{{ date('H:i') }}</span></div>
                                                    <div class="position-absolute end-0 top-0 translate-middle-x mt-4" style="width: 0; height: 0; border-top: 12px solid #e1f5fe; border-right: 12px solid transparent;"></div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column align-items-start">
                                                <div class="bg-white p-3 rounded-3 shadow-sm position-relative w-100" style="max-width: 100%;">
                                                    <!-- Media Preview -->
                                                    <div id="preview-media-container" class="mb-3 d-none">
                                                        <img id="preview-image" src="" class="img-fluid rounded-2 w-100 d-none" style="max-height: 200px; object-fit: cover;">
                                                        <div id="preview-doc" class="p-3 bg-light rounded-2 d-none d-flex align-items-center border">
                                                            <i class="ki-outline ki-file fs-1 text-primary me-2"></i>
                                                            <span id="preview-doc-name" class="fs-7 fw-semibold text-truncate">document.pdf</span>
                                                        </div>
                                                    </div>

                                                    <div id="preview-reply" class="fs-7 text-gray-800 lh-base" style="white-space: pre-wrap;">Tulis pesan balasan...</div>
                                                    <div class="text-end mt-2">
                                                        <span class="fs-9 text-muted" style="font-size: 0.65rem !important;">{{ date('H:i') }} <i class="ki-outline ki-double-check text-primary fs-9 ms-1"></i></span>
                                                    </div>
                                                    <div class="position-absolute start-0 top-0 translate-middle-x mt-4" style="width: 0; height: 0; border-top: 12px solid white; border-left: 12px solid transparent; transform: scaleX(-1);"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card card-flush mt-5">
                                    <div class="card-body p-5">
                                        <div class="d-flex flex-stack mb-5">
                                            <div class="me-5">
                                                <label class="fs-6 fw-semibold">Aktifkan Aturan</label>
                                            </div>
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $reply->is_active ?? true) ? 'checked' : '' }} />
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="ki-outline ki-check fs-2 me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Buat Auto Reply' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Aside Column-->
                    </div>
                </form>

            </div>
        </div>
        <!--end::Content-->
    </div>

    @push('js')
    <script>
        $(document).ready(function() {
            const triggerType = $('#trigger-type');
            const keywordWrapper = $('#keyword-wrapper');
            const inputContent = $('#input-content');
            const previewReply = $('#preview-reply');
            const inputMedia = $('#input-media');
            const previewMediaContainer = $('#preview-media-container');
            const previewImage = $('#preview-image');
            const previewDoc = $('#preview-doc');
            const previewDocName = $('#preview-doc-name');

            // --- Basic Logic ---
            triggerType.on('change', function() {
                if ($(this).val() === 'keyword') keywordWrapper.removeClass('d-none');
                else keywordWrapper.addClass('d-none');
            });

            inputContent.on('input keyup change', function() {
                const val = $(this).val();
                if (!val || val.trim() === '') {
                    previewReply.text("Tulis pesan balasan...");
                    return;
                }
                
                let simulated = val
                    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                    // WA Formatting Simulation
                    .replace(/\*([^*]+)\*/g, '<b>$1</b>')
                    .replace(/_([^_]+)_/g, '<i>$1</i>')
                    .replace(/~([^~]+)~/g, '<strike>$1</strike>')
                    .replace(/```([^`]+)```/g, '<code>$1</code>')
                    // Spintax
                    .replace(/\{([^{}]+)\}/g, function(match, options) {
                        const opts = options.split('|');
                        return '<span class="text-info fw-bold">' + opts[0] + '</span>';
                    });
                
                previewReply.html(simulated);
            });

            // --- Media Logic ---
            inputMedia.on('change', function() {
                const file = this.files[0];
                if (file) {
                    $('#remove-media-flag').val('0');
                    $('#existing-media-info').addClass('d-none');
                    previewMediaContainer.removeClass('d-none');
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => previewImage.attr('src', e.target.result).removeClass('d-none') && previewDoc.addClass('d-none');
                        reader.readAsDataURL(file);
                    } else {
                        previewImage.addClass('d-none');
                        previewDoc.removeClass('d-none');
                        previewDocName.text(file.name);
                    }
                }
            });

            $('#btn-remove-media').on('click', function() {
                $('#remove-media-flag').val('1');
                $('#existing-media-info').addClass('d-none');
                previewMediaContainer.addClass('d-none');
                inputMedia.val('');
            });

            // --- Emoji & Formatting ---
            $('#btn-emoji').on('click', () => $('#emoji-container').toggleClass('d-none'));
            $('.emoji-item').on('click', function() {
                const emoji = $(this).text();
                const pos = inputContent[0].selectionStart;
                const text = inputContent.val();
                inputContent.val(text.substring(0, pos) + emoji + text.substring(pos)).trigger('change').focus();
            });

            $('.format-btn').on('click', function() {
                const format = $(this).data('format');
                const start = inputContent[0].selectionStart;
                const end = inputContent[0].selectionEnd;
                const text = inputContent.val();
                const selected = text.substring(start, end);
                let formatted = selected;
                
                if (format === 'bold') formatted = `*${selected}*`;
                else if (format === 'italic') formatted = `_${selected}_`;
                else if (format === 'strike') formatted = `~${selected}~`;
                else if (format === 'mono') formatted = `\`\`\`${selected}\`\`\``;

                inputContent.val(text.substring(0, start) + formatted + text.substring(end)).trigger('change').focus();
            });

            // --- Time Repeater ---
            let timeIndex = {{ count($activeTimes) }};
            $('#btn-add-time').on('click', function() {
                const html = `
                    <div class="time-item d-flex align-items-center gap-3">
                        <div class="flex-grow-1"><input type="time" name="active_times[${timeIndex}][start]" class="form-control form-control-solid" value="00:00" /></div>
                        <div class="text-muted fw-bold">s/d</div>
                        <div class="flex-grow-1"><input type="time" name="active_times[${timeIndex}][end]" class="form-control form-control-solid" value="23:59" /></div>
                        <button type="button" class="btn btn-icon btn-light-danger btn-remove-time"><i class="ki-outline ki-trash fs-3"></i></button>
                    </div>
                `;
                $('#time-repeater').append(html);
                timeIndex++;
                $('.btn-remove-time').removeClass('d-none');
            });

            $(document).on('click', '.btn-remove-time', function() {
                $(this).closest('.time-item').remove();
                if ($('.time-item').length <= 1) $('.btn-remove-time').addClass('d-none');
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
            inputContent.trigger('change');
            if (inputMedia[0].files.length) inputMedia.trigger('change');
            @if($isEdit && $reply->media_path) previewMediaContainer.removeClass('d-none'); @endif
        });
    </script>
    <style>
        .emoji-item:hover { transform: scale(1.3); transition: 0.1s; }
    </style>
    @endpush
</x-metronic-layout>

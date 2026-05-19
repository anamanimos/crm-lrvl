<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Edit Broadcast
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.broadcasts.index') }}" class="text-muted text-hover-primary">Broadcast</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Edit</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ki-outline ki-arrow-left fs-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <form action="{{ route('admin.broadcasts.update', $broadcast->id) }}" method="POST" id="broadcast-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-5">
                        <!--begin::Main Column (9 Columns)-->
                        <div class="col-lg-9">
                            <div class="row g-5">
                                <div class="col-12">
                                    <div class="card card-flush h-lg-100">
                                        <div class="card-header pt-7">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="card-label fw-bold text-gray-800">Informasi Dasar</span>
                                                <span class="text-muted mt-1 fw-semibold fs-7">Atur detail kampanye broadcast Anda</span>
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="fv-row mb-10">
                                                <label class="required fs-6 fw-semibold mb-2">Nama Broadcast</label>
                                                <input type="text" name="name" id="input-name" class="form-control form-control-solid @error('name') is-invalid @enderror" 
                                                       placeholder="Contoh: Promo Ramadhan 2024" value="{{ old('name', $broadcast->name) }}" required />
                                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="fv-row mb-10">
                                                <div class="d-flex flex-stack mb-2">
                                                    <label class="required fs-6 fw-semibold">Konten Pesan</label>
                                                    <button type="button" class="btn btn-sm btn-light-primary" id="btn-add-message">
                                                        <i class="ki-outline ki-plus fs-2"></i> Tambah Variasi Pesan
                                                    </button>
                                                </div>
                                                
                                                <div id="messages-container">
                                                    @php
                                                        $templates = json_decode($broadcast->message_template, true);
                                                        if (!is_array($templates) || count($templates) == 0) {
                                                            $templates = [$broadcast->message_template];
                                                        }
                                                    @endphp
                                                    @foreach($templates as $index => $tmpl)
                                                    <div class="message-item position-relative mb-4">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="badge badge-light-primary">Pesan {{ $index + 1 }}</span>
                                                            @if($index > 0)
                                                            <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-message">
                                                                <i class="ki-outline ki-trash fs-5"></i>
                                                            </button>
                                                            @endif
                                                        </div>
                                                        <textarea name="message_template[]" class="form-control form-control-solid message-input @error('message_template') is-invalid @enderror" 
                                                                  rows="6" placeholder="Tulis pesan di sini..." required>{{ old('message_template.'.$index, $tmpl) }}</textarea>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                
                                                <div class="text-muted fs-7 mt-3">
                                                    <p class="mb-1">Gunakan variabel: <code>{name}</code>, <code>{wa_number}</code></p>
                                                    <p class="mb-0">Dukung Spintax: <code>{Halo|Hai|Selamat Pagi}</code> untuk variasi kata.</p>
                                                    <p class="mb-0 mt-1"><span class="badge badge-light-info">Info</span> Sistem akan memilih salah satu pesan secara acak untuk setiap penerima agar terhindar dari banned.</p>
                                                </div>
                                                @error('message_template') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>

                                            <div class="fv-row mb-0">
                                                <label class="fs-6 fw-semibold mb-2">Lampiran (Gambar/Dokumen)</label>
                                                <input type="file" name="media_file" id="input-media" class="form-control form-control-solid" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" />
                                                <div class="text-muted fs-7 mt-2">Maksimal 5MB. Gambar akan dikirim sebagai foto, file lainnya sebagai dokumen.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!--begin::Targeting-->
                                    <div class="card card-flush h-100">
                                        <div class="card-header pt-7">
                                            <h3 class="card-title fw-bold">Target Audience</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="fv-row mb-7">
                                                <label class="fs-6 fw-semibold mb-2">Tipe Target</label>
                                                <select name="target_type" id="target-type" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                                    <option value="all" {{ old('target_type', $broadcast->target_type) == 'all' ? 'selected' : '' }}>Semua Pelanggan</option>
                                                    <option value="label" {{ old('target_type', $broadcast->target_type) == 'label' ? 'selected' : '' }}>Berdasarkan Label</option>
                                                    <option value="custom" {{ old('target_type', $broadcast->target_type) == 'custom' ? 'selected' : '' }}>Pilih Manual</option>
                                                </select>
                                            </div>

                                            <div id="target-label-wrapper" class="fv-row mb-7 d-none">
                                                <label class="fs-6 fw-semibold mb-2">Pilih Label</label>
                                                <select name="label_id" class="form-select form-select-solid" data-control="select2">
                                                    @foreach($labels as $label)
                                                        <option value="{{ $label->id }}" {{ old('label_id', $broadcast->target_filters['label_id'] ?? '') == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="target-custom-wrapper" class="fv-row mb-7 d-none">
                                                <label class="fs-6 fw-semibold mb-2">Pilih Pelanggan</label>

                                                <!-- Selected Badges Container (Like Active Filters) - NOW ABOVE -->
                                                <div class="border border-dashed border-gray-300 rounded p-5 bg-light-dark bg-opacity-5 mb-5">
                                                    <div class="d-flex flex-wrap align-items-center gap-3" id="selected-customers-container">
                                                        <span class="text-muted fw-semibold fs-7 me-2 w-100 mb-2">Pelanggan Terpilih:</span>
                                                        @php
                                                            $selectedCustomerIds = old('customer_ids', $broadcast->target_filters['customer_ids'] ?? []);
                                                        @endphp
                                                        @if(empty($selectedCustomerIds))
                                                        <div id="empty-selection-msg" class="text-gray-400 fs-7 italic px-2">Belum ada pelanggan dipilih</div>
                                                        @endif
                                                        @foreach($customers as $customer)
                                                            @if(in_array($customer->id, $selectedCustomerIds))
                                                            <div class="badge badge-lg badge-primary d-flex align-items-center px-4 py-2 gap-2" id="customer-badge-{{ $customer->id }}">
                                                                <div class="d-flex flex-column align-items-start">
                                                                    <span class="fs-7 fw-bold">{{ $customer->name }}</span>
                                                                    <span class="fs-9 opacity-75">{{ $customer->wa_number }}</span>
                                                                </div>
                                                                <a href="javascript:;" class="remove-customer text-white ms-2" data-id="{{ $customer->id }}">
                                                                    <i class="ki-outline ki-cross fs-6 text-white"></i>
                                                                </a>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                
                                                <!-- Search Trigger - NOW BELOW -->
                                                <select id="customer-search" class="form-select form-select-solid" data-control="select2" data-placeholder="Cari nama atau nomor...">
                                                    <option></option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->wa_number }}">
                                                            {{ $customer->name }} ({{ $customer->wa_number }})
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <!-- Hidden Inputs Container -->
                                                <div id="hidden-customer-inputs">
                                                    @foreach($selectedCustomerIds ?? [] as $id)
                                                    <input type="hidden" name="customer_ids[]" value="{{ $id }}" id="customer-input-{{ $id }}">
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Targeting-->
                                </div>

                                <div class="col-md-6">
                                    <!--begin::Settings-->
                                    <div class="card card-flush h-100">
                                        <div class="card-header pt-7">
                                            <h3 class="card-title fw-bold">Pengaturan Antrian</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3 mb-7">
                                                <div class="col-6">
                                                    <label class="fs-7 fw-bold mb-1">Min Delay (s)</label>
                                                    <input type="number" name="delay_min" class="form-control form-control-solid" value="{{ old('delay_min', $broadcast->delay_min) }}" min="1" />
                                                </div>
                                                <div class="col-6">
                                                    <label class="fs-7 fw-bold mb-1">Max Delay (s)</label>
                                                    <input type="number" name="delay_max" class="form-control form-control-solid" value="{{ old('delay_max', $broadcast->delay_max) }}" min="1" />
                                                </div>
                                            </div>

                                            <div class="fv-row mb-7">
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" id="schedule-toggle" name="schedule_type" value="date" {{ old('schedule_type', $broadcast->scheduled_at ? 'date' : '') == 'date' ? 'checked' : '' }} />
                                                    <label class="form-check-label fw-bold text-gray-700" for="schedule-toggle">
                                                        Jadwalkan Pengiriman
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="schedule-wrapper" class="fv-row mb-7 {{ old('schedule_type', $broadcast->scheduled_at ? 'date' : '') == 'date' ? '' : 'd-none' }}">
                                                <label class="fs-6 fw-semibold mb-2">Waktu Pengiriman</label>
                                                <input type="text" name="scheduled_at" id="scheduled_at" class="form-control form-control-solid" placeholder="Pilih tanggal & waktu" value="{{ old('scheduled_at', $broadcast->scheduled_at) }}" />
                                            </div>
                                        </div>
                                        <div class="card-footer pt-0">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <span class="indicator-label">Simpan Perubahan</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!--end::Settings-->
                                </div>
                            </div>
                        </div>
                        <!--end::Main Column-->

                        <!--begin::Preview Column (3 Columns)-->
                        <div class="col-lg-3">
                            <div class="sticky-lg-top" style="top: 100px; z-index: 100">
                                <div class="card shadow-sm border-0" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-size: 400px;">
                                    <div class="card-header border-0 bg-success py-4 min-h-auto">
                                        <h3 id="preview-header-title" class="card-title text-white fs-6 fw-bold text-truncate">Pratinjau Broadcast</h3>
                                    </div>
                                    <div class="card-body p-4">
                                        <!-- Preview Navigation -->
                                        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-2 rounded shadow-sm d-none" id="preview-nav">
                                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" id="btn-prev-preview">
                                                <i class="ki-outline ki-left fs-4"></i>
                                            </button>
                                            <span class="badge badge-light-primary fs-7 fw-bold" id="preview-indicator">Pesan 1 dari 1</span>
                                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" id="btn-next-preview">
                                                <i class="ki-outline ki-right fs-4"></i>
                                            </button>
                                        </div>

                                        <!-- WhatsApp Bubble -->
                                        <div class="d-flex flex-column gap-3">
                                            <div class="bg-white p-3 rounded-3 shadow-sm position-relative" style="max-width: 100%;">
                                                <!-- Media Preview -->
                                                <div id="preview-media-container" class="mb-3 d-none">
                                                    <img id="preview-image" src="" class="img-fluid rounded-2 w-100 d-none" style="max-height: 200px; object-fit: cover;">
                                                    <div id="preview-doc" class="p-3 bg-light rounded-2 d-none d-flex align-items-center border">
                                                        <i class="ki-outline ki-file fs-1 text-primary me-2"></i>
                                                        <span id="preview-doc-name" class="fs-7 fw-semibold text-truncate">document.pdf</span>
                                                    </div>
                                                </div>
                                                <!-- Message Content -->
                                                <div id="preview-content" class="fs-7 text-gray-800 lh-base" style="white-space: pre-wrap;">Tulis pesan di sini...</div>
                                                
                                                <!-- Timestamp Style -->
                                                <div class="text-end mt-2">
                                                    <span class="fs-9 text-muted" style="font-size: 0.65rem !important;">{{ date('H:i') }} <i class="ki-outline ki-double-check text-primary fs-9 ms-1"></i></span>
                                                </div>

                                                <!-- Bubble Tail -->
                                                <div class="position-absolute start-0 top-0 translate-middle-x mt-4" style="width: 0; height: 0; border-top: 12px solid white; border-left: 12px solid transparent; transform: scaleX(-1);"></div>
                                            </div>

                                            <div class="text-center">
                                                <span class="badge badge-light-primary fs-8 px-3 py-2">Mode: Broadcast Kampanye</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-light-warning border border-warning border-dashed mt-5 p-5">
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-warning fs-6 fw-bold">Tips Spintax</h4>
                                        <span class="fs-7">Variabel spintax akan muncul sebagai salah satu opsi acak pada pratinjau ini.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Preview Column-->
                    </div>
                </form>

            </div>
        </div>
        <!--end::Content-->
    </div>

    @push('js')
    <script>
        (function() {
            function initBroadcastPreview() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(initBroadcastPreview, 100);
                    return;
                }
                
                const $ = jQuery;
                const inputName = $('#input-name');
                const messagesContainer = $('#messages-container');
                const targetType = $('#target-type');
                const labelWrapper = $('#target-label-wrapper');
                const customWrapper = $('#target-custom-wrapper');
                const scheduleToggle = $('#schedule-toggle');
                const scheduleWrapper = $('#schedule-wrapper');

                // Preview Elements
                const previewHeaderTitle = $('#preview-header-title');
                const previewContent = $('#preview-content');

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
                        // Process Spintax for preview (pick one)
                        .replace(/\{([^{}]+)\}/g, function(match, options) {
                            const opts = options.split('|');
                            if (opts.length > 1) {
                                return '<span class="text-info fw-bold">' + opts[0] + '</span>';
                            }
                            if (match === '{name}') return '<span class="text-primary fw-bold">Budi Santoso</span>';
                            if (match === '{wa_number}') return '<span class="text-primary fw-bold">628123456789</span>';
                            return match;
                        });
                    
                    previewContent.html(simulated);
                }

                // Listeners
                inputName.on('input keyup change', function() {
                    const val = $(this).val();
                    previewHeaderTitle.text(val.trim() === '' ? "Pratinjau Broadcast" : val);
                });

                // Messages logic
                const btnAddMessage = $('#btn-add-message');
                let messageCount = 1;
                let currentPreviewIndex = 0;

                function updatePreviewNav() {
                    const totalMessages = messagesContainer.find('.message-item').length;
                    
                    if (totalMessages > 1) {
                        $('#preview-nav').removeClass('d-none');
                        $('#preview-indicator').text(`Pesan ${currentPreviewIndex + 1} dari ${totalMessages}`);
                    } else {
                        $('#preview-nav').addClass('d-none');
                    }
                    
                    // Show current message preview
                    const inputs = messagesContainer.find('.message-input');
                    if (inputs[currentPreviewIndex]) {
                        updateContentPreview($(inputs[currentPreviewIndex]).val());
                    } else {
                        currentPreviewIndex = 0;
                        if (inputs[0]) updateContentPreview($(inputs[0]).val());
                    }
                }

                $('#btn-prev-preview').on('click', function() {
                    const totalMessages = messagesContainer.find('.message-item').length;
                    if (totalMessages === 0) return;
                    currentPreviewIndex = (currentPreviewIndex - 1 + totalMessages) % totalMessages;
                    updatePreviewNav();
                });

                $('#btn-next-preview').on('click', function() {
                    const totalMessages = messagesContainer.find('.message-item').length;
                    if (totalMessages === 0) return;
                    currentPreviewIndex = (currentPreviewIndex + 1) % totalMessages;
                    updatePreviewNav();
                });

                btnAddMessage.on('click', function() {
                    messageCount++;
                    const newItem = $(`
                        <div class="message-item position-relative mb-4" style="display:none;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge badge-light-primary">Pesan ${messageCount}</span>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-message">
                                    <i class="ki-outline ki-trash fs-5"></i>
                                </button>
                            </div>
                            <textarea name="message_template[]" class="form-control form-control-solid message-input" rows="6" placeholder="Tulis variasi pesan ke-${messageCount} di sini..." required></textarea>
                        </div>
                    `);
                    messagesContainer.append(newItem);
                    newItem.slideDown(200);
                    updatePreviewNav();
                });

                messagesContainer.on('click', '.btn-remove-message', function() {
                    if (messagesContainer.find('.message-item').length > 1) {
                        const item = $(this).closest('.message-item');
                        const index = item.index();
                        item.slideUp(200, function() {
                            $(this).remove();
                            updateMessageBadges();
                            
                            if (currentPreviewIndex >= messagesContainer.find('.message-item').length) {
                                currentPreviewIndex = Math.max(0, messagesContainer.find('.message-item').length - 1);
                            } else if (index < currentPreviewIndex) {
                                currentPreviewIndex--;
                            }
                            updatePreviewNav();
                        });
                    }
                });

                function updateMessageBadges() {
                    messageCount = 0;
                    messagesContainer.find('.message-item').each(function() {
                        messageCount++;
                        $(this).find('.badge').text('Pesan ' + messageCount);
                    });
                }

                messagesContainer.on('input keyup change focus', '.message-input', function() {
                    currentPreviewIndex = $(this).closest('.message-item').index();
                    updatePreviewNav();
                });

                function toggleTarget() {
                    const val = targetType.val();
                    labelWrapper.addClass('d-none');
                    customWrapper.addClass('d-none');
                    
                    if (val === 'label') labelWrapper.removeClass('d-none');
                    if (val === 'custom') customWrapper.removeClass('d-none');
                }

                targetType.on('change', toggleTarget);
                toggleTarget();

                // Custom Customer Selection (Badge Style)
                const customerSearch = $('#customer-search');
                const selectedContainer = $('#selected-customers-container');
                const hiddenInputs = $('#hidden-customer-inputs');
                const emptyMsg = $('#empty-selection-msg');
                let selectedIds = [];
                
                // Init selectedIds from hidden inputs
                $('#hidden-customer-inputs input').each(function() {
                    selectedIds.push($(this).val().toString());
                });

                customerSearch.on('select2:select', function(e) {
                    const data = e.params.data;
                    const id = data.id;
                    const name = $(data.element).data('name');
                    const phone = $(data.element).data('phone');

                    if (selectedIds.includes(id)) {
                        customerSearch.val(null).trigger('change');
                        return;
                    }

                    selectedIds.push(id);
                    emptyMsg.addClass('d-none');

                    // Create Badge
                    const badge = $(`
                        <div class="badge badge-lg badge-primary d-flex align-items-center px-4 py-2 gap-2" id="customer-badge-${id}">
                            <div class="d-flex flex-column align-items-start">
                                <span class="fs-7 fw-bold">${name}</span>
                                <span class="fs-9 opacity-75">${phone}</span>
                            </div>
                            <a href="javascript:;" class="remove-customer text-white ms-2" data-id="${id}">
                                <i class="ki-outline ki-cross fs-6 text-white"></i>
                            </a>
                        </div>
                    `);

                    // Create Hidden Input
                    const input = $(`<input type="hidden" name="customer_ids[]" value="${id}" id="customer-input-${id}">`);

                    selectedContainer.append(badge);
                    hiddenInputs.append(input);

                    // Clear Search
                    customerSearch.val(null).trigger('change');
                });

                $(document).on('click', '.remove-customer', function() {
                    const id = $(this).data('id').toString();
                    $(`#customer-badge-${id}`).remove();
                    $(`#customer-input-${id}`).remove();
                    selectedIds = selectedIds.filter(i => i !== id);
                    
                    if (selectedIds.length === 0) {
                        emptyMsg.removeClass('d-none');
                    }
                });

                scheduleToggle.on('change', function() {
                    if ($(this).is(':checked')) {
                        scheduleWrapper.removeClass('d-none');
                    } else {
                        scheduleWrapper.addClass('d-none');
                    }
                });

                // Initial trigger
                updatePreviewNav();

                // --- Media Preview ---
                const inputMedia = $('#input-media');
                const previewMediaContainer = $('#preview-media-container');
                const previewImage = $('#preview-image');
                const previewDoc = $('#preview-doc');
                const previewDocName = $('#preview-doc-name');

                inputMedia.on('change', function() {
                    const file = this.files[0];
                    if (file) {
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
                    } else {
                        previewMediaContainer.addClass('d-none');
                    }
                });
                // --- AJAX Submission ---
                const broadcastForm = $('#broadcast-form');
                const btnSubmit = broadcastForm.find('button[type="submit"]');

                broadcastForm.on('submit', function(e) {
                    e.preventDefault();

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
                                html: errorMsg,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, mengerti!",
                                customClass: { confirmButton: "btn btn-primary" }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            text: "Terjadi kesalahan sistem.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, mengerti!",
                            customClass: { confirmButton: "btn btn-primary" }
                        });
                    })
                    .finally(() => {
                        btnSubmit.prop('disabled', false);
                        btnSubmit.html('Buat Broadcast');
                    });
                });

                // --- Flatpickr ---
                $("#scheduled_at").flatpickr({
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today",
                    time_24hr: true
                });
            }

            initBroadcastPreview();
        })();
    </script>
    @endpush
</x-metronic-layout>

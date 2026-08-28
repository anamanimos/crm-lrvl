<x-metronic-layout>
    @php
        $title = 'Pengaturan: Sumber Chat (Lead Source)';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Aturan Sumber Chat
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('settings.index') }}" class="text-muted text-hover-primary">Pengaturan</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Sumber Chat</li>
                </ul>
            </div>
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm fw-bold btn-primary" onclick="openAddModal()">
                    <i class="ki-outline ki-plus fs-4 me-1"></i>
                    Tambah Aturan
                </button>
            </div>
            <!--end::Actions-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="card mb-5">
                <div class="card-body">
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                        <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Cara Kerja Otomatisasi Sumber Chat</h4>
                                <div class="fs-6 text-gray-700">
                                    Ketika customer mengirim pesan chat pertama kali ke WhatsApp (misalnya dari link iklan/bio), sistem akan mencocokkan teks pesan dengan kata kunci di bawah ini. Jika cocok, kolom <strong>Source</strong> pada data customer akan otomatis terisi sesuai pengaturan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2>Daftar Aturan Pencocokan</h2>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="rules_table">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Nama Aturan</th>
                                    <th>Kata Kunci / Pola</th>
                                    <th>Metode</th>
                                    <th>Hasil Sumber (Source)</th>
                                    <th>Status</th>
                                    <th class="text-end min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold" id="rules_tbody">
                                @forelse($rules as $rule)
                                <tr id="rule-row-{{ $rule->id }}" data-json="{{ json_encode($rule) }}">
                                    <td>
                                        <span class="fw-bold text-gray-800" id="rule-name-{{ $rule->id }}">{{ $rule->name }}</span>
                                    </td>
                                    <td>
                                        <code class="text-primary fw-bold fs-7 bg-light-primary px-2 py-1 rounded" id="rule-keyword-{{ $rule->id }}">{{ $rule->keyword }}</code>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-secondary" id="rule-type-{{ $rule->id }}">
                                            @if($rule->match_type === 'exact')
                                                Sama Persis
                                            @elseif($rule->match_type === 'starts_with')
                                                Diawali Kata
                                            @else
                                                Mengandung Kata
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-info fw-bold fs-7 py-1 px-3" id="rule-source-{{ $rule->id }}">
                                            <i class="ki-outline ki-compass fs-8 me-1 text-info"></i>{{ $rule->source_name }}
                                        </span>
                                    </td>
                                    <td id="status-col-{{ $rule->id }}">
                                        @if($rule->is_active)
                                            <span class="badge badge-light-success">Aktif</span>
                                        @else
                                            <span class="badge badge-light-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" 
                                            onclick="openEditModal({{ $rule->id }})" 
                                            title="Edit">
                                            <i class="ki-outline ki-pencil fs-4"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-{{ $rule->is_active ? 'warning' : 'success' }} me-2 btn-toggle-status" 
                                            id="btn-toggle-{{ $rule->id }}"
                                            onclick="toggleRuleStatus({{ $rule->id }}, '{{ route('chat-sources.toggle-status', $rule->id) }}')" 
                                            title="{{ $rule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="ki-outline {{ $rule->is_active ? 'ki-cross' : 'ki-check' }} fs-3"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger" 
                                            onclick="deleteRule({{ $rule->id }}, '{{ route('chat-sources.destroy', $rule->id) }}')" 
                                            title="Hapus">
                                            <i class="ki-outline ki-trash fs-3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="6" class="text-center text-muted py-8">Belum ada aturan sumber chat yang dibuat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Add/Edit Rule -->
    <div class="modal fade" id="modal_rule" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-550px">
            <div class="modal-content">
                <form id="form_rule" method="POST">
                    @csrf
                    <input type="hidden" id="rule_id" name="id" value="" />
                    <div class="modal-header">
                        <h2 class="fw-bold" id="modal_title">Tambah Aturan Sumber Chat</h2>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </div>
                    </div>
                    <div class="modal-body py-6 px-lg-8">
                        <div class="fv-row mb-6">
                            <label class="required fs-6 fw-semibold mb-2">Nama Aturan</label>
                            <input type="text" class="form-control form-control-solid" id="input_name" name="name" placeholder="Contoh: Campaign Iklan TikTok" required />
                            <div class="text-muted fs-7 mt-1">Nama pengenal internal untuk aturan ini.</div>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="required fs-6 fw-semibold mb-2">Kata Kunci / Frasa yang Dicari</label>
                            <input type="text" class="form-control form-control-solid" id="input_keyword" name="keyword" placeholder="Contoh: tiktok atau konsultasi tiktok" required />
                            <div class="text-muted fs-7 mt-1">Kata atau kalimat yang diharapkan ada pada pesan awal chat customer.</div>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="required fs-6 fw-semibold mb-2">Metode Pencocokan</label>
                            <select class="form-select form-select-solid" id="input_match_type" name="match_type" required>
                                <option value="contains" selected>Mengandung Kata (Contains - Direkomendasikan)</option>
                                <option value="starts_with">Diawali Kata (Starts With)</option>
                                <option value="exact">Sama Persis (Exact Match)</option>
                            </select>
                            <div class="text-muted fs-7 mt-1">Pencocokan tidak membedakan huruf besar/kecil (*case-insensitive*).</div>
                        </div>

                        <div class="fv-row mb-4">
                            <label class="required fs-6 fw-semibold mb-2">Tetapkan Sumber (Source) Menjadi</label>
                            <input type="text" class="form-control form-control-solid" id="input_source_name" name="source_name" placeholder="Contoh: TikTok, Instagram, Meta Ads" required />
                            <div class="text-muted fs-7 mt-1">Nilai yang akan otomatis disimpan di kolom <strong>Source</strong> customer.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn_submit_rule">
                            <span class="indicator-label">Simpan Aturan</span>
                            <span class="indicator-progress">Memproses...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        const storeUrl = "{{ route('chat-sources.store') }}";

        function openAddModal() {
            document.getElementById('modal_title').innerText = 'Tambah Aturan Sumber Chat';
            document.getElementById('rule_id').value = '';
            document.getElementById('input_name').value = '';
            document.getElementById('input_keyword').value = '';
            document.getElementById('input_match_type').value = 'contains';
            document.getElementById('input_source_name').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modal_rule'));
            modal.show();
        }

        function openEditModal(id) {
            const row = document.getElementById('rule-row-' + id);
            if (!row) return;

            const data = JSON.parse(row.getAttribute('data-json'));

            document.getElementById('modal_title').innerText = 'Edit Aturan Sumber Chat';
            document.getElementById('rule_id').value = data.id;
            document.getElementById('input_name').value = data.name;
            document.getElementById('input_keyword').value = data.keyword;
            document.getElementById('input_match_type').value = data.match_type || 'contains';
            document.getElementById('input_source_name').value = data.source_name;

            const modal = new bootstrap.Modal(document.getElementById('modal_rule'));
            modal.show();
        }

        function getMatchTypeName(type) {
            if (type === 'exact') return 'Sama Persis';
            if (type === 'starts_with') return 'Diawali Kata';
            return 'Mengandung Kata';
        }

        // Handle AJAX Submit
        document.getElementById('form_rule').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('btn_submit_rule');
            const id = document.getElementById('rule_id').value;
            const isEdit = Boolean(id);
            const url = isEdit ? `{{ url('settings/chat-sources') }}/${id}` : storeUrl;

            const payload = {
                name: document.getElementById('input_name').value.trim(),
                keyword: document.getElementById('input_keyword').value.trim(),
                match_type: document.getElementById('input_match_type').value,
                source_name: document.getElementById('input_source_name').value.trim(),
            };

            if (!payload.name || !payload.keyword || !payload.source_name) return;

            submitBtn.setAttribute('data-kt-indicator', 'on');
            submitBtn.disabled = true;

            fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;

                if (res.success) {
                    const modalEl = document.getElementById('modal_rule');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    const d = res.data;

                    if (isEdit) {
                        // Update existing row
                        document.getElementById('rule-name-' + d.id).innerText = d.name;
                        document.getElementById('rule-keyword-' + d.id).innerText = d.keyword;
                        document.getElementById('rule-type-' + d.id).innerText = getMatchTypeName(d.match_type);
                        document.getElementById('rule-source-' + d.id).innerHTML = `<i class="ki-outline ki-compass fs-8 me-1 text-info"></i>${escapeHtml(d.source_name)}`;
                        
                        const row = document.getElementById('rule-row-' + d.id);
                        if (row) {
                            row.setAttribute('data-json', JSON.stringify(d));
                        }
                    } else {
                        // Remove empty row
                        const emptyRow = document.getElementById('empty-row');
                        if (emptyRow) emptyRow.remove();

                        // Prepend new row
                        const newRow = `
                            <tr id="rule-row-${d.id}" data-json='${JSON.stringify(d)}'>
                                <td>
                                    <span class="fw-bold text-gray-800" id="rule-name-${d.id}">${escapeHtml(d.name)}</span>
                                </td>
                                <td>
                                    <code class="text-primary fw-bold fs-7 bg-light-primary px-2 py-1 rounded" id="rule-keyword-${d.id}">${escapeHtml(d.keyword)}</code>
                                </td>
                                <td>
                                    <span class="badge badge-light-secondary" id="rule-type-${d.id}">${getMatchTypeName(d.match_type)}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-info fw-bold fs-7 py-1 px-3" id="rule-source-${d.id}">
                                        <i class="ki-outline ki-compass fs-8 me-1 text-info"></i>${escapeHtml(d.source_name)}
                                    </span>
                                </td>
                                <td id="status-col-${d.id}">
                                    <span class="badge badge-light-success">Aktif</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" 
                                        onclick="openEditModal(${d.id})" 
                                        title="Edit">
                                        <i class="ki-outline ki-pencil fs-4"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-warning me-2 btn-toggle-status" 
                                        id="btn-toggle-${d.id}"
                                        onclick="toggleRuleStatus(${d.id}, '${d.toggle_url}')" 
                                        title="Nonaktifkan">
                                        <i class="ki-outline ki-cross fs-3"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" 
                                        onclick="deleteRule(${d.id}, '${d.destroy_url}')" 
                                        title="Hapus">
                                        <i class="ki-outline ki-trash fs-3"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        document.getElementById('rules_tbody').insertAdjacentHTML('afterbegin', newRow);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan.'
                    });
                }
            })
            .catch(err => {
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan koneksi.'
                });
            });
        });

        function toggleRuleStatus(id, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const statusCol = document.getElementById('status-col-' + id);
                    const toggleBtn = document.getElementById('btn-toggle-' + id);

                    if (res.is_active) {
                        statusCol.innerHTML = '<span class="badge badge-light-success">Aktif</span>';
                        toggleBtn.className = 'btn btn-sm btn-icon btn-light-warning me-2 btn-toggle-status';
                        toggleBtn.title = 'Nonaktifkan';
                        toggleBtn.innerHTML = '<i class="ki-outline ki-cross fs-3"></i>';
                    } else {
                        statusCol.innerHTML = '<span class="badge badge-light-danger">Nonaktif</span>';
                        toggleBtn.className = 'btn btn-sm btn-icon btn-light-success me-2 btn-toggle-status';
                        toggleBtn.title = 'Aktifkan';
                        toggleBtn.innerHTML = '<i class="ki-outline ki-check fs-3"></i>';
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });
        }

        function deleteRule(id, url) {
            Swal.fire({
                title: 'Hapus Aturan?',
                text: 'Aturan ini tidak akan digunakan lagi untuk mendeteksi sumber pesan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light'
                }
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            const row = document.getElementById('rule-row-' + id);
                            if (row) row.remove();

                            const tbody = document.getElementById('rules_tbody');
                            if (tbody && tbody.children.length === 0) {
                                tbody.innerHTML = '<tr id="empty-row"><td colspan="6" class="text-center text-muted py-8">Belum ada aturan sumber chat yang dibuat.</td></tr>';
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Aturan berhasil dihapus.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
    @endpush
</x-metronic-layout>

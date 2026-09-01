<x-metronic-layout>
    @php
        $title = 'Pengaturan: API Keys';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    API Keys
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
                    <li class="breadcrumb-item text-muted">API Keys</li>
                </ul>
            </div>
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_api_key">
                    <i class="ki-outline ki-plus fs-4 me-1"></i>
                    Create New Key
                </button>
            </div>
            <!--end::Actions-->
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="d-flex flex-column flex-lg-row">
                @include('settings.sidebar')

                <!--begin::Main column-->
                <div class="flex-row-fluid">
                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h2>Daftar API Keys</h2>
                            </div>
                        </div>
                        <div class="card-body py-4">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5" id="api_keys_table">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th>Nama</th>
                                            <th>API Key (Token)</th>
                                            <th>Status</th>
                                            <th>Dibuat</th>
                                            <th class="text-end min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-semibold" id="api_keys_tbody">
                                        @forelse($apiKeys as $key)
                                        <tr id="key-row-{{ $key->id }}">
                                            <td>
                                                <span class="fw-bold text-gray-800">{{ $key->name }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <code class="me-2 text-primary fw-bold" id="key-text-{{ $key->id }}">{{ $key->key }}</code>
                                                    <button type="button" class="btn btn-icon btn-sm btn-light-primary btn-copy" onclick="copyKey('{{ $key->key }}')" title="Salin Key">
                                                        <i class="ki-outline ki-copy fs-4"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td id="status-col-{{ $key->id }}">
                                                @if($key->is_active)
                                                    <span class="badge badge-light-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-light-danger">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>{{ $key->created_at->format('d M Y') }}</td>
                                            <td class="text-end text-nowrap">
                                                <div class="d-inline-flex align-items-center justify-content-end gap-2">
                                                    <button type="button" class="btn btn-icon btn-light-{{ $key->is_active ? 'warning' : 'success' }} btn-sm btn-toggle-status" 
                                                        id="btn-toggle-{{ $key->id }}"
                                                        onclick="toggleKeyStatus({{ $key->id }}, '{{ route('api-keys.toggle-status', $key->id) }}')" 
                                                        title="{{ $key->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="ki-outline {{ $key->is_active ? 'ki-cross' : 'ki-check' }} fs-4"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-icon btn-light-danger btn-sm" 
                                                        onclick="deleteKey({{ $key->id }}, '{{ route('api-keys.destroy', $key->id) }}')" 
                                                        title="Hapus">
                                                        <i class="ki-outline ki-trash fs-5"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr id="empty-row">
                                            <td colspan="5" class="text-center text-muted py-8">Belum ada API Key yang dibuat.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Main column-->
            </div>

        </div>
    </div>

    <!-- Modal Add API Key -->
    <div class="modal fade" id="modal_add_api_key" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <form id="form_add_api_key" method="POST" action="{{ route('api-keys.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="fw-bold">Buat API Key Baru</h2>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </div>
                    </div>
                    <div class="modal-body py-6 px-lg-8">
                        <div class="fv-row mb-6">
                            <label class="required fs-6 fw-semibold mb-2">Nama / Deskripsi Key</label>
                            <input type="text" class="form-control form-control-solid" id="api_key_name" name="name" placeholder="Contoh: Integrasi Server, Webhook Eksternal" required />
                            <div class="text-muted fs-7 mt-2">Beri label pengenal untuk apa API Key ini digunakan.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn_submit_api_key">
                            <span class="indicator-label">Generate Key</span>
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
    function copyKey(text) {
        if (!navigator.clipboard) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        } else {
            navigator.clipboard.writeText(text);
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'API Key berhasil disalin!',
            showConfirmButton: false,
            timer: 2000
        });
    }

    // AJAX Form Submission
    document.getElementById('form_add_api_key').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('btn_submit_api_key');
        const nameInput = document.getElementById('api_key_name');
        const name = nameInput.value.trim();

        if (!name) return;

        submitBtn.setAttribute('data-kt-indicator', 'on');
        submitBtn.disabled = true;

        fetch("{{ route('api-keys.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(res => {
            submitBtn.removeAttribute('data-kt-indicator');
            submitBtn.disabled = false;

            if (res.success) {
                // Close modal
                const modalEl = document.getElementById('modal_add_api_key');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                nameInput.value = '';

                // Remove empty row if present
                const emptyRow = document.getElementById('empty-row');
                if (emptyRow) emptyRow.remove();

                // Append new row
                const d = res.data;
                const newRow = `
                    <tr id="key-row-${d.id}">
                        <td><span class="fw-bold text-gray-800">${escapeHtml(d.name)}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <code class="me-2 text-primary fw-bold" id="key-text-${d.id}">${d.key}</code>
                                <button type="button" class="btn btn-icon btn-sm btn-light-primary btn-copy" onclick="copyKey('${d.key}')" title="Salin Key">
                                    <i class="ki-outline ki-copy fs-4"></i>
                                </button>
                            </div>
                        </td>
                        <td id="status-col-${d.id}">
                            <span class="badge badge-light-success">Aktif</span>
                        </td>
                        <td>${d.created_at}</td>
                        <td class="text-end text-nowrap">
                            <div class="d-inline-flex align-items-center justify-content-end gap-2">
                                <button type="button" class="btn btn-icon btn-light-warning btn-sm btn-toggle-status" 
                                    id="btn-toggle-${d.id}"
                                    onclick="toggleKeyStatus(${d.id}, '${d.toggle_url}')" 
                                    title="Nonaktifkan">
                                    <i class="ki-outline ki-cross fs-4"></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-light-danger btn-sm" 
                                    onclick="deleteKey(${d.id}, '${d.destroy_url}')" 
                                    title="Hapus">
                                    <i class="ki-outline ki-trash fs-5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                document.getElementById('api_keys_tbody').insertAdjacentHTML('afterbegin', newRow);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'API Key baru telah dibuat.',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Terjadi kesalahan saat membuat API Key.'
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

    function toggleKeyStatus(id, url) {
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

    function deleteKey(id, url) {
        Swal.fire({
            title: 'Hapus API Key?',
            text: 'Aplikasi yang menggunakan key ini tidak akan bisa mengakses API lagi.',
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
                        const row = document.getElementById('key-row-' + id);
                        if (row) row.remove();

                        const tbody = document.getElementById('api_keys_tbody');
                        if (tbody && tbody.children.length === 0) {
                            tbody.innerHTML = '<tr id="empty-row"><td colspan="5" class="text-center text-muted py-8">Belum ada API Key yang dibuat.</td></tr>';
                        }

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'API Key berhasil dihapus.',
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

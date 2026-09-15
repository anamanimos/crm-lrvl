<x-metronic-layout>
    @php
        $title = 'Label Customer';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Label Customer
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Label</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm fw-bold btn-light-danger btn-reset-labels">
                    <i class="ki-outline ki-arrows-circle fs-4 me-1"></i>
                    Kosongkan Label
                </button>
                <a href="{{ route('admin.labels.create') }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-4 me-1"></i>
                    Tambah Label
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <!--begin::WhatsApp Sync Notice-->
            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-5 mb-5">
                <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h5 class="text-gray-900 fw-bold mb-1">Sinkronisasi Label WhatsApp Business</h5>
                        <div class="fs-7 text-gray-700">
                            Label customer terhubung langsung dengan label WhatsApp. Setiap kali label dibuat, diubah, dihapus, atau dipasangkan ke kontak di WhatsApp Business, CRM akan otomatis memetakannya secara <em>real-time</em> via webhook.
                        </div>
                    </div>
                </div>
            </div>
            <!--end::WhatsApp Sync Notice-->

            <form id="form_reset_labels" action="{{ route('admin.labels.reset') }}" method="POST" style="display: none;">
                @csrf
            </form>
            
            @if (session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-cross-circle fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!--begin::Card-->
            <div class="card">
                <div class="card-body py-4">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_labels_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Label</th>
                                <th class="min-w-100px">Warna</th>
                                <th class="min-w-100px">Customer</th>
                                <th class="min-w-100px">Status</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse ($labels as $label)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge fs-6" style="background-color: {{ $label->color }}20; color: {{ $label->color }}">
                                            {{ $label->name }}
                                        </span>
                                        @if ($label->wa_label_id)
                                        <span class="badge badge-light-success fs-8" title="Label terhubung dengan WhatsApp">
                                            <i class="ki-outline ki-whatsapp fs-8 text-success me-1"></i>WA ID: {{ $label->wa_label_id }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="w-25px h-25px rounded" style="background-color: {{ $label->color }}"></div>
                                        <span class="text-muted">{{ $label->color }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-primary">{{ $label->customers_count }} customer</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $label->is_active ? 'success' : 'danger' }}">
                                        {{ $label->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.labels.edit', $label->id) }}" 
                                       class="btn btn-icon btn-light-primary btn-sm me-1">
                                        <i class="ki-outline ki-pencil fs-5"></i>
                                    </a>
                                    <button type="button" class="btn btn-icon btn-light-danger btn-sm btn-delete-label" 
                                            data-id="{{ $label->id }}" data-name="{{ $label->name }}">
                                        <i class="ki-outline ki-trash fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-outline ki-tag fs-3x text-muted mb-3"></i>
                                        <div class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Label</div>
                                        <div class="text-muted fs-7 max-w-400px text-center">
                                            Label WhatsApp akan otomatis dipetakan di sini saat Anda membuat, mengedit, atau menandai chat di aplikasi WhatsApp Business.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!--end::Card-->

        </div>
    </div>
</x-metronic-layout>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btnReset = document.querySelector('.btn-reset-labels');
        if (btnReset) {
            btnReset.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Kosongkan Semua Label?',
                    text: 'Seluruh label dan penandaan pelanggan saat ini akan dihapus. Label baru akan otomatis terpetakan ketika ada aktivitas label di WhatsApp.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Kosongkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form_reset_labels').submit();
                    }
                });
            });
        }

        document.querySelectorAll('.btn-delete-label').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                var name = this.dataset.name;
                
                Swal.fire({
                    title: 'Hapus Label?',
                    text: 'Label "' + name + '" akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ url('admin/labels/delete') }}/" + id, { 
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        });
                    }
                });
            });
        });
    });
</script>
@endpush

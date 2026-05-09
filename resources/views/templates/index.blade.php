<x-metronic-layout>
    @php
        $title = 'Template Chat';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Template Chat
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Template</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.templates.category.index') }}" class="btn btn-sm fw-bold btn-light-primary">
                    <i class="ki-outline ki-tag fs-4"></i>
                    Kelola Kategori
                </a>
                <a href="{{ route('admin.templates.create') }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-4"></i>
                    Tambah Template
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            @if (session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row g-5 g-xl-10">
                <!--begin::Col - Stats Area (Left 3 Columns)-->
                <div class="col-lg-3">
                    <div class="sticky-lg-top" style="top: 100px; z-index: 100">
                        <div class="d-flex flex-column gap-5">
                            <div class="card card-flush py-4">
                                <div class="card-header pt-0">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                <span class="symbol-label bg-light-primary">
                                                    <i class="ki-outline ki-abstract-26 fs-2 text-primary"></i>
                                                </span>
                                            </div>
                                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['total'] }}</span>
                                        </div>
                                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Template</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-flush py-4">
                                <div class="card-header pt-0">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                <span class="symbol-label bg-light-success">
                                                    <i class="ki-outline ki-check-circle fs-2 text-success"></i>
                                                </span>
                                            </div>
                                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['active'] }}</span>
                                        </div>
                                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Template Aktif</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-flush py-4">
                                <div class="card-header pt-0">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                <span class="symbol-label bg-light-warning">
                                                    <i class="ki-outline ki-picture fs-2 text-warning"></i>
                                                </span>
                                            </div>
                                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['with_media'] }}</span>
                                        </div>
                                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Dengan Lampiran</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-flush py-4">
                                <div class="card-header pt-0">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                <span class="symbol-label bg-light-info">
                                                    <i class="ki-outline ki-tag fs-2 text-info"></i>
                                                </span>
                                            </div>
                                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['categories'] }}</span>
                                        </div>
                                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Kategori</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Table Area (Right 9 Columns)-->
                <div class="col-lg-9">
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <div class="card-body py-4">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_templates_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-150px">Judul Template</th>
                                        <th class="min-w-100px">Shortcut</th>
                                        <th class="min-w-100px">Kategori</th>
                                        <th class="min-w-250px">Konten</th>
                                        <th class="min-w-100px">Status</th>
                                        <th class="text-end min-w-100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    @forelse ($templates as $template)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6">{{ $template->title }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($template->shortcut)
                                                <span class="badge badge-light-warning">/{{ $template->shortcut }}</span>
                                            @else
                                                <span class="text-muted fs-7">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light-info">{{ $template->category ?: 'Umum' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($template->media_path)
                                                    <div class="symbol symbol-35px me-3">
                                                        <div class="symbol-label bg-light-{{ $template->media_type == 'image' ? 'success' : 'primary' }}">
                                                            <i class="ki-outline ki-{{ $template->media_type == 'image' ? 'picture' : 'file' }} fs-4 text-{{ $template->media_type == 'image' ? 'success' : 'primary' }}"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="text-gray-600 fs-7 text-truncate" style="max-width: 250px;">
                                                    {{ Str::limit($template->content, 100) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input h-20px w-30px toggle-status" type="checkbox" 
                                                       data-id="{{ $template->id }}" {{ $template->is_active ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.templates.edit', $template->id) }}" 
                                               class="btn btn-icon btn-light-primary btn-sm me-1">
                                                <i class="ki-outline ki-pencil fs-5"></i>
                                            </a>
                                            <button type="button" class="btn btn-icon btn-light-danger btn-sm btn-delete-template" 
                                                     data-id="{{ $template->id }}" data-title="{{ $template->title }}">
                                                <i class="ki-outline ki-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10 text-muted">
                                            Belum ada data template
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Col-->
            </div>

        </div>
    </div>

        </div>
    </div>
</x-metronic-layout>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Delete
        document.querySelectorAll('.btn-delete-template').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.dataset.id;
                var title = this.dataset.title;
                
                Swal.fire({
                    title: 'Hapus Template?',
                    text: 'Template "' + title + '" akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ url('admin/templates/delete') }}/" + id, { 
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

        // Handle Toggle Status
        document.querySelectorAll('.toggle-status').forEach(function(el) {
            el.addEventListener('change', function() {
                var id = this.dataset.id;
                fetch("{{ url('admin/templates/toggle-status') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            });
        });
    });
</script>
@endpush

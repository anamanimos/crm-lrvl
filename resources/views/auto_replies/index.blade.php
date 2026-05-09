<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Auto Reply
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Auto Reply</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('admin.auto-replies.create') }}" class="btn btn-sm fw-bold btn-primary">
                        <i class="ki-outline ki-plus fs-2"></i> Tambah Auto Reply
                    </a>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                @if(session('success'))
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Berhasil</h4>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
                @endif

                <div class="row g-5">
                    <!--begin::Left Column (Stats)-->
                    <div class="col-lg-3">
                        <div class="card card-flush mb-5 bg-light-primary border-0">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['total'] }}</span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Aturan</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0">
                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                        <div class="bg-primary rounded h-8px" role="progressbar" style="width: 100%;"></div>
                                    </div>
                                </div>
                                <i class="ki-outline ki-messages fs-2x text-primary opacity-25 position-absolute end-0 bottom-0 me-5 mb-5"></i>
                            </div>
                        </div>

                        <div class="card card-flush mb-5 bg-light-success border-0">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['active'] }}</span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Aturan Aktif</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0">
                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                        @php $activePercent = $stats['total'] > 0 ? ($stats['active'] / $stats['total']) * 100 : 0; @endphp
                                        <div class="bg-success rounded h-8px" role="progressbar" style="width: {{ $activePercent }}%;"></div>
                                    </div>
                                </div>
                                <i class="ki-outline ki-check-circle fs-2x text-success opacity-25 position-absolute end-0 bottom-0 me-5 mb-5"></i>
                            </div>
                        </div>

                        <div class="card card-flush mb-5 bg-light-danger border-0">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $stats['inactive'] }}</span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Aturan Non-aktif</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0">
                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                        @php $inactivePercent = $stats['total'] > 0 ? ($stats['inactive'] / $stats['total']) * 100 : 0; @endphp
                                        <div class="bg-danger rounded h-8px" role="progressbar" style="width: {{ $inactivePercent }}%;"></div>
                                    </div>
                                </div>
                                <i class="ki-outline ki-cross-circle fs-2x text-danger opacity-25 position-absolute end-0 bottom-0 me-5 mb-5"></i>
                            </div>
                        </div>
                    </div>
                    <!--end::Left Column-->

                    <!--begin::Right Column (Table)-->
                    <div class="col-lg-9">
                        <div class="card card-flush">
                            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <div class="card-title">
                                    <div class="d-flex align-items-center position-relative my-1">
                                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                                        <input type="text" id="table-search" class="form-control form-control-solid w-250px ps-12" placeholder="Cari auto reply..." />
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_auto_replies">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-150px">Nama / Aturan</th>
                                                <th class="min-w-100px">Trigger</th>
                                                <th class="min-w-150px">Keyword</th>
                                                <th class="min-w-100px">Lampiran</th>
                                                <th class="min-w-80px">Status</th>
                                                <th class="text-end min-w-100px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @foreach($replies as $reply)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <a href="{{ route('admin.auto-replies.edit', $reply->id) }}" class="text-gray-800 text-hover-primary fw-bold mb-1">{{ $reply->name }}</a>
                                                        <span class="text-muted fs-7">Dibuat {{ $reply->created_at->format('d M Y') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $triggerColors = [
                                                            'keyword' => 'badge-light-primary',
                                                            'first_chat' => 'badge-light-info',
                                                            'all' => 'badge-light-warning'
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $triggerColors[$reply->trigger_type] ?? 'badge-light' }} fw-bold">
                                                        {{ $reply->trigger_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($reply->trigger_type == 'keyword')
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach(explode(',', $reply->keyword) as $kw)
                                                                <span class="badge badge-secondary fs-8">{{ trim($kw) }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted fs-7 italic">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($reply->media_path)
                                                        <span class="badge badge-light-info fw-bold">
                                                            <i class="ki-outline {{ $reply->media_type == 'image' ? 'ki-picture' : 'ki-file' }} fs-7 text-info me-1"></i>
                                                            {{ ucfirst($reply->media_type) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted fs-7 italic">Tidak ada</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input h-20px w-30px toggle-status" type="checkbox" 
                                                               data-id="{{ $reply->id }}" {{ $reply->is_active ? 'checked' : '' }} />
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                        Aksi <i class="ki-outline ki-down fs-5 ms-1"></i>
                                                    </a>
                                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                        <div class="menu-item px-3">
                                                            <a href="{{ route('admin.auto-replies.edit', $reply->id) }}" class="menu-link px-3">Edit</a>
                                                        </div>
                                                        <div class="menu-item px-3">
                                                            <a href="javascript:void(0)" class="menu-link px-3 text-danger btn-delete" data-id="{{ $reply->id }}" data-url="{{ route('admin.auto-replies.delete', $reply->id) }}">Hapus</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Right Column-->
                </div>

            </div>
        </div>
        <!--end::Content-->
    </div>

    @push('js')
    <script>
        $(document).ready(function() {
            // Search
            $('#table-search').on('keyup', function() {
                const val = $(this).val().toLowerCase();
                $("#kt_table_auto_replies tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1)
                });
            });

            // Toggle Status
            $('.toggle-status').on('change', function() {
                const id = $(this).data('id');
                const url = `{{ url('admin/auto-replies') }}/${id}/toggle-status`;
                
                $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.success) {
                        toastr.success('Status berhasil diperbarui');
                    }
                });
            });

            // Delete
            $('.btn-delete').on('click', function() {
                const id = $(this).data('id');
                const url = $(this).data('url');
                
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Auto reply ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(url, { _token: '{{ csrf_token() }}' }, function(res) {
                            if (res.success) {
                                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-metronic-layout>

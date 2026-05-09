<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Pengaturan Tahapan Deal
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('deals.index') }}" class="text-muted text-hover-primary">Deals</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Pengaturan Tahapan</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('deals.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ki-outline ki-arrow-left fs-2"></i> Kembali ke Pipeline
                    </a>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <div class="row g-5">
                    <div class="col-md-8">
                        <div class="card card-flush">
                            <div class="card-header pt-7">
                                <h3 class="card-title fw-bold">Daftar Tahapan</h3>
                                <div class="card-toolbar">
                                    <span class="text-muted fs-7 fw-semibold">Tarik dan lepas untuk merubah urutan</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="stages-list" class="d-flex flex-column gap-3">
                                    @foreach($stages as $stage)
                                        <div class="card card-bordered p-4 cursor-move stage-item" data-id="{{ $stage->id }}">
                                            <div class="d-flex flex-stack">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-abstract-14 fs-3 text-muted me-4"></i>
                                                    <span class="bullet bullet-dot w-10px h-10px me-3" style="background-color: {{ $stage->color }}"></span>
                                                    <span class="fw-bold fs-6 text-gray-800">{{ $stage->name }}</span>
                                                    <span class="badge badge-light-{{ $stage->stage_type == 'won' ? 'success' : ($stage->stage_type == 'lost' ? 'danger' : 'primary') }} fs-9 ms-3">{{ ucfirst($stage->stage_type) }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button class="btn btn-icon btn-sm btn-light-primary btn-edit" 
                                                            data-id="{{ $stage->id }}" 
                                                            data-name="{{ $stage->name }}" 
                                                            data-color="{{ $stage->color }}"
                                                            data-type="{{ $stage->stage_type }}">
                                                        <i class="ki-outline ki-pencil fs-4"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-sm btn-light-danger btn-delete" data-id="{{ $stage->id }}">
                                                        <i class="ki-outline ki-trash fs-4"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-flush">
                            <div class="card-header pt-7">
                                <h3 class="card-title fw-bold">Tambah Tahapan</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('deals.stages.store') }}" method="POST">
                                    @csrf
                                    <div class="fv-row mb-5">
                                        <label class="form-label fw-bold">Nama Tahapan</label>
                                        <input type="text" name="name" class="form-control form-control-solid" placeholder="Contoh: Produksi" required />
                                    </div>
                                    <div class="fv-row mb-5">
                                        <label class="form-label fw-bold">Warna</label>
                                        <input type="color" name="color" class="form-control form-control-solid h-40px" value="#009ef7" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="form-label fw-bold">Tipe</label>
                                        <select name="stage_type" class="form-select form-select-solid">
                                            <option value="pipeline">Pipeline (Standard)</option>
                                            <option value="won">Won (Berhasil)</option>
                                            <option value="lost">Lost (Gagal)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Simpan Tahapan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--end::Content-->
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="modal-edit-stage" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-edit-stage">
                    <div class="modal-header">
                        <h2 class="fw-bold">Edit Tahapan</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold">Nama Tahapan</label>
                            <input type="text" name="name" id="edit-name" class="form-control form-control-solid" required />
                        </div>
                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold">Warna</label>
                            <input type="color" name="color" id="edit-color" class="form-control form-control-solid h-40px" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="form-label fw-bold">Tipe</label>
                            <select name="stage_type" id="edit-type" class="form-select form-select-solid">
                                <option value="pipeline">Pipeline (Standard)</option>
                                <option value="won">Won (Berhasil)</option>
                                <option value="lost">Lost (Gagal)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sortable
            const list = document.getElementById('stages-list');
            new Sortable(list, {
                animation: 150,
                onEnd: function() {
                    const order = [];
                    $('.stage-item').each(function() {
                        order.push($(this).data('id'));
                    });
                    $.post('{{ route("deals.stages.reorder") }}', {
                        _token: '{{ csrf_token() }}',
                        order: order
                    }, function(res) {
                        if (res.success) toastr.success('Urutan diperbarui');
                    });
                }
            });

            // Edit
            $('.btn-edit').on('click', function() {
                $('#edit-id').val($(this).data('id'));
                $('#edit-name').val($(this).data('name'));
                $('#edit-color').val($(this).data('color'));
                $('#edit-type').val($(this).data('type'));
                $('#modal-edit-stage').modal('show');
            });

            $('#form-edit-stage').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit-id').val();
                $.post(`{{ url('deals/settings/stages/update') }}/${id}`, $(this).serialize() + '&_token={{ csrf_token() }}', function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        location.reload();
                    }
                });
            });

            // Delete
            $('.btn-delete').on('click', function() {
                const id = $(this).data('id');
                if (confirm('Yakin hapus tahapan ini?')) {
                    $.post(`{{ url('deals/settings/stages/delete') }}/${id}`, { _token: '{{ csrf_token() }}' }, function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            location.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    });
                }
            });
        });
    </script>
    <style>
        .cursor-move { cursor: move; }
    </style>
    @endpush
</x-metronic-layout>

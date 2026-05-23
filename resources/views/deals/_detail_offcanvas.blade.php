<div class="row g-5">
    <!-- Info -->
    <div class="col-12">
        <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
            <div class="fw-bold text-gray-600">ID Deal</div>
            <div class="text-gray-800 text-end">#{{ $deal->id }}</div>
        </div>
        <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
            <div class="fw-bold text-gray-600">Pelanggan</div>
            <div class="text-gray-800 text-end">
                <a href="{{ route('admin.customers.show', $deal->customer_id) }}" target="_blank" class="text-hover-primary fw-bold text-gray-800">{{ $deal->customer->name }}</a>
            </div>
        </div>
        <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
            <div class="fw-bold text-gray-600">Status</div>
            <div class="text-gray-800 text-end">
                <span class="badge fw-bold" style="background-color: {{ $deal->stage->color }}; color: white;">{{ $deal->stage->name }}</span>
            </div>
        </div>
        <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
            <div class="fw-bold text-gray-600">Nilai</div>
            <div class="text-gray-900 text-end d-flex align-items-center justify-content-end gap-2">
                <form action="{{ route('deals.update-value') }}" method="POST" id="form-update-value-offcanvas" class="d-flex align-items-center m-0">
                    @csrf
                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">
                    <div class="input-group input-group-sm w-200px">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="expected_value" class="form-control form-control-solid text-end fw-boldest" value="{{ $deal->expected_value }}" />
                        <button type="submit" class="btn btn-icon btn-light-primary" title="Simpan Nilai"><i class="ki-outline ki-check fs-4"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
            <div class="fw-bold text-gray-600">Assigned To</div>
            <div class="text-gray-800 text-end">{{ $deal->assignedUser->name ?? '-' }}</div>
        </div>
        <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
            <div class="fw-bold text-gray-600">Dibuat Pada</div>
            <div class="text-gray-800 text-end">{{ $deal->created_at->format('d M Y H:i') }}</div>
        </div>
    </div>

    <!-- Update Tahapan -->
    <div class="col-12 mt-5">
        <h4 class="fw-bold text-gray-800 mb-3">Update Tahapan</h4>
        <form action="{{ route('deals.update-stage') }}" method="POST" id="form-update-stage-offcanvas">
            @csrf
            <input type="hidden" name="deal_id" value="{{ $deal->id }}">
            <div class="d-flex gap-2">
                <select name="stage_id" class="form-select form-select-solid flex-grow-1" data-control="select2" data-dropdown-parent="#kt_offcanvas_deal_detail">
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ $deal->deal_stage_id == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>

    <!-- Riwayat Aktivitas -->
    <div class="col-12 mt-8">
        <h4 class="fw-bold text-gray-800 mb-3">Riwayat Aktivitas</h4>
        <div class="mb-5">
            <form action="{{ route('deals.activity', $deal->id) }}" method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded-3" id="form-add-activity-offcanvas">
                @csrf
                <div class="fv-row mb-3">
                    <textarea name="description" class="form-control form-control-flush border-0 bg-transparent fs-6 px-0" rows="2" placeholder="Tulis catatan atau update di sini..."></textarea>
                </div>
                <div class="d-flex flex-stack">
                    <div class="d-flex align-items-center me-2">
                        <label class="btn btn-icon btn-sm btn-active-color-primary me-1" title="Lampirkan File">
                            <i class="ki-outline ki-paper-clip fs-2"></i>
                            <input type="file" name="media_file" class="d-none" onchange="document.getElementById('selected-filename-offcanvas').innerText = this.files[0].name" />
                        </label>
                        <span id="selected-filename-offcanvas" class="text-muted fs-8 fw-semibold"></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Tambah</button>
                </div>
            </form>
        </div>

        <div class="timeline-label">
            @forelse($deal->activities as $activity)
                <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-8 w-50px">{{ $activity->created_at->format('H:i') }}</div>
                    <div class="timeline-badge">
                        <i class="fa fa-genderless {{ $activity->activity_type == 'stage_change' ? 'text-warning' : ($activity->activity_type == 'file' ? 'text-info' : 'text-success') }} fs-1"></i>
                    </div>
                    <div class="timeline-content d-flex">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-stack mb-1">
                                <span class="text-muted fs-8">{{ $activity->created_at->format('d M Y') }} - {{ $activity->creator->name }}</span>
                                @if($activity->activity_type == 'stage_change')
                                    <span class="badge badge-light-warning fs-9">Update Tahapan</span>
                                @endif
                            </div>
                            <div class="text-gray-800 fs-6">
                                {!! nl2br(strip_tags($activity->description, '<strong><b><i><em><br><a>')) !!}
                            </div>
                            @if($activity->file_data)
                                <div class="mt-3 p-3 bg-light rounded d-flex align-items-center">
                                    @if(($activity->file_data['type'] ?? '') == 'image')
                                        <a href="{{ $activity->file_data['url'] }}" target="_blank">
                                            <img src="{{ $activity->file_data['url'] }}" class="w-75px h-75px object-fit-cover rounded me-3" />
                                        </a>
                                    @else
                                        <i class="ki-outline ki-file fs-2x text-primary me-3"></i>
                                    @endif
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-7 text-truncate" style="max-width: 150px;">{{ $activity->file_data['name'] ?? 'File Attachment' }}</span>
                                        <a href="{{ $activity->file_data['url'] }}" target="_blank" class="text-primary fs-9 fw-bold">Download</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <span class="text-muted">Belum ada aktivitas</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    $('#form-update-stage-offcanvas select[data-control="select2"]').select2();
    
    $('#form-update-stage-offcanvas').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.attr('disabled', true);
        
        $.post(form.attr('action'), form.serialize(), function(res) {
            if(res.success) {
                toastr.success(res.message);
                if (typeof loadBoard === 'function') loadBoard();
                openDealDetail('{{ $deal->uuid }}');
            } else {
                toastr.error(res.message);
                btn.attr('disabled', false);
            }
        }).fail(function() {
            toastr.error('Terjadi kesalahan.');
            btn.attr('disabled', false);
        });
    });

    $('#form-update-value-offcanvas').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.attr('disabled', true);
        
        $.post(form.attr('action'), form.serialize(), function(res) {
            if(res.success) {
                toastr.success(res.message || 'Nilai diperbarui');
                if (typeof loadBoard === 'function') loadBoard();
                openDealDetail('{{ $deal->uuid }}');
            } else {
                toastr.error(res.message);
                btn.attr('disabled', false);
            }
        }).fail(function() {
            toastr.error('Terjadi kesalahan.');
            btn.attr('disabled', false);
        });
    });

    $('#form-add-activity-offcanvas').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.attr('disabled', true);
        
        var formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(res.success) {
                    toastr.success(res.message);
                    openDealDetail('{{ $deal->uuid }}');
                } else {
                    toastr.error(res.message || 'Terjadi kesalahan');
                    btn.attr('disabled', false);
                }
            },
            error: function(err) {
                toastr.error('Terjadi kesalahan.');
                btn.attr('disabled', false);
            }
        });
    });
</script>

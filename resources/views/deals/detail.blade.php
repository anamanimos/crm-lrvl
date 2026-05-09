<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Detail Deal: {{ $deal->title }}
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('deals.index') }}" class="text-muted text-hover-primary">Deals</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Detail</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('deals.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ki-outline ki-arrow-left fs-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <div class="row g-5 g-xl-10">
                    <!--begin::Left Column (Info)-->
                    <div class="col-xl-4">
                        <div class="card card-flush mb-5">
                            <div class="card-header pt-5">
                                <h3 class="card-title fw-bold">Informasi Deal</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
                                    <div class="fw-bold text-gray-600">ID Deal</div>
                                    <div class="text-gray-800 text-end">#{{ $deal->id }}</div>
                                </div>
                                <div class="d-flex flex-stack fs-6 py-3 border-bottom border-gray-200 border-bottom-dashed">
                                    <div class="fw-bold text-gray-600">Pelanggan</div>
                                    <div class="text-gray-800 text-end">
                                        <a href="{{ route('admin.customers.show', $deal->customer_id) }}" class="text-hover-primary fw-bold text-gray-800">{{ $deal->customer->name }}</a>
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
                                    <div class="text-gray-900 fw-boldest text-end">Rp {{ number_format($deal->expected_value) }}</div>
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
                        </div>

                        <!-- Update Stage Card -->
                        <div class="card card-flush">
                            <div class="card-header pt-5">
                                <h3 class="card-title fw-bold">Update Tahapan</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('deals.update-stage') }}" method="POST" id="form-update-stage">
                                    @csrf
                                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">
                                    <div class="fv-row mb-5">
                                        <select name="stage_id" class="form-select form-select-solid" data-control="select2">
                                            @foreach($stages as $stg)
                                                <option value="{{ $stg->id }}" {{ $deal->deal_stage_id == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Update Tahapan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!--begin::Right Column (Timeline)-->
                    <div class="col-xl-8">
                        <div class="card card-flush h-lg-100">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-800">Riwayat Aktivitas</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Catatan dan log perubahan untuk deal ini</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Add Activity Form -->
                                <div class="mb-10">
                                    <form action="{{ route('deals.activity', $deal->id) }}" method="POST" enctype="multipart/form-data" class="bg-light p-5 rounded-3">
                                        @csrf
                                        <div class="fv-row mb-3">
                                            <textarea name="description" class="form-control form-control-flush border-0 bg-transparent fs-6" rows="3" placeholder="Tulis catatan atau update di sini..."></textarea>
                                        </div>
                                        <div class="d-flex flex-stack">
                                            <div class="d-flex align-items-center me-2">
                                                <label class="btn btn-icon btn-sm btn-active-color-primary me-1" title="Lampirkan File">
                                                    <i class="ki-outline ki-paper-clip fs-2"></i>
                                                    <input type="file" name="media_file" class="d-none" onchange="document.getElementById('selected-filename').innerText = this.files[0].name" />
                                                </label>
                                                <span id="selected-filename" class="text-muted fs-8 fw-semibold"></span>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm px-5">Tambah Catatan</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Timeline -->
                                <div class="timeline-label">
                                    @forelse($deal->activities as $activity)
                                        <div class="timeline-item">
                                            <div class="timeline-label fw-bold text-gray-800 fs-8 w-75px">{{ $activity->created_at->format('H:i') }}</div>
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
                                                        {!! nl2br(e($activity->description)) !!}
                                                    </div>
                                                    @if($activity->file_data)
                                                        <div class="mt-3 p-3 bg-light rounded d-flex align-items-center">
                                                            @if(($activity->file_data['type'] ?? '') == 'image')
                                                                <a href="{{ $activity->file_data['url'] }}" target="_blank">
                                                                    <img src="{{ $activity->file_data['url'] }}" class="w-100px h-100px object-fit-cover rounded me-3" />
                                                                </a>
                                                            @else
                                                                <i class="ki-outline ki-file fs-2x text-primary me-3"></i>
                                                            @endif
                                                            <div class="d-flex flex-column">
                                                                <span class="text-gray-800 fw-bold fs-7">{{ $activity->file_data['name'] ?? 'File Attachment' }}</span>
                                                                <a href="{{ $activity->file_data['url'] }}" target="_blank" class="text-primary fs-9 fw-bold">Download File</a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-10">
                                            <span class="text-muted">Belum ada aktivitas</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--end::Content-->
    </div>
</x-metronic-layout>

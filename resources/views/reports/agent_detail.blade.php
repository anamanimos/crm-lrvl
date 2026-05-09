<x-metronic-layout :title="'Detail Performa Agen: ' . $agent->name">

    @push('css')
    <style>
    </style>
    @endpush

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $title }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.reports.daily', ['date' => $date]) }}" class="text-muted text-hover-primary">Laporan Harian</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Detail Agen</li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <form action="{{ route('admin.reports.daily.agent', $agent->id) }}" method="GET" class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        <i class="ki-outline ki-calendar-8 fs-2 position-absolute top-50 translate-middle-y ms-3"></i>
                        <input class="form-control form-control-sm form-control-solid ps-10" name="date" value="{{ $date }}" type="date" onchange="this.form.submit()" />
                    </div>
                </form>
                <a href="{{ route('admin.reports.daily', ['date' => $date]) }}" class="btn btn-sm btn-secondary">
                    <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid pb-10">
            
            <div class="row g-5 g-xl-10">
                <!-- Kolom Kiri: Ringkasan & Aktivitas -->
                <div class="col-xl-4">
                    <!-- User Profile Card -->
                    <div class="card card-flush mb-5">
                        <div class="card-body pt-9">
                            <div class="d-flex flex-center flex-column mb-5">
                                <div class="symbol symbol-100px symbol-circle mb-7">
                                    <div class="symbol-label fs-1 fw-bold bg-light-primary text-primary">{{ substr($agent->name, 0, 1) }}</div>
                                </div>
                                <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ $agent->name }}</a>
                                <div class="fs-5 fw-semibold text-muted mb-6">Agen</div>
                                
                                <div class="d-flex flex-wrap flex-center">
                                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mx-4 mb-3">
                                        <div class="fs-4 fw-bold text-gray-700 text-center">{{ $chatsOut->count() }}</div>
                                        <div class="fw-semibold text-muted">Chat Keluar</div>
                                    </div>
                                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mx-4 mb-3">
                                        <div class="fs-4 fw-bold text-gray-700 text-center">{{ $deals->count() }}</div>
                                        <div class="fw-semibold text-muted">Deals Baru</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aktivitas Terbaru -->
                    <div class="card card-flush mb-5">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Log Aktivitas Agen</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Riwayat tindakan pada tanggal ini</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <div class="timeline-label">
                                @forelse ($activities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-label fw-bold text-gray-800 fs-7">{{ $activity->created_at->format('H:i') }}</div>
                                    <div class="timeline-badge">
                                        <i class="fa fa-genderless {{ $activity->activity_type == 'stage_change' ? 'text-success' : 'text-primary' }} fs-1"></i>
                                    </div>
                                    <div class="fw-mormal timeline-content text-muted ps-3">
                                        @if ($activity->activity_type == 'stage_change')
                                            Mengubah stage <strong>{{ $activity->deal->title ?? 'Deal' }}</strong>
                                        @else
                                            Menambahkan catatan pada <strong>{{ $activity->deal->title ?? 'Deal' }}</strong>
                                        @endif
                                        <div class="fs-8 text-gray-400">{{ $activity->deal->customer->name ?? 'Unknown Customer' }}</div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-10">Tidak ada aktivitas tercatat.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Detail Chat & Deals -->
                <div class="col-xl-8">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_tab_chats">Daftar Chat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_tab_deals">Daftar Deals</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Chat Tab -->
                        <div class="tab-pane fade show active" id="kt_tab_chats">
                            <div class="card card-flush">
                                <div class="card-header pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark">Interaksi Pelanggan</span>
                                        <span class="text-muted mt-1 fw-semibold fs-7">Daftar chat yang ditangani hari ini</span>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                    <th>Pelanggan</th>
                                                    <th class="text-center">Status Respon</th>
                                                    <th class="text-end">Terakhir Chat</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-semibold">
                                                @forelse ($chatsIn->pluck('customer')->merge($chatsOut->pluck('customer'))->unique('id')->filter() as $customer)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-40px me-3">
                                                                <div class="symbol-label bg-light-info text-info fw-bold">{{ substr($customer->name, 0, 1) }}</div>
                                                            </div>
                                                            <div class="d-flex flex-column">
                                                                <span class="text-gray-800 fw-bold text-hover-primary mb-1">{{ $customer->name }}</span>
                                                                <span class="text-muted fs-7">{{ $customer->wa_number }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($chatsOut->where('customer_id', $customer->id)->isNotEmpty())
                                                            <span class="badge badge-light-success">Sudah Dibalas</span>
                                                        @else
                                                            <span class="badge badge-light-danger">Belum Dibalas</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @php
                                                            $lastChat = $chatsIn->where('customer_id', $customer->id)->merge($chatsOut->where('customer_id', $customer->id))->sortByDesc('created_at')->first();
                                                        @endphp
                                                        {{ $lastChat ? $lastChat->created_at->format('H:i') : '-' }}
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-10">Tidak ada data chat.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deals Tab -->
                        <div class="tab-pane fade" id="kt_tab_deals">
                            <div class="card card-flush">
                                <div class="card-header pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark">Deals Baru</span>
                                        <span class="text-muted mt-1 fw-semibold fs-7">Prospek yang dibuat oleh agen hari ini</span>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                    <th>Judul Deal</th>
                                                    <th>Pelanggan</th>
                                                    <th class="text-end">Nilai</th>
                                                    <th class="text-end">Waktu</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-semibold">
                                                @forelse ($deals as $deal)
                                                <tr>
                                                    <td>
                                                        <span class="text-gray-800 fw-bold">{{ $deal->title }}</span>
                                                    </td>
                                                    <td>{{ $deal->customer->name ?? 'Unknown' }}</td>
                                                    <td class="text-end">Rp {{ number_format($deal->expected_value, 0, ',', '.') }}</td>
                                                    <td class="text-end">{{ $deal->created_at->format('H:i') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-10">Tidak ada deal baru.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-metronic-layout>

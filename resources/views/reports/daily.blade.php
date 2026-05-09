<x-metronic-layout>
    @php
        $title = 'Laporan Harian';
    @endphp

    @push('css')
    <style>
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important; }
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
                    <li class="breadcrumb-item text-muted">Report</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Laporan Harian</li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <form action="{{ route('admin.reports.daily') }}" method="GET" class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        <i class="ki-outline ki-calendar-8 fs-2 position-absolute top-50 translate-middle-y ms-3"></i>
                        <input class="form-control form-control-sm form-control-solid ps-10" name="date" value="{{ $date }}" type="date" onchange="this.form.submit()" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            @if ($isTemporaryToday)
            <div class="alert alert-primary d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-chart-line fs-2hx text-primary me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-primary">Data Sementara (Hari Ini)</h4>
                    <span>Menampilkan performa real-time hari ini. Metrik <strong>Missed Chat</strong> akan tetap 0 sampai jam kerja berakhir ({{ $businessHours['end'] }}).</span>
                </div>
            </div>
            @endif

            @if (!$isWorkingDay)
            <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-information-5 fs-2hx text-warning me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Hari Libur / Di Luar Hari Kerja</h4>
                    <span>Tanggal ini bukan hari kerja aktif. Metrik <strong>Missed Chat</strong> tidak dihitung pada hari ini.
                    <a href="{{ route('settings.section', 'general') }}" class="text-primary fw-bold">Ubah Pengaturan Jam Kerja</a></span>
                </div>
            </div>
            @endif

            <div class="d-flex align-items-center mb-5">
                <span class="badge badge-light-info fs-7 me-3">
                    <i class="ki-outline ki-time fs-6 me-1"></i>
                    Jam Kerja: {{ $businessHours['start'] }} - {{ $businessHours['end'] }}
                </span>
                <a href="{{ route('settings.section', 'general') }}" class="text-muted text-hover-primary fs-8">
                    <i class="ki-outline ki-pencil fs-7 me-1"></i>Ubah
                </a>
            </div>

            <!--begin::Row 1: Kualitas & Performa CS-->
            <div class="mb-5">
                <h3 class="text-gray-800 fw-bold mb-4">Kualitas & Performa CS</h3>
                <div class="row g-5 g-xl-8">
                    <!--begin::Col-->
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-light-primary border-primary border-dashed stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-primary bg-opacity-10 text-primary">
                                            <i class="ki-outline ki-message-text-2 fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Chat Masuk</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-1 fw-bold text-gray-900">{{ number_format($chatIn) }}</span>
                                    <span class="badge {{ $chatInTrend >= 0 ? 'badge-light-success' : 'badge-light-danger' }} fs-base">
                                        <i class="ki-outline {{ $chatInTrend >= 0 ? 'ki-arrow-up text-success' : 'ki-arrow-down text-danger' }} fs-5 ms-n1"></i>
                                        {{ abs(round($chatInTrend, 1)) }}%
                                    </span>
                                </div>
                                <div class="text-muted fw-semibold fs-7 mt-2">vs Kemarin</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                    
                    <!--begin::Col-->
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-light-success border-success border-dashed stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-success bg-opacity-10 text-success">
                                            <i class="ki-outline ki-send fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Chat Keluar</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-1 fw-bold text-gray-900">{{ number_format($chatOut) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-light-danger border-danger border-dashed stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-danger bg-opacity-10 text-danger">
                                            <i class="ki-outline ki-cross-square fs-2 text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Chat Tak Dibalas / Missed</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-1 fw-bold text-gray-900">{{ number_format($missedChats) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                    
                    <!--begin::Col-->
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-light-warning border-warning border-dashed stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-warning bg-opacity-10 text-warning">
                                            <i class="ki-outline ki-timer fs-2 text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">Rata-rata Waktu Balas</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-3 fw-bold text-gray-900">
                                        {{ $avgResponseTime < 60 ? round($avgResponseTime) . ' Detik' : round($avgResponseTime / 60, 1) . ' Menit' }}
                                    </span>
                                </div>
                                <div class="text-muted fw-semibold fs-7 mt-2">First Response Time: 
                                    <span class="text-gray-800 fw-bold">{{ $avgFRT < 60 ? round($avgFRT) . ' dtk' : round($avgFRT / 60, 1) . ' mnt' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Col-->
                </div>
            </div>
            <!--end::Row 1-->

            <!--begin::Row 2: Pipeline, Customer, Trends-->
            <div class="row g-5 g-xl-8 mb-5">
                <!--begin::Pipeline-->
                <div class="col-xl-6">
                    <h3 class="text-gray-800 fw-bold mb-4">Pipeline & Sales</h3>
                    <div class="row g-5">
                        <div class="col-6">
                            <div class="card card-bordered h-100 stat-card">
                                <div class="card-body text-center pb-5">
                                    <i class="ki-outline ki-abstract-26 fs-3x text-primary d-block mb-3"></i>
                                    <span class="text-gray-900 fw-bold fs-1">{{ number_format($newDealsCount) }}</span>
                                    <span class="text-muted fw-semibold fs-6 d-block">Deals Baru Dibuat</span>
                                    <div class="mt-3">
                                        <span class="badge {{ $dealsTrend >= 0 ? 'badge-light-success' : 'badge-light-danger' }}">
                                            {{ $dealsTrend >= 0 ? '+' : '' }}{{ round($dealsTrend, 1) }}% vs kemarin
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card card-bordered h-100 stat-card">
                                <div class="card-body text-center pb-5">
                                    <i class="ki-outline ki-dollar fs-3x text-success d-block mb-3"></i>
                                    <span class="text-gray-900 fw-bold fs-1">Rp {{ number_format($newDealsValue, 0, ',', '.') }}</span>
                                    <span class="text-muted fw-semibold fs-6 d-block">Nilai Total Deals Baru</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-bordered h-100 stat-card bg-light">
                                <div class="card-body text-center p-4">
                                    <span class="text-gray-900 fw-bold fs-2">{{ number_format($statusChanges) }}</span>
                                    <span class="text-muted fw-semibold fs-7 d-block">Perubahan Status Deals</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-bordered h-100 stat-card bg-light">
                                <div class="card-body text-center p-4">
                                    <span class="text-gray-900 fw-bold fs-2">{{ number_format($followUps) }}</span>
                                    <span class="text-muted fw-semibold fs-7 d-block">Jumlah Follow-up</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-bordered h-100 stat-card bg-light">
                                <div class="card-body text-center p-4">
                                    <span class="text-gray-900 fw-bold fs-2">{{ round($conversionRate, 1) }}%</span>
                                    <span class="text-muted fw-semibold fs-7 d-block">Konversi Chat → Deal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Pipeline-->

                <!--begin::Konteks & Customer-->
                <div class="col-xl-6">
                    <h3 class="text-gray-800 fw-bold mb-4">Konteks & Customer</h3>
                    
                    <div class="card card-bordered mb-5">
                        <div class="card-body d-flex align-items-center py-5">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label bg-light-info text-info">
                                    <i class="ki-outline ki-time fs-1"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-grow-1">
                                <span class="text-gray-800 fw-bold fs-4">Jam Paling Ramai (Peak Hour)</span>
                                <span class="text-muted fw-semibold fs-6">Waktu interaksi terbanyak pelanggan</span>
                            </div>
                            <span class="badge badge-lg badge-info fs-3 fw-bold">{{ $peakHour }}</span>
                        </div>
                    </div>

                    <div class="card card-bordered">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Demografi Pelanggan (Chat Hari Ini)</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-stack mt-5">
                                <div class="d-flex align-items-center me-5">
                                    <div class="symbol symbol-40px me-4">
                                        <div class="symbol-label bg-light-success">
                                            <i class="ki-outline ki-user-tick fs-2 text-success"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-5">Pelanggan Baru</span>
                                        <span class="text-muted fw-semibold fs-7">Terdaftar hari ini</span>
                                    </div>
                                </div>
                                <span class="text-gray-900 fw-bold fs-3">{{ number_format($newCustomersCount) }}</span>
                            </div>
                            <div class="separator separator-dashed my-4"></div>
                            <div class="d-flex flex-stack">
                                <div class="d-flex align-items-center me-5">
                                    <div class="symbol symbol-40px me-4">
                                        <div class="symbol-label bg-light-primary">
                                            <i class="ki-outline ki-profile-user fs-2 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-5">Pelanggan Lama</span>
                                        <span class="text-muted fw-semibold fs-7">Terdaftar sebelum hari ini</span>
                                    </div>
                                </div>
                                <span class="text-gray-900 fw-bold fs-3">{{ number_format($oldCustomersCount) }}</span>
                            </div>
                        </div>
                    </div>

                </div>
                <!--end::Konteks & Customer-->
            </div>
            <!--end::Row 2-->

            <!--begin::Row 3: Performa per CS-->
            <h3 class="text-gray-800 fw-bold mb-4 mt-8">Performa per CS (Agen)</h3>
            <div class="card card-bordered">
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th class="min-w-150px">Nama CS (Agen)</th>
                                    <th class="text-center">Chat Masuk</th>
                                    <th class="text-center">Chat Dibalas</th>
                                    <th class="text-center text-danger">Missed</th>
                                    <th class="text-center">Avg Response</th>
                                    <th class="text-center">Deals Baru</th>
                                    <th class="text-center">Nilai Deals</th>
                                    <th class="text-center">Follow-up</th>
                                    <th class="text-center">Konversi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse ($agentPerformance as $agent)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.reports.daily.agent', ['id' => $agent['user_id'], 'date' => $date]) }}" class="d-flex align-items-center text-gray-800 text-hover-primary">
                                            <div class="symbol symbol-circle symbol-30px me-3">
                                                <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                    {{ substr($agent['name'], 0, 1) }}
                                                </div>
                                            </div>
                                            <span class="fw-bold">{{ $agent['name'] }}</span>
                                            <i class="ki-outline ki-arrow-right fs-9 ms-2"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($agent['chat_in']) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-success fw-bold">{{ number_format($agent['chat_out']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($agent['missed'] > 0)
                                            <span class="badge badge-light-danger fw-bold">{{ number_format($agent['missed']) }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($agent['avg_response_time'] < 60)
                                            {{ round($agent['avg_response_time']) }} dtk
                                        @else
                                            {{ round($agent['avg_response_time'] / 60, 1) }} mnt
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($agent['deals_count']) }}
                                    </td>
                                    <td class="text-center">
                                        Rp {{ number_format($agent['deals_value'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($agent['follow_ups']) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light-info">{{ round($agent['conversion_rate'], 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-8">Belum ada aktivitas CS pada tanggal ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Row 3-->

        </div>
    </div>
    <!--end::Content-->
</x-metronic-layout>

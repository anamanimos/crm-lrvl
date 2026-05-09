<x-metronic-layout>
    @php
        $title = 'Dashboard';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Dashboard
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <!--begin::Row - Statistics-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" 
                         style="background-color: #F1416C;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">
                                    {{ $stats['total_customers'] }}
                                </span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Customer</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">
                                    {{ $stats['messages_today']['total_in'] }}
                                </span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Pesan Masuk Hari Ini</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10" style="background-color: #50CD89;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">
                                    {{ $stats['messages_today']['total_out'] }}
                                </span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Pesan Keluar Hari Ini</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10" style="background-color: #009EF7;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">
                                    {{ $stats['total_deals'] }}
                                </span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Deals</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Funnel Pipeline-->
                <div class="col-xxl-6">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Deals Pipeline</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">Deals per tahapan</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            @if($stats['deal_stats']->isNotEmpty())
                                @foreach ($stats['deal_stats'] as $deal)
                                <div class="d-flex flex-stack mb-5">
                                    <div class="d-flex align-items-center me-2">
                                        <div class="symbol symbol-35px me-3">
                                            <div class="symbol-label" style="background-color: {{ $deal->color }}20;">
                                                <i class="ki-outline ki-abstract-26 fs-4" style="color: {{ $deal->color }}"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ url('deals?stage=' . $deal->id) }}" 
                                               class="fs-5 text-gray-800 text-hover-primary fw-bold">
                                                {{ $deal->name }}
                                            </a>
                                            <div class="fs-8 text-muted mt-n1">
                                                Rp {{ number_format($deal->deals_sum_expected_value ?? 0, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-light-primary fs-base fw-bold">
                                            {{ $deal->deals_count ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-10">
                                    Belum ada data deals
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row - Recent Customers-->
            <div class="row g-5 g-xl-10">
                <div class="col-xl-12">
                    <div class="card card-flush">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Customer Terbaru</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">10 customer dengan aktivitas terakhir</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light-primary">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-6">
                            @if($recent_customers->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th>CUSTOMER</th>
                                            <th>NOMOR WA</th>
                                            <th>TERAKHIR CHAT</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recent_customers as $customer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label fs-5 fw-semibold bg-light-primary text-primary">
                                                            {{ generate_initials($customer->name ?: $customer->wa_number) }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold">
                                                            {{ $customer->name ?: 'Tanpa Nama' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-600 fw-semibold">
                                                    {{ format_phone_display($customer->wa_number) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-gray-600">
                                                    {{ time_ago($customer->last_chat_at) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ url('chat?customer=' . $customer->id) }}" 
                                                   class="btn btn-sm btn-icon btn-light-success">
                                                    <i class="ki-outline ki-message-text-2 fs-4"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-10">
                                Belum ada data customer
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Row-->

        </div>
    </div>
</x-metronic-layout>

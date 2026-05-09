<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Broadcast
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Broadcast</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-sm btn-primary">
                        <i class="ki-outline ki-plus fs-2"></i>
                        Buat Broadcast
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
                        <!--begin::Stat: Total Broadcast-->
                        <div class="card card-flush mb-5 bg-light-primary border-0">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($globalStats['total_broadcasts']) }}</span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Broadcast</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0">
                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                        <div class="bg-primary rounded h-8px" role="progressbar" style="width: 100%;"></div>
                                    </div>
                                </div>
                                <i class="ki-outline ki-send fs-2x text-primary opacity-25 position-absolute end-0 bottom-0 me-5 mb-5"></i>
                            </div>
                        </div>
                        <!--end::Stat-->

                        <!--begin::Stat: Sent-->
                        <div class="card card-flush mb-5 bg-light-success border-0">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($globalStats['total_sent']) }}</span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Pesan Terkirim</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0">
                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                        <div class="bg-success rounded h-8px" role="progressbar" style="width: 100%;"></div>
                                    </div>
                                </div>
                                <i class="ki-outline ki-double-check fs-2x text-success opacity-25 position-absolute end-0 bottom-0 me-5 mb-5"></i>
                            </div>
                        </div>
                        <!--end::Stat-->

                        <!--begin::Stat: Pending-->
                        <div class="card card-flush mb-5 bg-light-warning border-0">
                            <div class="card-header pt-5">
                                <div class="card-title d-flex flex-column">
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($globalStats['total_pending']) }}</span>
                                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Antrian Pending</span>
                                </div>
                            </div>
                            <div class="card-body d-flex align-items-end pt-0">
                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                        <div class="bg-warning rounded h-8px" role="progressbar" style="width: 75%;"></div>
                                    </div>
                                </div>
                                <i class="ki-outline ki-time fs-2x text-warning opacity-25 position-absolute end-0 bottom-0 me-5 mb-5"></i>
                            </div>
                        </div>
                        <!--end::Stat-->
                    </div>
                    <!--end::Left Column-->

                    <!--begin::Right Column (Table)-->
                    <div class="col-lg-9">
                        <!--begin::Card-->
                        <div class="card card-flush">
                            <div class="card-body pt-10">
                                @if($broadcasts->isEmpty())
                                <div class="text-center py-20">
                                    <i class="ki-outline ki-send fs-5x text-gray-200 mb-5"></i>
                                    <h2 class="text-gray-700 fw-bold">Belum Ada Broadcast</h2>
                                    <p class="text-gray-500 fs-6 mb-8">Mulailah menjangkau pelanggan Anda dengan pesan broadcast WhatsApp.</p>
                                    <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-primary">Buat Broadcast Sekarang</a>
                                </div>
                                @else
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_broadcasts">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-150px">Nama Broadcast</th>
                                                <th class="min-w-100px">Target</th>
                                                <th class="min-w-100px">Status</th>
                                                <th class="min-w-200px">Progress</th>
                                                <th class="min-w-100px text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @foreach($broadcasts as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <a href="{{ route('admin.broadcasts.view', $item->id) }}" class="text-gray-800 text-hover-primary fw-bold mb-1 fs-6">{{ $item->name }}</a>
                                                        <span class="text-muted fs-7">{{ $item->created_at->format('d M Y H:i') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $targetBadges = [
                                                            'all' => 'badge-light-primary',
                                                            'label' => 'badge-light-info',
                                                            'funnel' => 'badge-light-warning',
                                                            'custom' => 'badge-light-success'
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $targetBadges[$item->target_type] ?? 'badge-light' }} fw-bold px-4 py-2">
                                                        {{ ucfirst($item->target_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $item->status_badge }} fw-bold px-4 py-3">{{ ucfirst($item->status) }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $total = max($item->stats['total'], 1);
                                                        $percent = round(($item->stats['sent'] / $total) * 100);
                                                        $color = $item->status == 'completed' ? 'success' : ($item->status == 'running' ? 'primary' : 'secondary');
                                                    @endphp
                                                    <div class="d-flex flex-column w-100 me-2">
                                                        <div class="d-flex flex-stack mb-2">
                                                            <span class="text-muted me-2 fs-7 fw-bold">{{ $percent }}%</span>
                                                        </div>
                                                        <div class="progress h-6px w-100 bg-light-{{ $color }}">
                                                            <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                                                 style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <div class="text-muted fs-8 mt-2">
                                                            <span class="text-gray-800 fw-bold">{{ number_format($item->stats['sent']) }}</span> terkirim dari {{ number_format($item->stats['total']) }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                        Aksi <i class="ki-outline ki-down fs-5 ms-1"></i>
                                                    </a>
                                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                        <div class="menu-item px-3">
                                                            <a href="{{ route('admin.broadcasts.view', $item->id) }}" class="menu-link px-3">Detail</a>
                                                        </div>
                                                        @if(in_array($item->status, ['draft', 'paused', 'cancelled']))
                                                        <div class="menu-item px-3">
                                                            <form action="{{ route('admin.broadcasts.delete', $item->id) }}" method="POST" id="delete-form-{{ $item->id }}">
                                                                @csrf
                                                                <a href="javascript:void(0)" class="menu-link px-3 text-danger" onclick="if(confirm('Yakin hapus broadcast ini?')) document.getElementById('delete-form-{{ $item->id }}').submit();">Hapus</a>
                                                            </form>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Right Column-->
                </div>
                <!--end::Card-->

            </div>
        </div>
        <!--end::Content-->
    </div>
</x-metronic-layout>

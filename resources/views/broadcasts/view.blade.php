<x-metronic-layout>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Detail Broadcast
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.broadcasts.index') }}" class="text-muted text-hover-primary">Broadcast</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">{{ $broadcast->name }}</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ki-outline ki-arrow-left fs-2"></i>
                        Kembali
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
                    <!--begin::Left Column (Stats & Control)-->
                    <div class="col-lg-4">
                        <!--begin::Stats Card-->
                        <div class="card card-flush mb-5">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Status Pengiriman</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Total {{ number_format($broadcast->stats['total']) }} penerima</span>
                                </h3>
                                <div class="card-toolbar">
                                    <span class="badge {{ $broadcast->status_badge }} fs-7 fw-bold" id="broadcast-status-badge">
                                        {{ ucfirst($broadcast->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-stack">
                                    <div class="text-gray-700 fw-semibold fs-6 me-2">Terkirim</div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-gray-900 fw-bold fs-6" id="stat-sent">{{ number_format($broadcast->stats['sent']) }}</span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                
                                <div class="d-flex flex-stack">
                                    <div class="text-gray-700 fw-semibold fs-6 me-2">Gagal</div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-danger fw-bold fs-6" id="stat-failed">{{ number_format($broadcast->stats['failed']) }}</span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                
                                <div class="d-flex flex-stack">
                                    <div class="text-gray-700 fw-semibold fs-6 me-2">Pending</div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted fw-bold fs-6" id="stat-pending">{{ number_format($broadcast->stats['pending']) }}</span>
                                    </div>
                                </div>
                                
                                <div class="mt-8">
                                    @php
                                        $total = max($broadcast->stats['total'], 1);
                                        $percent = round(($broadcast->stats['sent'] / $total) * 100);
                                    @endphp
                                    <div class="d-flex flex-column w-100 me-2 mb-2">
                                        <div class="d-flex flex-stack mb-2">
                                            <span class="text-muted me-2 fs-7 fw-bold" id="progress-text">{{ $percent }}%</span>
                                        </div>
                                        <div class="progress h-8px w-100 bg-light-primary">
                                            <div class="progress-bar bg-primary" role="progressbar" id="progress-bar"
                                                 style="width: {{ $percent }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-8">
                                    @if($broadcast->status == 'draft' || $broadcast->status == 'paused' || $broadcast->status == 'scheduled')
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.broadcasts.action', [$broadcast->id, 'start']) }}" class="btn btn-primary w-100">
                                            <i class="ki-outline ki-entrance-left fs-2"></i> Mulai
                                        </a>
                                        <a href="{{ route('admin.broadcasts.edit', $broadcast->id) }}" class="btn btn-light-primary w-100">
                                            <i class="ki-outline ki-pencil fs-2"></i> Edit
                                        </a>
                                    </div>
                                    @elseif($broadcast->status == 'running')
                                    <a href="{{ route('admin.broadcasts.action', [$broadcast->id, 'pause']) }}" class="btn btn-warning w-100 mb-4">
                                        <i class="ki-outline ki-pause fs-2"></i> Pause
                                    </a>
                                    <div class="alert alert-info d-flex align-items-center p-4 mb-0 border-0 bg-light-info shadow-sm">
                                        <span class="spinner-border spinner-border-sm text-info me-3"></span>
                                        <div class="fs-7 text-info fw-semibold">Sedang berjalan di background...</div>
                                    </div>
                                    @elseif($broadcast->status == 'completed')
                                    <button class="btn btn-success w-100" disabled>
                                        <i class="ki-outline ki-check fs-2"></i> Selesai
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!--end::Stats Card-->
                        
                        <!--begin::Info Card-->
                        <div class="card card-flush">
                            <div class="card-header pt-5">
                                <h3 class="card-title fw-bold">Konfigurasi</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-5">
                                    <span class="text-muted d-block fs-8 fw-bold text-uppercase">Target Audience</span>
                                    <span class="fw-bold text-gray-800 fs-6">
                                        {{ ucfirst($broadcast->target_type) }}
                                        @if($broadcast->target_type == 'label')
                                            @php $label = \App\Models\Label::find($broadcast->target_filters['label_id'] ?? null); @endphp
                                            ({{ $label->name ?? 'N/A' }})
                                        @endif
                                    </span>
                                </div>
                                <div class="mb-5">
                                    <span class="text-muted d-block fs-8 fw-bold text-uppercase">Delay Antar Pesan</span>
                                    <span class="fw-bold text-gray-800 fs-6">{{ $broadcast->delay_min }} - {{ $broadcast->delay_max }} detik</span>
                                </div>
                                <div class="mb-5">
                                    <span class="text-muted d-block fs-8 fw-bold text-uppercase">Dibuat Oleh</span>
                                    <span class="fw-bold text-gray-800 fs-6">{{ $broadcast->creator->name ?? '-' }}</span>
                                </div>
                                <div class="mb-5">
                                    <span class="text-muted d-block fs-8 fw-bold text-uppercase">Waktu Jadwal</span>
                                    <span class="fw-bold text-gray-800 fs-6">{{ $broadcast->scheduled_at ? $broadcast->scheduled_at->format('d M Y H:i') : 'Tanpa Jadwal' }}</span>
                                </div>
                                <div class="mb-0">
                                    <span class="text-muted d-block fs-8 fw-bold text-uppercase">Waktu Mulai</span>
                                    <span class="fw-bold text-gray-800 fs-6">{{ $broadcast->started_at ? $broadcast->started_at->format('d M Y H:i') : '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <!--end::Info Card-->
                    </div>
                    <!--end::Left Column-->
                    
                    <!--begin::Right Column (Template & Logs)-->
                    <div class="col-lg-8">
                        <div class="card card-flush h-lg-100">
                            <div class="card-header pt-7">
                                <h3 class="card-title fw-bold">Isi Pesan Template</h3>
                            </div>
                            <div class="card-body">
                                <div class="bg-light-primary p-7 rounded border border-primary border-dashed position-relative">
                                    @if($broadcast->media_path)
                                        <div class="mb-5">
                                            @if($broadcast->media_type == 'image')
                                                <img src="{{ Str::startsWith($broadcast->media_path, ['http://', 'https://']) ? $broadcast->media_path : asset('storage/' . $broadcast->media_path) }}" 
                                                     class="img-fluid rounded-2 shadow-sm mb-2" style="max-height: 250px;">
                                            @else
                                                <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                    <i class="ki-outline ki-file fs-2hx text-primary me-3"></i>
                                                    <div class="d-flex flex-column">
                                                        <span class="fs-7 fw-bold text-gray-800">Dokumen Lampiran</span>
                                                        <a href="{{ Str::startsWith($broadcast->media_path, ['http://', 'https://']) ? $broadcast->media_path : asset('storage/' . $broadcast->media_path) }}" 
                                                           target="_blank" class="fs-8 text-primary text-hover-underline">Lihat File</a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @php
                                        $templates = json_decode($broadcast->message_template, true);
                                    @endphp
                                    @if(is_array($templates) && count($templates) > 0)
                                        @foreach($templates as $index => $tmpl)
                                            <div class="mb-5">
                                                <span class="badge badge-primary mb-3">Pesan {{ $index + 1 }}</span>
                                                <div class="text-gray-800 fs-5 lh-lg whitespace-pre-wrap" style="white-space: pre-wrap;">{{ $tmpl }}</div>
                                            </div>
                                            @if(!$loop->last) <div class="separator separator-dashed border-primary my-5 opacity-25"></div> @endif
                                        @endforeach
                                    @else
                                        <div class="text-gray-800 fs-5 lh-lg whitespace-pre-wrap" style="white-space: pre-wrap;">{{ $broadcast->message_template }}</div>
                                    @endif
                                    <div class="position-absolute top-0 end-0 p-3">
                                        <i class="ki-outline ki-whatsapp text-success fs-2hx opacity-25"></i>
                                    </div>
                                </div>
                                
                                <div class="mt-10">
                                    <h4 class="fw-bold mb-5">Analisa Variabel</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm fs-7">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th class="p-2">Variabel</th>
                                                    <th class="p-2">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="p-2"><code>{name}</code></td>
                                                    <td class="p-2 text-muted">Akan diganti dengan nama pelanggan</td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2"><code>{wa_number}</code></td>
                                                    <td class="p-2 text-muted">Akan diganti dengan nomor WhatsApp pelanggan</td>
                                                </tr>
                                                <tr>
                                                    <td class="p-2"><code>{A|B|C}</code></td>
                                                    <td class="p-2 text-muted">Akan dipilih secara acak antara A, B, atau C (Spintax)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Right Column-->
            </div>
            
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card card-flush">
                        <div class="card-header pt-7">
                            <h3 class="card-title fw-bold">Daftar Penerima</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-150px">Pelanggan</th>
                                            <th class="min-w-100px">Status</th>
                                            <th class="min-w-150px">Waktu Kirim</th>
                                            <th class="min-w-300px">Pesan Terkirim</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-semibold">
                                        @foreach($recipients as $recipient)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold mb-1">{{ $recipient->customer->name ?? 'Unknown' }}</span>
                                                    <span class="text-muted fs-7">{{ $recipient->customer->wa_number ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusBadge = 'badge-light-secondary';
                                                    if ($recipient->status == 'sent') $statusBadge = 'badge-light-success';
                                                    elseif ($recipient->status == 'failed') $statusBadge = 'badge-light-danger';
                                                    elseif ($recipient->status == 'pending') $statusBadge = 'badge-light-warning';
                                                @endphp
                                                <span class="badge {{ $statusBadge }} fw-bold px-3 py-1">{{ ucfirst($recipient->status) }}</span>
                                            </td>
                                            <td>
                                                {{ $recipient->sent_at ? $recipient->sent_at->format('d M Y H:i:s') : '-' }}
                                            </td>
                                            <td>
                                                <div class="text-gray-800 fs-7" style="white-space: pre-wrap; max-height: 100px; overflow-y: auto;">{{ $recipient->sent_message_text }}</div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        @if($recipients->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">Belum ada penerima</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-5 d-flex justify-content-end">
                                {{ $recipients->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </div>
        <!--end::Content-->
    </div>

    @if($broadcast->status == 'running')
    @push('js')
    <script>
        jQuery(document).ready(function($) {
            const statsUrl = '{{ route("admin.broadcasts.stats", $broadcast->id) }}';
            
            function updateStats() {
                $.ajax({
                    url: statsUrl,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Update UI Stats
                        $('#stat-sent').text(data.stats.sent.toLocaleString());
                        $('#stat-failed').text(data.stats.failed.toLocaleString());
                        $('#stat-pending').text(data.stats.pending.toLocaleString());
                        
                        const total = Math.max(data.stats.total, 1);
                        const sent = data.stats.sent;
                        const percent = Math.round((sent / total) * 100);
                        
                        $('#progress-bar').css('width', percent + '%');
                        $('#progress-text').text(percent + '%');

                        // Update Badge if status changed
                        if (data.status === 'completed') {
                            location.reload(); // Refresh to show completed state
                            return;
                        }

                        if (data.status === 'running') {
                            setTimeout(updateStats, 3000); // Poll every 3 seconds
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        setTimeout(updateStats, 10000); // Retry after 10s on error
                    }
                });
            }
            
            @if($broadcast->status == 'running')
            updateStats();
            @endif
        });
    </script>
    @endpush
    @endif
</x-metronic-layout>

<div id="storage-sync-content">
    <!-- Table Card -->
    <div class="card card-flush shadow-sm" id="storage-sync-table-card">
        <div class="card-body pt-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_storage_sync_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_storage_sync_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="min-w-100px sortable" data-column="wa_message_id">ID Pesan</th>
                            <th class="min-w-150px sortable" data-column="updated_at">Waktu</th>
                            <th class="min-w-100px sortable" data-column="media_status">Status</th>
                            <th class="min-w-200px">Cloud URL / Path</th>
                            <th class="text-end min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input sync-checkbox" type="checkbox" value="{{ $log->id }}" />
                                </div>
                            </td>
                            <td><code class="fs-8 text-primary">{{ $log->wa_message_id ?: 'N/A' }}</code></td>
                            <td>{{ $log->updated_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($log->media_status == 'uploaded')
                                    <span class="badge badge-light-success">Synced</span>
                                @elseif($log->media_status == 'failed')
                                    <span class="badge badge-light-danger" data-bs-toggle="tooltip" title="{{ $log->media_last_error }}">Failed</span>
                                @else
                                    <span class="badge badge-light-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($log->media_path)
                                    @php 
                                        $isAbsolute = \Illuminate\Support\Str::startsWith($log->media_path, ['http://', 'https://']);
                                        $fullPath = $isAbsolute ? $log->media_path : asset($log->media_path);
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <a href="{{ $fullPath }}" target="_blank" class="text-gray-800 text-hover-primary mb-1 fs-7 text-break">
                                            {{ \Illuminate\Support\Str::limit($log->media_path, 50) }}
                                        </a>
                                        <span class="text-muted fs-8">{{ $isAbsolute ? 'Cloud Storage' : 'Local Storage' }} Path</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 fs-7 italic">Belum disinkron</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($log->media_path)
                                    @php 
                                        $fullPath = \Illuminate\Support\Str::startsWith($log->media_path, ['http://', 'https://']) ? $log->media_path : asset($log->media_path);
                                    @endphp
                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" 
                                            onclick="window.open('{{ $fullPath }}', '_blank')">
                                        <i class="ki-outline ki-eye fs-3"></i>
                                    </button>
                                @endif
                                @if($log->media_url)
                                    @php 
                                        $fullUrl = \Illuminate\Support\Str::startsWith($log->media_url, ['http://', 'https://']) ? $log->media_url : asset($log->media_url);
                                    @endphp
                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm" 
                                            onclick="window.open('{{ $fullUrl }}', '_blank')" title="Original WA Link">
                                        <i class="ki-outline ki-whatsapp fs-3"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <span class="text-muted fs-6">Belum ada aktivitas sinkronisasi</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-stack flex-wrap pt-10 ajax-pagination">
                <div class="d-flex align-items-center">
                    <select id="per-page-selector" class="form-select form-select-sm form-select-solid w-75px me-3">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <div class="fs-6 fw-bold text-gray-700">
                        Showing {{ $logs->firstItem() ?: 0 }} to {{ $logs->lastItem() ?: 0 }} of {{ $logs->total() }} entries
                    </div>
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<div id="webhook-logs-content">
    <!-- Active Filters Bar (Internal) -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-5" id="active-filters-container">
        <span class="text-muted fw-semibold me-2">Filter aktif:</span>
        @php
            $activeFilters = [];
            if (request('category')) {
                $cats = is_array(request('category')) ? request('category') : [request('category')];
                foreach($cats as $c) $activeFilters[] = ['key' => 'category[]', 'val' => $c, 'label' => 'Kategori: ' . ($categories[$c] ?? $c)];
            }
            if (request('event_type')) {
                $types = is_array(request('event_type')) ? request('event_type') : [request('event_type')];
                foreach($types as $t) $activeFilters[] = ['key' => 'event_type[]', 'val' => $t, 'label' => 'Event: ' . $t];
            }
            if (request()->has('processed') && request('processed') !== '') {
                $status = (array)request('processed');
                foreach($status as $s) $activeFilters[] = ['key' => 'processed[]', 'val' => $s, 'label' => 'Status: ' . ($s == '1' ? 'Processed' : 'Unprocessed')];
            }
        @endphp

        @forelse($activeFilters as $filter)
            <div class="badge badge-lg badge-primary d-flex align-items-center px-4 py-2 gap-2">
                <span>{{ $filter['label'] }}</span>
                <a href="javascript:;" class="remove-filter text-white" data-key="{{ $filter['key'] }}" data-val="{{ $filter['val'] }}">
                    <i class="ki-outline ki-cross fs-6 text-white"></i>
                </a>
            </div>
        @empty
            <span class="text-gray-400 fs-7 italic">Tidak ada filter aktif</span>
        @endforelse
    </div>

    <!-- Table Card -->
    <div class="card card-flush shadow-sm" id="webhook-table-card">
        <div class="card-body pt-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_webhook_logs_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_webhook_logs_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="min-w-50px sortable" data-column="id">
                                ID {!! request('order_by') == 'id' ? (request('order_dir') == 'asc' ? '<i class="ki-outline ki-arrow-up fs-7"></i>' : '<i class="ki-outline ki-arrow-down fs-7"></i>') : '' !!}
                            </th>
                            <th class="min-w-100px sortable" data-column="created_at">
                                Waktu {!! request('order_by', 'created_at') == 'created_at' ? (request('order_dir', 'desc') == 'asc' ? '<i class="ki-outline ki-arrow-up fs-7"></i>' : '<i class="ki-outline ki-arrow-down fs-7"></i>') : '' !!}
                            </th>
                            <th class="min-w-100px sortable" data-column="category">
                                Kategori {!! request('order_by') == 'category' ? (request('order_dir') == 'asc' ? '<i class="ki-outline ki-arrow-up fs-7"></i>' : '<i class="ki-outline ki-arrow-down fs-7"></i>') : '' !!}
                            </th>
                            <th class="min-w-100px">Event</th>
                            <th class="min-w-120px">From/Phone</th>
                            <th class="min-w-150px">Preview</th>
                            <th class="min-w-80px">Status</th>
                            <th class="text-end min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($logs as $log)
                        @php
                            $preview = '';
                            $payload = $log->payload;
                            if (isset($payload['message'])) {
                                if (is_array($payload['message'])) {
                                    $preview = $payload['message']['text'] ?? $payload['message']['body'] ?? '';
                                } else {
                                    $preview = $payload['message'];
                                }
                            }
                            $preview = \Illuminate\Support\Str::limit(strip_tags($preview), 50);
                            
                            $category_badges = [
                                'message_incoming' => 'badge-light-success',
                                'message_outgoing' => 'badge-light-info',
                                'status_update' => 'badge-light-warning',
                                'media' => 'badge-light-primary',
                                'group_event' => 'badge-light-secondary',
                                'connection' => 'badge-light-dark',
                                'unknown' => 'badge-light'
                            ];
                            $badge_class = $category_badges[$log->category] ?? 'badge-light';
                        @endphp
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="{{ $log->id }}" />
                                </div>
                            </td>
                            <td>{{ $log->id }}</td>
                            <td>
                                <span class="text-gray-800">{{ $log->created_at->format('d/m H:i:s') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $badge_class }}">{{ $categories[$log->category] ?? $log->category }}</span>
                            </td>
                            <td>
                                <code class="fs-8">{{ $log->event_type ?: '-' }}</code>
                            </td>
                            <td>
                                <span class="text-gray-700">{{ $log->from_number ?: '-' }}</span>
                            </td>
                            <td>
                                <span class="text-gray-600 fs-7 italic">{{ $preview ?: '-' }}</span>
                            </td>
                            <td>
                                @if($log->processed)
                                    <span class="badge badge-light-success">OK</span>
                                @elseif($log->error_message)
                                    <span class="badge badge-light-danger" data-bs-toggle="tooltip" title="{{ $log->error_message }}">Error</span>
                                @else
                                    <span class="badge badge-light">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-view-payload" 
                                        data-payload="{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}">
                                    <i class="ki-outline ki-eye fs-3"></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete-log" 
                                        data-id="{{ $log->id }}" data-url="{{ route('settings.whatsapp.webhook.delete', $log->id) }}">
                                    <i class="ki-outline ki-trash fs-3"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-10">
                                <div class="text-muted fs-6">Tidak ada log webhook ditemukan</div>
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

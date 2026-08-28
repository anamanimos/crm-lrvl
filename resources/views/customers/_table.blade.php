<div class="table-responsive position-relative">
    <!--begin::Loading Overlay-->
    <div id="table-loading-overlay" class="position-absolute w-100 h-100 top-0 start-0 d-none justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 10;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <!--end::Loading Overlay-->

    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th class="min-w-125px col-customer">Customer</th>
                <th class="min-w-100px col-company">Perusahaan</th>
                <th class="min-w-125px col-whatsapp">Nomor WA</th>
                <th class="min-w-100px col-source">Source</th>
                <th class="min-w-100px col-last-chat">Terakhir Chat</th>
                <th class="text-end min-w-100px col-action">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 fw-semibold">
            @forelse ($customers as $customer)
            <tr>
                <td class="col-customer">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-40px me-3">
                            <div class="symbol-label fs-5 fw-semibold bg-light-primary text-primary">
                                {{ generate_initials($customer->name ?: $customer->wa_number) }}
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <a href="{{ route('admin.customers.show', $customer->id) }}" 
                               class="text-gray-800 text-hover-primary fw-bold">
                                {{ $customer->name ?: 'Tanpa Nama' }}
                            </a>
                            <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                @if ($customer->assignedUser)
                                <span class="text-muted fs-7">
                                    <i class="ki-outline ki-user fs-7"></i> {{ $customer->assignedUser->name }}
                                </span>
                                @endif
                                @if ($customer->labels->isNotEmpty())
                                    @foreach ($customer->labels as $label)
                                    <span class="badge badge-sm py-0 px-2 fs-8" style="background-color: {{ $label->color }}20; color: {{ $label->color }}">
                                        {{ $label->name }}
                                    </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td class="col-company">
                    @if ($customer->company)
                    <span class="text-gray-800 fw-bold">{{ $customer->company->name }}</span>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="col-whatsapp">
                    <a href="https://wa.me/{{ $customer->wa_number }}" target="_blank" 
                       class="text-gray-600 text-hover-primary">
                        {{ format_phone_display($customer->wa_number) }}
                    </a>
                </td>
                <td class="col-source">
                    @if ($customer->source && strtolower($customer->source) !== 'unknown')
                        <span class="badge badge-light-primary fw-bold fs-7 py-1 px-3">
                            <i class="ki-outline ki-compass fs-6 me-1 text-primary"></i>{{ $customer->source }}
                        </span>
                    @else
                        <span class="badge badge-light text-muted fw-semibold fs-7 py-1 px-3">
                            {{ $customer->source ?: 'Unknown' }}
                        </span>
                    @endif
                </td>
                <td class="col-last-chat">
                    {{ time_ago($customer->last_chat_at) }}
                </td>
                <td class="text-end col-action">
                    <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" 
                       data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        Aksi
                        <i class="ki-outline ki-down fs-5 ms-1"></i>
                    </a>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" 
                         data-kt-menu="true">
                        <div class="menu-item px-3">
                            <a href="{{ url('chat?customer=' . $customer->id) }}" class="menu-link px-3">
                                <i class="ki-outline ki-message-text-2 fs-5 me-2"></i> Chat
                            </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="menu-link px-3">
                                <i class="ki-outline ki-eye fs-5 me-2"></i> Detail
                            </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="menu-link px-3">
                                <i class="ki-outline ki-pencil fs-5 me-2"></i> Edit
                            </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3 btn-archive" 
                               data-id="{{ $customer->id }}" data-archived="{{ $customer->is_archived }}">
                                <i class="ki-outline ki-archive fs-5 me-2"></i> {{ $customer->is_archived ? 'Pulihkan' : 'Arsip' }}
                            </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3 text-danger btn-delete" 
                               data-id="{{ $customer->id }}" data-name="{{ $customer->name ?: $customer->wa_number }}">
                                <i class="ki-outline ki-trash fs-5 me-2"></i> Hapus
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-10 text-muted">
                    <div class="d-flex flex-column align-items-center">
                        <i class="ki-outline ki-file-deleted fs-3x text-muted mb-3"></i>
                        <span class="fw-bold fs-6">Tidak ada data customer yang cocok</span>
                        <span class="fs-7 text-muted">Coba ubah kata kunci pencarian atau filter yang dipilih.</span>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!--begin::Pagination Wrapper-->
<div class="d-flex flex-stack flex-wrap pt-4">
    <div class="text-gray-600 fs-7 my-2">
        Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries
    </div>
    <div class="ajax-pagination my-2">
        {{ $customers->links() }}
    </div>
</div>
<!--end::Pagination Wrapper-->

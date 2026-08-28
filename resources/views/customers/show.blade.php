<x-metronic-layout>
    @php
        $title = 'Detail Customer: ' . ($customer->name ?: $customer->wa_number);
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $customer->name ?: 'Customer Tanpa Nama' }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.customers.index') }}" class="text-muted text-hover-primary">Customer</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Detail</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ url('chat?customer=' . $customer->id) }}" class="btn btn-sm fw-bold btn-success">
                    <i class="ki-outline ki-message-text-2 fs-4 me-1"></i> Buka Chat
                </a>
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-pencil fs-4 me-1"></i> Edit Customer
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <div class="row g-6 g-xl-9">
                <!--begin::Profile Sidebar-->
                <div class="col-xl-4">
                    <div class="card mb-5 mb-xl-8">
                        <div class="card-body pt-9">
                            <div class="d-flex flex-center flex-column mb-5">
                                <div class="symbol symbol-100px symbol-circle mb-7">
                                    <span class="symbol-label fs-1 fw-bold bg-light-primary text-primary">
                                        {{ generate_initials($customer->name ?: $customer->wa_number) }}
                                    </span>
                                </div>
                                <h3 class="fs-3 text-gray-800 fw-bold mb-1">{{ $customer->name ?: 'Tanpa Nama' }}</h3>
                                <div class="fs-5 fw-semibold text-muted mb-6">
                                    <a href="https://wa.me/{{ $customer->wa_number }}" target="_blank" class="text-hover-primary">
                                        {{ format_phone_display($customer->wa_number) }}
                                    </a>
                                </div>
                            </div>

                            <div class="d-flex flex-stack fs-4 py-3">
                                <div class="fw-bold rotate btn-active-color-primary">Detail Informasi</div>
                            </div>
                            <div class="separator separator-dashed my-3"></div>

                            <div class="pb-5 fs-6">
                                <div class="fw-bold mt-5 text-gray-600">Perusahaan</div>
                                <div class="text-gray-800">{{ $customer->company ? $customer->company->name : '-' }}</div>

                                <div class="fw-bold mt-5 text-gray-600">Email</div>
                                <div class="text-gray-800">{{ $customer->email ?: '-' }}</div>

                                <div class="fw-bold mt-5 text-gray-600">Sumber / Asal (Source)</div>
                                <div class="text-gray-800">
                                    @if($customer->source && $customer->source !== 'Unknown')
                                        <span class="badge badge-light-info fw-bold py-1 px-3">
                                            <i class="ki-outline ki-compass fs-8 me-1 text-info"></i>{{ $customer->source }}
                                        </span>
                                    @else
                                        <span class="badge badge-light-secondary fw-semibold py-1 px-3">Unknown</span>
                                    @endif
                                </div>

                                <div class="fw-bold mt-5 text-gray-600">Sales / CS Ditugaskan</div>
                                <div class="text-gray-800">{{ $customer->assignedUser ? $customer->assignedUser->name : 'Belum Ditugaskan' }}</div>

                                <div class="fw-bold mt-5 text-gray-600">Alamat</div>
                                <div class="text-gray-800">{{ $customer->address ?: '-' }}</div>

                                <div class="fw-bold mt-5 text-gray-600">Label</div>
                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    @forelse($customer->labels as $label)
                                        <span class="badge" style="background-color: {{ $label->color }}20; color: {{ $label->color }}">
                                            {{ $label->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted fs-7 italic">Tidak ada label</span>
                                    @endforelse
                                </div>

                                <div class="fw-bold mt-5 text-gray-600">Catatan</div>
                                <div class="text-gray-800">{{ $customer->notes ?: '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Deals Card -->
                    <div class="card mb-5 mb-xl-8">
                        <div class="card-header border-0">
                            <div class="card-title">
                                <h3 class="fw-bold text-gray-800">Daftar Deals</h3>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            @forelse($customer->deals as $deal)
                                <div class="d-flex align-items-center mb-6">
                                    <div class="symbol symbol-45px me-5">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-outline ki-briefcase fs-1 text-warning"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap flex-grow-1">
                                        <div class="d-flex flex-column me-3">
                                            <a href="{{ route('deals.index') }}" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $deal->title }}</a>
                                            <span class="text-muted fw-semibold fs-7">{{ $deal->stage->name ?? '-' }}</span>
                                        </div>
                                        <span class="badge badge-light-success fw-bold my-2 ms-auto">
                                            Rp {{ number_format($deal->expected_value, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">Belum ada deal aktif.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <!--end::Profile Sidebar-->

                <!--begin::Chat History-->
                <div class="col-xl-8">
                    <div class="card" id="kt_chat_messenger">
                        <div class="card-header" id="kt_chat_messenger_header">
                            <div class="card-title">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="symbol symbol-35px symbol-circle me-3">
                                        <span class="symbol-label bg-light-success text-success">
                                            <i class="ki-outline ki-whatsapp fs-2"></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-5 fw-bold text-gray-900 text-hover-primary me-1 mb-1">Riwayat Percakapan WhatsApp</span>
                                        <span class="fs-7 text-muted">Pesan dari sistem WhatsApp Gateway</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="kt_chat_messenger_body">
                            <div class="scroll-y me-n5 pe-5" style="max-height: 500px;" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="500px">
                                @forelse($customer->messages as $message)
                                    <div class="d-flex {{ $message->direction == 'out' ? 'justify-content-end' : 'justify-content-start' }} mb-6">
                                        <div class="d-flex flex-column align-items-{{ $message->direction == 'out' ? 'end' : 'start' }}">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="text-muted fs-8">{{ \Carbon\Carbon::parse($message->created_at)->format('d M H:i') }}</span>
                                            </div>
                                            <div class="p-4 rounded bg-light-{{ $message->direction == 'out' ? 'primary text-gray-900' : 'info text-gray-900' }} fw-semibold mw-lg-400px text-start">
                                                @if($message->reply_content)
                                                    <div class="mb-2 p-2 bg-white rounded border-start border-4 border-primary text-muted fs-8">
                                                        <div class="fw-bold text-primary">{{ $message->reply_sender_name }}</div>
                                                        {{ Str::limit($message->reply_content, 80) }}
                                                    </div>
                                                @endif
                                                @if($message->type == 'image' && $message->media_url)
                                                    <div class="mb-2">
                                                        <img src="{{ $message->media_url }}" class="rounded mw-100" alt="media" />
                                                    </div>
                                                @endif
                                                <div class="whitespace-pre-wrap">{{ $message->content }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-10">Belum ada riwayat pesan percakapan.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted fs-7">Untuk membalas pesan secara interaktif, silakan gunakan menu <strong>Chat</strong>.</span>
                                <a href="{{ url('chat?customer=' . $customer->id) }}" class="btn btn-sm btn-primary">Buka di Menu Chat</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Chat History-->
            </div>

        </div>
    </div>
</x-metronic-layout>

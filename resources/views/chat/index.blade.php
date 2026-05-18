<x-metronic-layout>
    @push('css')
    <link href="{{ asset('assets/chat/chat.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .app-footer { display: none !important; }
        #kt_app_content_container { height: calc(100vh - 80px) !important; }
        
        #chat-context-menu {
            border-radius: 8px;
            background: white;
            border: 1px solid #eef3f7;
            min-width: 200px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        }
        #chat-context-menu .dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        #chat-context-menu .dropdown-item:hover {
            background-color: #f1faff;
            color: #0095e8;
        }
        .customer-item.context-open {
            background-color: #f1faff;
        }
        
        .badge.badge-success.whatsapp-green {
            background-color: #25D366 !important;
            color: white !important;
        }
    </style>
    @endpush

    <div id="chat-context-menu" class="dropdown-menu shadow-sm border py-2" style="display: none; position: fixed; z-index: 10000; min-width: 180px;">
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="menu-mark-as-read">
            <i class="ki-outline ki-double-check fs-4 me-3 text-success"></i> Tandai Sudah Dibaca
        </a>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="menu-mark-as-unread">
            <i class="ki-outline ki-message-text-2 fs-4 me-3 text-warning"></i> Tandai Belum Dibaca
        </a>
        <div class="dropdown-divider border-gray-200"></div>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="menu-rename-chat">
            <i class="ki-outline ki-pencil fs-4 me-3 text-primary"></i> Ubah Nama
        </a>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="menu-assign-label">
            <i class="ki-outline ki-tag fs-4 me-3 text-info"></i> Assign Label
        </a>
    </div>

    <div id="message-context-menu" class="dropdown-menu shadow-sm border py-2" style="display: none; position: fixed; z-index: 10000; min-width: 180px;">
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="msg-menu-reply">
            <i class="ki-outline ki-arrow-left fs-4 me-3 text-primary"></i> Balas
        </a>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="msg-menu-forward">
            <i class="ki-outline ki-arrow-right fs-4 me-3 text-info"></i> Teruskan
        </a>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="msg-menu-copy">
            <i class="ki-outline ki-copy fs-4 me-3 text-gray-600"></i> Copy
        </a>
        <div id="msg-menu-edit-divider" class="dropdown-divider border-gray-200 d-none"></div>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center d-none" href="#" id="msg-menu-edit">
            <i class="ki-outline ki-pencil fs-4 me-3 text-warning"></i> Edit Pesan
        </a>
        <div class="dropdown-divider border-gray-200"></div>
        <a class="dropdown-item py-2 px-4 d-flex align-items-center" href="#" id="msg-menu-delete">
            <i class="ki-outline ki-trash fs-4 me-3 text-danger"></i> Hapus Pesan
        </a>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid p-0">
        <div id="kt_app_content_container" class="app-container container-fluid p-0" style="height: 100%;">
            
            <div class="d-flex flex-column flex-lg-row h-100 g-0">
                <!--begin::Sidebar-->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-450px w-xl-500px border-end bg-white h-100 d-flex flex-column">
                    <!--begin::Sidebar Header-->
                    <div class="card-header pt-5 px-5 border-bottom bg-white d-flex align-items-center justify-content-between" id="chat_contacts_header" style="min-height: 70px;">
                        <button type="button" class="btn btn-icon btn-sm btn-light-primary me-2 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modal_new_chat" title="Mulai Chat Baru">
                            <i class="ki-outline ki-plus fs-2"></i>
                        </button>
                        <div class="d-flex align-items-center position-relative my-1 w-100">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" class="form-control form-control-solid w-100 ps-13" 
                                   id="search-customers" placeholder="Cari customer..." />
                        </div>
                        <button type="button" class="btn btn-icon btn-sm btn-light-success ms-2 flex-shrink-0" id="btn-mark-all-read" title="Tandai Semua Sudah Dibaca">
                            <i class="ki-outline ki-double-check fs-2"></i>
                        </button>
                    </div>
                    <!--end::Sidebar Header-->
                    
                    <!--begin::Filter Buttons-->
                    <div class="px-4 py-3 border-bottom bg-white d-flex align-items-center gap-2 flex-wrap" id="chat_filter_buttons">
                        <button type="button" class="btn btn-sm btn-light-primary chat-filter active" data-filter="all">
                            All
                        </button>
                        <button type="button" class="btn btn-sm btn-light chat-filter" data-filter="unread">
                            Unread
                        </button>
                        <button type="button" class="btn btn-sm btn-light chat-filter" data-filter="personal">
                            Personal
                        </button>
                        <button type="button" class="btn btn-sm btn-light chat-filter" data-filter="groups">
                            Groups
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filter-labels-btn">
                                Labels
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="filter-labels-menu" style="max-height: 250px; overflow-y: auto;">
                                <li><a class="dropdown-item label-filter-item" href="#" data-label="">Semua Label</a></li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach ($labels as $label)
                                <li>
                                    <a class="dropdown-item label-filter-item d-flex align-items-center" href="#" data-label="{{ $label->id }}">
                                        <span class="bullet bullet-dot me-2" style="background-color: {{ $label->color }}"></span>
                                        {{ $label->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="filter-deals-btn">
                                Deals
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="filter-deals-menu" style="max-height: 250px; overflow-y: auto;">
                                <li><a class="dropdown-item deal-filter-item" href="#" data-stage="">Semua Deal</a></li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach ($deal_stages as $stage)
                                <li>
                                    <a class="dropdown-item deal-filter-item d-flex align-items-center" href="#" data-stage="{{ $stage->id }}">
                                        <span class="bullet bullet-dot me-2" style="background-color: {{ $stage->color }}"></span>
                                        {{ $stage->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!--end::Filter Buttons-->
                    
                    <!--begin::Sidebar Body-->
                    <div class="card-body p-0 flex-grow-1 overflow-auto" id="chat_contacts_body">
                        <div id="customers-list">
                            <!-- Loaded via JavaScript -->
                            <div class="text-center py-10" id="customers-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <!-- Infinite scroll loading indicator -->
                        <div class="text-center py-3 d-none" id="customers-scroll-loading">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="text-muted ms-2 fs-7">Memuat...</span>
                        </div>
                    </div>
                    <!--end::Sidebar Body-->
                </div>
                <!--end::Sidebar-->
                
                <!--begin::Content-->
                <div class="flex-lg-row-fluid d-flex flex-column bg-secondary bg-opacity-10 h-100 position-relative">
                    
                    <!-- Empty State -->
                    <div class="d-flex flex-column flex-center h-100" id="no-chat-selected">
                        <i class="ki-outline ki-message-text-2 fs-5x text-gray-300 mb-5"></i>
                        <div class="fs-4 fw-bold text-gray-500">Pilih customer untuk memulai chat</div>
                    </div>

                    <!-- Chat Container -->
                    <div class="d-none flex-column h-100 w-100" id="chat-container">
                        <!--begin::Chat Header-->
                        <div class="card-header d-flex align-items-center justify-content-between p-5 border-bottom bg-white" style="min-height: 70px;">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px symbol-circle me-3" id="chat-header-avatar-container">
                                    <div class="symbol-label fs-3 fw-bold" style="background-color: #00a884; color: white;" id="chat-header-initials">
                                        -
                                    </div>
                                    <img src="" alt="image" id="chat-header-avatar-img" class="d-none" style="object-fit:cover;" />
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-4 fw-bold text-gray-900 lh-1" id="chat-customer-name">-</span>
                                    <div class="d-flex align-items-center mt-1">
                                        <span class="text-muted fs-7 me-2" id="chat-customer-phone">-</span>
                                        <div id="chat-customer-labels"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-sm btn-light-primary me-2" 
                                        style="display: none;"
                                        data-bs-toggle="tooltip" title="Refresh Info Grup" id="btn-refresh-group">
                                    <i class="bi bi-arrow-clockwise fs-3"></i> <span class="ms-1">Refresh</span>
                                </button>
                                <div class="d-flex align-items-center" id="chat-header-actions">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" 
                                            data-bs-toggle="tooltip" title="Detail Customer" id="btn-customer-detail">
                                        <i class="ki-outline ki-eye fs-4"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-warning me-2" 
                                            data-bs-toggle="tooltip" title="Edit Kontak" id="btn-edit-contact">
                                        <i class="ki-outline ki-pencil fs-4"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-info me-2" 
                                            data-bs-toggle="tooltip" title="Assign Label" id="btn-assign-label">
                                        <i class="ki-outline ki-tag fs-4"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light-primary me-2" id="btn-header-deal-baru">
                                        <i class="ki-outline ki-plus fs-4"></i> Deal Baru
                                    </button>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ki-outline ki-user fs-4"></i> Assign
                                        </button>
                                        <ul class="dropdown-menu" id="assign-dropdown">
                                            <li><a class="dropdown-item assign-option" href="#" data-id="0">Release</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            @foreach ($agents as $agent)
                                            <li><a class="dropdown-item assign-option" href="#" data-id="{{ $agent->id }}">{{ $agent->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!--end::Chat Header-->
                        
                        <!--begin::Chat Body-->
                        <div class="flex-grow-1 overflow-auto" id="messages-container">
                            <div id="messages-list" class="d-flex flex-column"></div>
                        </div>
                        <!--end::Chat Body-->
                        
                        <!--begin::Chat Footer-->
                        <div class="chat-input-area" id="chat_messenger_footer">
                            <form id="send-message-form">
                                <input type="hidden" name="customer_id" id="current-customer-id" value="">
                                
                                <div id="attachment-preview" class="d-none mb-3 border rounded p-2 bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span id="attachment-name" class="me-3 fw-semibold fs-7"></span>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger p-0 h-20px w-20px" id="remove-attachment">
                                            <i class="ki-outline ki-cross fs-6"></i>
                                        </button>
                                    </div>
                                </div>
                                
    @push('css')
    <style>
        img.joypixels {
            height: 1.5em;
            width: 1.5em;
            display: inline-block;
            vertical-align: middle;
            margin: 0 1px;
        }
        #emoji-list-container .joypixels {
            height: 2.5em;
            width: 2.5em;
        }
        .template-grid-item {
            cursor: pointer;
            border: 1px solid #e1e3ea;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.2s;
            height: 100%;
            background-color: #ffffff;
        }
        .template-grid-item:hover {
            background-color: #f1faff;
            border-color: #009ef7;
        }
        #chat-right-sidebar {
            z-index: 100;
            transition: all 0.3s ease;
        }
        @media (max-width: 991.98px) {
            #chat-right-sidebar {
                position: absolute !important;
                top: 0;
                right: 0;
                width: 100% !important;
                z-index: 200;
            }
        }
    </style>
    @endpush
    @push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emoji-toolkit/6.0.0/joypixels.min.js"></script>
    @endpush

                                <div id="emoji_picker" class="d-none position-absolute bg-white shadow-lg rounded border p-0" style="bottom: 80px; left: 10px; width: 420px; z-index: 1000; overflow: hidden; display: flex; flex-direction: column; height: 400px;">
                                    <div class="emoji-picker-header p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                                        <h6 class="mb-0">Emoji</h6>
                                        <div class="emoji-categories d-flex gap-2">
                                            <!-- Category icons will be injected here -->
                                        </div>
                                    </div>
                                    <div id="emoji-list-container" class="p-3 overflow-y-auto flex-grow-1">
                                        <div class="text-center py-5"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                                    </div>
                                    <div class="recent-section p-3 border-top bg-light d-none">
                                        <div class="fw-bold text-muted fs-9 mb-2 uppercase">TERAKHIR DIGUNAKAN</div>
                                        <div id="recent-emojis-container" class="d-flex flex-wrap gap-1"></div>
                                    </div>
                                </div>

                                <div id="template-sticky-container" class="d-none bg-white border rounded mb-3 shadow-sm" style="max-height: 400px; overflow-y: auto;">
                                    <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between sticky-top" style="z-index: 10;">
                                        <h6 class="mb-0">Pilih Template</h6>
                                        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" onclick="toggleTemplatePreview()">
                                            <i class="ki-outline ki-cross fs-3"></i>
                                        </button>
                                    </div>
                                    <div class="p-3 border-bottom sticky-top bg-white" style="top: 56px; z-index: 9;">
                                        <div class="position-relative">
                                            <i class="ki-outline ki-magnifier fs-4 position-absolute ms-3" style="top: 50%; transform: translateY(-50%);"></i>
                                            <input type="text" class="form-control form-control-sm ps-10" id="search-templates" placeholder="Cari template..." onkeyup="filterTemplates()">
                                        </div>
                                    </div>
                                    <div class="p-3" id="template-sticky-body">
                                        <div class="text-center py-3"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-end bg-transparent pt-2 position-relative">
                                    <div class="d-flex align-items-end flex-grow-1 bg-white px-2 py-1 shadow-sm me-3" style="border-radius: 24px;">
                                        <!-- Attachments Dropdown -->
                                        <div class="position-relative mb-1">
                                            <button class="btn btn-icon btn-sm btn-color-gray-700 btn-active-color-primary w-35px h-35px" type="button" id="btn-attachments" data-kt-menu-trigger="click" data-kt-menu-placement="top-start">
                                                <i class="ki-outline ki-plus fs-1 text-dark fw-bolder"></i>
                                            </button>
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-150px py-3" data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <label class="menu-link px-3 cursor-pointer">
                                                        <i class="ki-outline ki-picture fs-4 me-2"></i> Gambar
                                                        <input type="file" class="d-none" id="input-image" accept="image/*" multiple>
                                                    </label>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <label class="menu-link px-3 cursor-pointer">
                                                        <i class="ki-outline ki-document fs-4 me-2"></i> Dokumen
                                                        <input type="file" class="d-none" id="input-document" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" multiple>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Emoji Button -->
                                        <div class="mb-1">
                                            <button class="btn btn-icon btn-sm btn-color-gray-700 btn-active-color-primary w-35px h-35px" type="button" id="btn-emoji" onclick="toggleEmojiPicker()">
                                                <i class="ki-outline ki-emoji-happy fs-1 text-dark fw-bolder"></i>
                                            </button>
                                        </div>

                                        <!-- Template Button -->
                                        <div class="mb-1">
                                            <button class="btn btn-icon btn-sm btn-color-gray-700 btn-active-color-primary w-35px h-35px" type="button" id="btn-template" onclick="toggleTemplatePreview()">
                                                <i class="ki-outline ki-notepad-edit fs-1 text-dark fw-bolder"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="flex-grow-1 position-relative ms-2">
                                            <textarea class="form-control form-control-flush bg-transparent px-2 py-3 border-0" 
                                                      rows="1" id="message-input" name="message" 
                                                      style="resize: none; overflow-y: hidden; min-height: 44px; box-shadow: none;"
                                                      placeholder="" oninput="autoResize(this)"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-1 flex-shrink-0">
                                        <button class="btn btn-icon btn-dark rounded-circle w-45px h-45px shadow-sm" type="submit" id="btn-send">
                                            <i class="ki-outline ki-send fs-2 text-white ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!--end::Chat Footer-->
                    </div>
                </div>
                <!--end::Content-->

                <!--begin::Right Sidebar-->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-300px bg-white border-start d-none h-100 position-relative overflow-auto" id="chat-right-sidebar">
                    <div class="card-header d-flex align-items-center justify-content-between p-5 border-bottom flex-shrink-0" style="min-height: 70px;">
                        <span class="fs-4 fw-bold text-gray-900">Info Kontak</span>
                        <button type="button" class="btn btn-sm btn-icon btn-active-light-danger" id="btn-close-sidebar">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="text-center py-10 d-none" id="right-sidebar-loading">
                            <span class="spinner-border text-primary"></span>
                        </div>
                        <div id="right-sidebar-content" class="d-none">
                            <div class="d-flex flex-column align-items-center text-center p-5 border-bottom">
                                <div class="symbol symbol-100px symbol-circle mb-3 border border-2 border-primary border-opacity-25" style="overflow: hidden;">
                                    <img src="{{ asset('assets/media/avatars/blank.png') }}" alt="image" id="sidebar-avatar" style="object-fit:cover;" />
                                </div>
                                <div class="fs-4 fw-bold text-gray-900 mb-1" id="sidebar-name">-</div>
                                <div class="fs-6 text-muted mb-3" id="sidebar-phone">-</div>
                                <button type="button" class="btn btn-sm btn-light-primary fw-bold" id="btn-force-update-avatar">
                                    <i class="ki-outline ki-arrows-circle fs-6"></i> Update Foto
                                </button>
                            </div>
                            <div class="p-5">
                                <div class="mb-5">
                                    <span class="fs-7 text-muted fw-semibold">Label</span>
                                    <div class="d-flex flex-wrap gap-1 mt-1" id="sidebar-labels">
                                        <span class="text-muted fs-8">-</span>
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fs-7 text-muted fw-semibold">Active Deals</span>
                                        <button type="button" class="btn btn-icon btn-sm btn-light-primary w-20px h-20px" id="btn-sidebar-deal-baru" title="Buat Deal Baru">
                                            <i class="ki-outline ki-plus fs-7"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex flex-column gap-2 mt-1" id="sidebar-deals">
                                        <span class="text-muted fs-8">-</span>
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <span class="fs-7 text-muted fw-semibold">Assigned To</span>
                                    <div class="fs-6 fw-semibold text-gray-800" id="sidebar-assignee">-</div>
                                </div>
                                <div class="mb-5">
                                    <span class="fs-7 text-muted fw-semibold">Email</span>
                                    <div class="fs-6 fw-semibold text-gray-800" id="sidebar-email">-</div>
                                </div>
                                <div class="mb-5">
                                    <span class="fs-7 text-muted fw-semibold">Alamat</span>
                                    <div class="fs-6 fw-semibold text-gray-800" id="sidebar-address">-</div>
                                </div>
                                <div class="mb-5">
                                    <span class="fs-7 text-muted fw-semibold">Terakhir Chat</span>
                                    <div class="fs-6 fw-semibold text-gray-800" id="sidebar-last-chat">-</div>
                                </div>
                                <div class="mb-5">
                                    <span class="fs-7 text-muted fw-semibold">Diperbarui</span>
                                    <div class="fs-8 text-gray-600" id="sidebar-last-updated">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Right Sidebar-->
            </div>

        </div>
    </div>

    @push('js')
    <script>
        var appUrl = "{{ url('/') }}/";
        var selectedCustomerId = {{ $selected_customer ? $selected_customer->id : 'null' }};
        var currentUserId = {{ auth()->id() ?? 'null' }};
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Fallback visibility for refresh button
        $(document).on('click', '.customer-item', function() {
            const type = $(this).data('type');
            if (type === 'group') {
                $('#btn-refresh-group').attr('style', 'display: inline-flex !important');
            } else {
                $('#btn-refresh-group').attr('style', 'display: none !important');
            }
        });
    </script>
    <script src="{{ asset('assets/chat/chat.js') }}?v={{ time() }}"></script>
    @endpush
    <!--begin::Modal Deal Baru-->
    <div class="modal fade" id="modal-deal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold" id="modal-deal-title">Deal Baru</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-deal">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" id="deal-id">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label required">Judul Deal</label>
                                <input type="text" class="form-control" name="title" id="deal-title" placeholder="Contoh: Bordir Kaos Kelas 12 SMA..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <select class="form-select" name="customer_id" id="deal-customer"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estimasi Nilai (Rp)</label>
                                <input type="number" class="form-control" name="expected_value" id="deal-value" placeholder="0" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sumber</label>
                                <input type="text" class="form-control" name="source" id="deal-source" placeholder="WA, Instagram, Facebook, Referral...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PIC / Assigned To</label>
                                <select class="form-select" name="assigned_user_id" id="deal-assigned">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tgl Follow-up Selanjutnya</label>
                                <input type="datetime-local" class="form-control" name="next_followup_date" id="deal-followup">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Perkiraan Closing</label>
                                <input type="date" class="form-control" name="expected_close_date" id="deal-close">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-deal">
                            <span class="indicator-label">Simpan</span>
                            <span class="indicator-progress" style="display:none;">Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end::Modal Deal Baru-->

    <!--begin::Modal Assign Label-->
    <div class="modal fade" id="modal-assign-label" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-400px">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="fw-bold">Assign Label</h3>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assign-label-customer-id">
                    <div class="d-flex flex-column gap-3" id="label-checkbox-list">
                        @if ($labels->count() > 0)
                            @foreach ($labels as $label)
                            <label class="form-check form-check-custom form-check-solid align-items-start cursor-pointer mb-3">
                                <input class="form-check-input mt-1" type="checkbox" name="customer_labels[]" value="{{ $label->id }}" />
                                <span class="form-check-label d-flex flex-column align-items-start">
                                    <span class="fw-bold fs-6 d-flex align-items-center">
                                        <span class="bullet bullet-dot me-2" style="background-color: {{ $label->color }}"></span>
                                        {{ $label->name }}
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-5">Belum ada label yang tersedia</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-labels">
                        <span class="indicator-label">Simpan</span>
                        <span class="indicator-progress" style="display:none;">Menyimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal Assign Label-->

    <!-- Modal New Chat -->
    <div class="modal fade" id="modal_new_chat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-600px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Mulai Chat Baru</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-lg-10 px-lg-10">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6" id="newChatTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab_search_contact">
                                <i class="ki-outline ki-magnifier fs-4 me-1"></i> Cari Kontak
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab_new_number">
                                <i class="ki-outline ki-phone fs-4 me-1"></i> Nomor Baru
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab: Search Existing Contact -->
                        <div class="tab-pane fade show active" id="tab_search_contact">
                            <div class="d-flex align-items-center position-relative mb-5">
                                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                                <input type="text" class="form-control form-control-solid ps-12" id="search-new-chat" placeholder="Cari nama atau nomor WA..." />
                            </div>
                            <div class="scroll-y me-n5 pe-5" style="max-height: 300px;" id="new-chat-list">
                                <div class="text-center text-muted py-5">Ketik untuk mencari customer</div>
                            </div>
                        </div>

                        <!-- Tab: New Number -->
                        <div class="tab-pane fade" id="tab_new_number">
                            <div class="mb-5">
                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light-success text-success fw-bold">
                                        <i class="ki-outline ki-whatsapp fs-3 text-success me-1" style="display:none"></i>+62
                                    </span>
                                    <input type="text" class="form-control" id="input-new-wa-number" 
                                           placeholder="cth: 81234567890" 
                                           pattern="[0-9]*" inputmode="numeric"
                                           maxlength="15" />
                                    <button type="button" class="btn btn-primary" id="btn-check-wa">
                                        <span class="indicator-label">
                                            <i class="ki-outline ki-check-circle fs-4 me-1"></i> Cek
                                        </span>
                                        <span class="indicator-progress" style="display: none;">
                                            <span class="spinner-border spinner-border-sm align-middle"></span>
                                        </span>
                                    </button>
                                </div>
                                <div class="form-text text-muted">Masukkan nomor tanpa awalan 0 atau +62. Contoh: 81234567890</div>
                            </div>

                            <!-- Check Result Area -->
                            <div id="wa-check-result" class="d-none">
                                <!-- Will be populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rename Chat -->
    <div class="modal fade" id="modal-rename-chat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-450px">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="rename-chat-title">Ubah Nama</h3>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rename-chat-id">
                    <input type="hidden" id="rename-chat-type">
                    <div class="mb-3">
                        <label class="form-label" for="rename-chat-input">Nama Baru</label>
                        <input type="text" class="form-control" id="rename-chat-input" maxlength="255" placeholder="Masukkan nama baru">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-rename-chat">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Forward Message -->
    <div class="modal fade" id="modal-forward-message" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-600px">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Teruskan Pesan</h3>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-4 bg-light mb-4">
                        <div class="fw-bold text-primary mb-1" id="forward-preview-sender">-</div>
                        <div class="text-muted fs-7" id="forward-preview-content">-</div>
                    </div>
                    <div class="alert alert-warning d-flex align-items-start gap-3 py-3 px-4 mb-4">
                        <i class="ki-outline ki-shield-tick fs-2 text-warning mt-1"></i>
                        <div class="fs-7 text-gray-800">
                            Hati-hati saat meneruskan ke banyak chat sekaligus. Pengiriman massal terlalu sering bisa dianggap spam dan meningkatkan risiko nomor WhatsApp terkena pembatasan atau banned.
                        </div>
                    </div>
                    <div class="d-flex align-items-center position-relative mb-4">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" class="form-control form-control-solid ps-12" id="forward-search" placeholder="Cari chat tujuan..." />
                    </div>
                    <div id="forward-target-list" class="scroll-y" style="max-height: 320px;">
                        <div class="text-center text-muted py-5">Ketik untuk mencari chat tujuan</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto text-muted fs-7" id="forward-selected-count">0 dipilih</div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-forward-selected" disabled>Teruskan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Message -->
    <div class="modal fade" id="modal-edit-message" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-icon btn-active-color-primary me-2" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-2"></i>
                    </button>
                    <h3 class="modal-title fs-5 fw-bold text-gray-800">Edit message</h3>
                </div>
                <div class="modal-body p-0">
                    <div class="edit-preview-container p-10 d-flex flex-column align-items-center justify-content-center" 
                         style="background-image: url('{{ asset('assets/media/chat-bg-light.png') }}'); background-repeat: repeat; background-size: 400px; background-color: #efeae2; min-height: 200px;">
                        <div id="edit-message-preview-bubble" class="w-100 d-flex justify-content-center">
                            <!-- Message bubble will be injected here -->
                        </div>
                    </div>
                    <div class="px-8 py-6 bg-white d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-icon btn-active-light-primary w-35px h-35px">
                            <i class="ki-outline ki-notification-status fs-2 text-gray-600"></i>
                        </button>
                        <div class="flex-grow-1 border-bottom border-gray-300 pb-1">
                            <input type="text" id="edit-message-input" class="form-control form-control-flush fs-6 py-2 px-0" 
                                   placeholder="Edit message" style="border: none; outline: none; background: transparent;" />
                        </div>
                        <button type="button" class="btn btn-icon btn-dark rounded-circle w-45px h-45px shadow-sm" id="btn-save-message-edit">
                            <i class="ki-outline ki-check fs-1 text-white"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-metronic-layout>

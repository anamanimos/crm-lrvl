<x-metronic-layout>
    @php
        $title = 'Dashboard';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack flex-wrap gap-3">
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
                    <li class="breadcrumb-item text-muted">Ringkasan Leads & Aktivitas WhatsApp</li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                    <i class="ki-outline ki-whatsapp fs-4"></i>
                    <span>Buka Chat WhatsApp</span>
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <!--begin::Row - KPI Statistics Cards-->
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                <!--begin::Col - Leads Hari Ini-->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-flush h-100 border border-gray-200 hover-elevate-up shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-between p-6">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="text-gray-500 fw-semibold fs-7">LEADS HARI INI</span>
                                <div class="symbol symbol-40px symbol-circle bg-light-primary">
                                    <i class="ki-outline ki-user-tick fs-2 text-primary"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-2hx fw-bold text-gray-900 mb-1 lh-1">
                                    {{ $leadsToday }}
                                </span>
                                <div class="d-flex align-items-center gap-2 pt-2">
                                    @if ($leadsDiff > 0)
                                    <span class="badge badge-light-success fs-8 fw-bold">
                                        <i class="ki-outline ki-arrow-up fs-8 text-success me-1"></i>+{{ $leadsDiff }} vs kemarin
                                    </span>
                                    @elseif ($leadsDiff < 0)
                                    <span class="badge badge-light-danger fs-8 fw-bold">
                                        <i class="ki-outline ki-arrow-down fs-8 text-danger me-1"></i>{{ $leadsDiff }} vs kemarin
                                    </span>
                                    @else
                                    <span class="badge badge-light fs-8 fw-semibold text-muted">
                                        Sama dengan kemarin ({{ $leadsYesterday }})
                                    </span>
                                    @endif
                                </div>
                                <div class="mt-3 pt-2 border-top border-gray-100">
                                    <button type="button" onclick="openLeadsModal('{{ date('Y-m-d') }}', 'Hari Ini')" class="btn btn-link btn-color-primary btn-active-color-primary p-0 text-start fs-8 fw-bold d-inline-flex align-items-center gap-1">
                                        <span>Cek Detail Leads Hari Ini</span>
                                        <i class="ki-outline ki-arrow-right fs-8"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Pesan Masuk Hari Ini-->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-flush h-100 border border-gray-200 hover-elevate-up shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-between p-6">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="text-gray-500 fw-semibold fs-7">PESAN MASUK HARI INI</span>
                                <div class="symbol symbol-40px symbol-circle bg-light-success">
                                    <i class="ki-outline ki-entrance-left fs-2 text-success"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-2hx fw-bold text-gray-900 mb-1 lh-1">
                                    {{ $chatInToday }}
                                </span>
                                <span class="text-muted fs-7 pt-2">
                                    Dari <strong class="text-gray-700">{{ $uniqueChattersToday }}</strong> kontak aktif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Pesan Keluar Hari Ini-->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-flush h-100 border border-gray-200 hover-elevate-up shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-between p-6">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="text-gray-500 fw-semibold fs-7">PESAN KELUAR HARI INI</span>
                                <div class="symbol symbol-40px symbol-circle bg-light-info">
                                    <i class="ki-outline ki-exit-right fs-2 text-info"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-2hx fw-bold text-gray-900 mb-1 lh-1">
                                    {{ $chatOutToday }}
                                </span>
                                <span class="text-muted fs-7 pt-2">
                                    Balasan CS dan pesan keluar
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Total Database Kontak-->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-flush h-100 border border-gray-200 hover-elevate-up shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-between p-6">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="text-gray-500 fw-semibold fs-7">TOTAL KONTAK CRM</span>
                                <div class="symbol symbol-40px symbol-circle bg-light-warning">
                                    <i class="ki-outline ki-address-book fs-2 text-warning"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-2hx fw-bold text-gray-900 mb-1 lh-1">
                                    {{ number_format($totalCustomers, 0, ',', '.') }}
                                </span>
                                <span class="text-muted fs-7 pt-2">
                                    Database pelanggan tersimpan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row - Chart & Quick Actions-->
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                <!--begin::Col - Daily Leads Chart-->
                <div class="col-12 col-xl-8">
                    <div class="card card-flush h-xl-100 border border-gray-200 shadow-sm">
                        <div class="card-header pt-6 pb-2">
                            <div class="card-title d-flex flex-column">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ki-outline ki-chart-simple-3 fs-3 text-primary"></i>
                                    <h3 class="fw-bold text-gray-900 fs-5 mb-0">Tren Leads Masuk Harian</h3>
                                </div>
                                <span class="text-muted fs-7 mt-1">
                                    Perolehan kontak baru per hari (14 hari terakhir) &bull; <span class="text-primary fw-semibold"><i class="ki-outline ki-mouse fs-8 text-primary"></i> Klik batang untuk rincian detail</span>
                                </span>
                            </div>
                            <div class="card-toolbar">
                                <span class="badge badge-light-primary fw-bold fs-7 px-3 py-2">
                                    Total 14 Hari: {{ $totalLeads14Days }} Lead
                                </span>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-5">
                            <div id="chart_daily_leads" style="min-height: 310px;"></div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Gateway & Quick Shortcuts-->
                <div class="col-12 col-xl-4">
                    <div class="card card-flush h-xl-100 border border-gray-200 shadow-sm">
                        <div class="card-header pt-6 pb-2">
                            <div class="card-title d-flex flex-column">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ki-outline ki-element-11 fs-3 text-gray-700"></i>
                                    <h3 class="fw-bold text-gray-900 fs-5 mb-0">Operasional & Akses Cepat</h3>
                                </div>
                                <span class="text-muted fs-7 mt-1">Pintasan fitur utama CRM</span>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <!--begin::WhatsApp Status Box-->
                            <div class="p-4 rounded-3 bg-light-primary border border-primary border-opacity-20 mb-6">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px symbol-circle bg-white">
                                            <i class="ki-outline ki-whatsapp fs-2 text-success"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-6">{{ $waSession }}</span>
                                            <span class="text-muted fs-8">WhatsApp Gateway Utama</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'koneksi']) }}" 
                                       class="btn btn-sm btn-white btn-active-light-primary border border-gray-300 py-1 px-3 fs-8">
                                        Kelola Sesi
                                    </a>
                                </div>
                            </div>
                            <!--end::WhatsApp Status Box-->

                            <!--begin::Quick Links List-->
                            <div class="d-flex flex-column gap-3">
                                <a href="{{ route('chat.index') }}" 
                                   class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-hover-light border border-gray-200 text-gray-800 text-hover-primary transition-all">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-35px symbol-circle bg-light-success">
                                            <i class="ki-outline ki-messages fs-4 text-success"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold fs-6">Ruang Chat WhatsApp</span>
                                            <span class="text-muted fs-8">Obrolan langsung dengan pelanggan</span>
                                        </div>
                                    </div>
                                    <i class="ki-outline ki-arrow-right fs-5 text-gray-400"></i>
                                </a>

                                <a href="{{ route('admin.broadcasts.index') }}" 
                                   class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-hover-light border border-gray-200 text-gray-800 text-hover-primary transition-all">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-35px symbol-circle bg-light-primary">
                                            <i class="ki-outline ki-send fs-4 text-primary"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold fs-6">Broadcast Pesan</span>
                                            <span class="text-muted fs-8">Kirim pesan massal ke kontak</span>
                                        </div>
                                    </div>
                                    <i class="ki-outline ki-arrow-right fs-5 text-gray-400"></i>
                                </a>

                                <a href="{{ route('admin.reports.daily') }}" 
                                   class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-hover-light border border-gray-200 text-gray-800 text-hover-primary transition-all">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-35px symbol-circle bg-light-warning">
                                            <i class="ki-outline ki-chart-line fs-4 text-warning"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold fs-6">Laporan Harian CS</span>
                                            <span class="text-muted fs-8">Performa respon dan missed chat</span>
                                        </div>
                                    </div>
                                    <i class="ki-outline ki-arrow-right fs-5 text-gray-400"></i>
                                </a>

                                <a href="{{ route('admin.labels.index') }}" 
                                   class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-hover-light border border-gray-200 text-gray-800 text-hover-primary transition-all">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-35px symbol-circle bg-light-info">
                                            <i class="ki-outline ki-tag fs-4 text-info"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold fs-6">Modul Label WhatsApp</span>
                                            <span class="text-muted fs-8">Sinkronisasi label WhatsApp Business</span>
                                        </div>
                                    </div>
                                    <i class="ki-outline ki-arrow-right fs-5 text-gray-400"></i>
                                </a>
                            </div>
                            <!--end::Quick Links List-->
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row - Recent Contacts-->
            <div class="row g-5 g-xl-8">
                <div class="col-12">
                    <div class="card card-flush border border-gray-200 shadow-sm">
                        <div class="card-header pt-6 pb-2">
                            <div class="card-title d-flex flex-column">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ki-outline ki-people fs-3 text-gray-700"></i>
                                    <h3 class="fw-bold text-gray-900 fs-5 mb-0">Kontak & Obrolan Terbaru</h3>
                                </div>
                                <span class="text-muted fs-7 mt-1">Daftar pelanggan dengan interaksi WhatsApp terkini</span>
                            </div>
                            <div class="card-toolbar">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
                                    Lihat Semua Kontak
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            @if ($recentCustomers->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-1 border-gray-200">
                                            <th class="min-w-200px">PELANGGAN</th>
                                            <th class="min-w-140px">NOMOR WHATSAPP</th>
                                            <th class="min-w-160px">LABEL WHATSAPP</th>
                                            <th class="min-w-130px">TERAKHIR CHAT</th>
                                            <th class="text-end min-w-80px">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentCustomers as $customer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px symbol-circle me-3">
                                                        <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">
                                                            {{ generate_initials($customer->name ?: $customer->wa_number) }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold fs-6">
                                                            {{ $customer->name ?: 'Tanpa Nama' }}
                                                        </span>
                                                        @if ($customer->created_at && $customer->created_at->isToday())
                                                        <span class="badge badge-light-success fs-9 w-fit mt-1">Lead Baru</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-700 fw-semibold fs-7 font-monospace">
                                                    {{ format_phone_display($customer->wa_number) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($customer->labels->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($customer->labels as $label)
                                                        <span class="badge fs-8 py-1 px-2" style="background-color: {{ $label->color }}18; color: {{ $label->color }};">
                                                            {{ $label->name }}
                                                        </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-8">Tidak ada label</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-gray-600 fs-7">
                                                    {{ time_ago($customer->last_chat_at) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ url('chat?customer=' . $customer->id) }}" 
                                                   class="btn btn-sm btn-icon btn-light-success btn-active-success"
                                                   title="Buka Obrolan">
                                                    <i class="ki-outline ki-message-text-2 fs-4"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="d-flex flex-column align-items-center justify-content-center py-12">
                                <i class="ki-outline ki-messages fs-3x text-muted mb-3"></i>
                                <div class="fs-6 fw-bold text-gray-800 mb-1">Belum Ada Riwayat Obrolan</div>
                                <div class="text-muted fs-7 mb-4">Kontak yang mengirim atau menerima pesan akan otomatis ditampilkan di sini.</div>
                                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-primary">
                                    Mulai Chat Baru
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Row-->

        </div>
    </div>

    <!--begin::Modal - Leads Detail-->
    <div class="modal fade" id="modal_leads_detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header pb-3 border-bottom">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ki-outline ki-user-tick fs-3 text-primary"></i>
                            <h4 class="fw-bold text-gray-900 mb-0" id="modal_leads_title">Detail Leads Masuk</h4>
                            <span class="badge badge-light-primary fw-bold fs-8" id="modal_leads_badge">0 Lead</span>
                        </div>
                        <span class="text-muted fs-7 mt-1" id="modal_leads_subtitle">Pilih tanggal untuk melihat rincian kontak masuk</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" class="form-control form-control-sm form-control-solid w-140px" id="modal_leads_datepicker" onchange="fetchLeadsByDate(this.value)">
                        <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-body py-4">
                    <!-- Loading State -->
                    <div id="modal_leads_loading" class="py-12 text-center">
                        <div class="spinner-border text-primary w-35px h-35px mb-3" role="status"></div>
                        <div class="text-gray-600 fw-semibold fs-7">Mengambil rincian data leads...</div>
                    </div>

                    <!-- Empty State -->
                    <div id="modal_leads_empty" class="py-12 text-center d-none">
                        <div class="symbol symbol-60px symbol-circle bg-light mb-3">
                            <i class="ki-outline ki-user-square fs-2tx text-muted"></i>
                        </div>
                        <div class="fw-bold text-gray-800 fs-6 mb-1">Tidak Ada Leads Baru</div>
                        <div class="text-muted fs-7 max-w-350px mx-auto">
                            Belum ada kontak atau pelanggan baru yang terdaftar pada tanggal ini.
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div id="modal_leads_table_wrap" class="table-responsive d-none">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-500 border-bottom-1 border-gray-200">
                                    <th class="min-w-160px">PELANGGAN</th>
                                    <th class="min-w-130px">NOMOR WHATSAPP</th>
                                    <th class="min-w-100px">JAM MASUK</th>
                                    <th class="min-w-130px">LABEL WHATSAPP</th>
                                    <th class="min-w-120px">CS / SUMBER</th>
                                    <th class="text-end min-w-70px">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="modal_leads_tbody">
                                <!-- Rendered via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer justify-content-between py-3 border-top">
                    <a href="{{ route('admin.customers.index') }}" id="btn_modal_open_customer_page" class="btn btn-sm btn-light-primary">
                        <i class="ki-outline ki-arrow-up-right fs-5 me-1"></i>
                        Buka di Halaman Customer
                    </a>
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Leads Detail-->

    @push('js')
    <script>
        var leadsModalInstance = null;

        function openLeadsModal(dateStr, labelText) {
            var modalEl = document.getElementById('modal_leads_detail');
            if (!modalEl) return;

            if (!leadsModalInstance) {
                leadsModalInstance = new bootstrap.Modal(modalEl);
            }
            leadsModalInstance.show();

            document.getElementById('modal_leads_datepicker').value = dateStr;
            fetchLeadsByDate(dateStr, labelText);
        }

        function fetchLeadsByDate(dateStr, labelText) {
            var loadingEl = document.getElementById('modal_leads_loading');
            var emptyEl = document.getElementById('modal_leads_empty');
            var tableWrapEl = document.getElementById('modal_leads_table_wrap');
            var tbodyEl = document.getElementById('modal_leads_tbody');
            var titleEl = document.getElementById('modal_leads_title');
            var subtitleEl = document.getElementById('modal_leads_subtitle');
            var badgeEl = document.getElementById('modal_leads_badge');
            var customerLinkEl = document.getElementById('btn_modal_open_customer_page');

            loadingEl.classList.remove('d-none');
            emptyEl.classList.add('d-none');
            tableWrapEl.classList.add('d-none');
            tbodyEl.innerHTML = '';

            fetch('{{ route('dashboard.leads-detail') }}?date=' + encodeURIComponent(dateStr))
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    loadingEl.classList.add('d-none');
                    if (!data || !data.success) {
                        emptyEl.classList.remove('d-none');
                        return;
                    }

                    titleEl.textContent = 'Detail Leads: ' + data.formatted_date;
                    subtitleEl.textContent = 'Hari ' + data.day_name + ' (' + data.total + ' kontak baru)';
                    badgeEl.textContent = data.total + ' Lead';
                    if (customerLinkEl) {
                        customerLinkEl.href = data.customer_list_url;
                    }

                    if (data.total === 0) {
                        emptyEl.classList.remove('d-none');
                    } else {
                        tableWrapEl.classList.remove('d-none');
                        var html = '';
                        data.leads.forEach(function(lead) {
                            var labelsHtml = '';
                            if (lead.labels && lead.labels.length > 0) {
                                labelsHtml = '<div class="d-flex flex-wrap gap-1">';
                                lead.labels.forEach(function(lbl) {
                                    labelsHtml += '<span class="badge fs-8 py-1 px-2" style="background-color: ' + lbl.color + '18; color: ' + lbl.color + ';">' + escapeHtml(lbl.name) + '</span>';
                                });
                                labelsHtml += '</div>';
                            } else {
                                labelsHtml = '<span class="text-muted fs-8">Tidak ada label</span>';
                            }

                            var csInfo = escapeHtml(lead.assigned_user);
                            if (lead.source) {
                                csInfo += ' <span class="badge badge-light fs-9 text-muted ms-1">' + escapeHtml(lead.source) + '</span>';
                            }

                            html += '<tr>' +
                                '<td>' +
                                    '<div class="d-flex align-items-center">' +
                                        '<div class="symbol symbol-35px symbol-circle me-3">' +
                                            '<div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">' + escapeHtml(lead.initials) + '</div>' +
                                        '</div>' +
                                        '<div class="d-flex flex-column">' +
                                            '<span class="text-gray-800 fw-bold fs-6">' + escapeHtml(lead.name) + '</span>' +
                                        '</div>' +
                                    '</div>' +
                                '</td>' +
                                '<td>' +
                                    '<span class="text-gray-700 fw-semibold fs-7 font-monospace">' + escapeHtml(lead.formatted_phone) + '</span>' +
                                '</td>' +
                                '<td>' +
                                    '<span class="badge badge-light-secondary text-gray-700 fs-8 fw-semibold">' + escapeHtml(lead.created_at_time) + '</span>' +
                                '</td>' +
                                '<td>' + labelsHtml + '</td>' +
                                '<td>' +
                                    '<span class="text-gray-700 fs-7">' + csInfo + '</span>' +
                                '</td>' +
                                '<td class="text-end">' +
                                    '<a href="' + lead.chat_url + '" class="btn btn-sm btn-icon btn-light-success btn-active-success" title="Buka Chat">' +
                                        '<i class="ki-outline ki-message-text-2 fs-4"></i>' +
                                    '</a>' +
                                '</td>' +
                            '</tr>';
                        });
                        tbodyEl.innerHTML = html;
                    }
                })
                .catch(function(err) {
                    loadingEl.classList.add('d-none');
                    emptyEl.classList.remove('d-none');
                });
        }

        function escapeHtml(text) {
            if (!text) return '';
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var chartElement = document.getElementById('chart_daily_leads');
            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            var categories = @json($dailyDates);
            var fullDates = @json($dailyFullDates);
            var leadsData = @json($dailyLeads);

            var options = {
                series: [{
                    name: 'Leads Masuk',
                    data: leadsData
                }],
                chart: {
                    fontFamily: 'inherit',
                    type: 'bar',
                    height: 310,
                    toolbar: {
                        show: false
                    },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            var idx = config.dataPointIndex;
                            if (idx >= 0 && fullDates[idx]) {
                                openLeadsModal(fullDates[idx], categories[idx]);
                            }
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '40%',
                        borderRadius: 4
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: categories,
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            colors: '#7E8299',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    labels: {
                        style: {
                            colors: '#7E8299',
                            fontSize: '12px'
                        },
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                states: {
                    normal: {
                        filter: {
                            type: 'none',
                            value: 0
                        }
                    },
                    hover: {
                        filter: {
                            type: 'none',
                            value: 0
                        }
                    },
                    active: {
                        allowMultipleDataPointsSelection: false,
                        filter: {
                            type: 'none',
                            value: 0
                        }
                    }
                },
                tooltip: {
                    style: {
                        fontSize: '12px'
                    },
                    y: {
                        formatter: function(val) {
                            return val + ' Lead Baru (Klik untuk detail)';
                        }
                    }
                },
                colors: ['#00A884'],
                grid: {
                    borderColor: '#EFF2F5',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                }
            };

            var chart = new ApexCharts(chartElement, options);
            chart.render();
        });
    </script>
    @endpush
</x-metronic-layout>

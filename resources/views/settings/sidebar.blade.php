<!--begin::Aside column-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-300px mb-10 mb-lg-0 me-lg-7 me-xl-10">
    <!--begin::Navigation-->
    <div class="card card-flush py-4 sticky-lg-top" style="top: 80px">
        <div class="card-header">
            <div class="card-title">
                <h2 class="fs-4 fw-bold text-gray-800">Kategori</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('settings.section', 'general') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'general') ? 'active' : '' }}">
                    <i class="ki-outline ki-gear fs-3 me-2"></i> Umum
                </a>
                <a href="{{ route('settings.section', 'whatsapp') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'whatsapp') ? 'active' : '' }}">
                    <i class="ki-outline ki-whatsapp fs-3 me-2"></i> WhatsApp Gateway
                </a>
                <a href="{{ route('settings.section', 'erp') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'erp') ? 'active' : '' }}">
                    <i class="ki-outline ki-cube-2 fs-3 me-2"></i> Sistem ERP
                </a>
                <a href="{{ route('settings.section', 'katalog') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'katalog') ? 'active' : '' }}">
                    <i class="ki-outline ki-shop fs-3 me-2"></i> Katalog Produk
                </a>
                <a href="{{ route('settings.section', 'google') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'google') ? 'active' : '' }}">
                    <i class="ki-outline ki-google fs-3 me-2"></i> Google Contact
                </a>
                <a href="{{ route('settings.section', 'ai') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'ai') ? 'active' : '' }}">
                    <i class="ki-outline ki-abstract-26 fs-3 me-2"></i> AI Assistant
                </a>
                <a href="{{ route('settings.section', 'storage') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'storage') ? 'active' : '' }}">
                    <i class="ki-outline ki-folder-down fs-3 me-2"></i> Cloud Storage
                </a>
                <a href="{{ route('settings.section', 'backup') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ (isset($section) && $section == 'backup') ? 'active' : '' }}">
                    <i class="ki-outline ki-cloud-change fs-3 me-2"></i> Backup & Maintenance
                </a>
                <a href="{{ route('chat-sources.index') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ request()->routeIs('chat-sources.*') ? 'active' : '' }}">
                    <i class="ki-outline ki-compass fs-3 me-2"></i> Sumber Chat
                </a>
                <a href="{{ route('api-keys.index') }}" class="btn btn-color-muted btn-active-light-primary fw-bold px-3 py-3 text-start fs-6 {{ request()->routeIs('api-keys.*') ? 'active' : '' }}">
                    <i class="ki-outline ki-key fs-3 me-2"></i> API Keys
                </a>
            </div>
        </div>
    </div>
    <!--end::Navigation-->
</div>
<!--end::Aside column-->

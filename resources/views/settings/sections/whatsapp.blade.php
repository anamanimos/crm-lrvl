<!--begin::Tabs Header-->
<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-7 fs-5">
    <li class="nav-item">
        <a class="nav-link text-active-primary py-4 {{ $subsection == 'koneksi' ? 'active' : '' }}" href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'koneksi']) }}">
            <i class="ki-outline ki-scan-barcode fs-4 me-2"></i> Koneksi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-active-primary py-4 {{ $subsection == 'pengaturan' ? 'active' : '' }}" href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'pengaturan']) }}">
            <i class="ki-outline ki-setting-2 fs-4 me-2"></i> Gateway
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-active-primary py-4 {{ $subsection == 'broadcast' ? 'active' : '' }}" href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'broadcast']) }}">
            <i class="ki-outline ki-send fs-4 me-2"></i> Broadcast
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-active-primary py-4 {{ $subsection == 'autoreply' ? 'active' : '' }}" href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'autoreply']) }}">
            <i class="ki-outline ki-messages fs-4 me-2"></i> Auto Reply
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-active-primary py-4 {{ $subsection == 'webhook' ? 'active' : '' }}" href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'webhook']) }}">
            <i class="ki-outline ki-notification-on fs-4 me-2"></i> Webhook Logs
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-active-primary py-4 {{ $subsection == 'storage_sync' ? 'active' : '' }}" href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'storage_sync']) }}">
            <i class="ki-outline ki-cloud-sync fs-4 me-2"></i> Sinkron Media
        </a>
    </li>
</ul>
<!--end::Tabs Header-->

<!--begin::Tab Content-->
<div class="tab-content">
    @include('settings.sections.whatsapp.' . $subsection)
</div>
<!--end::Tab Content-->

<x-metronic-layout>
    @php
        $title = 'API Documentation';
    @endphp

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Dokumentasi API
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Docs</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">API</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <div class="row g-5 g-xl-10">
                <!--begin::Col Sidebar-->
                <div class="col-xl-3">
                    <div class="card card-flush sticky-top" style="top: 100px;">
                        <div class="card-body">
                            <nav class="nav flex-column gap-2" id="api_nav">
                                <a href="#intro" class="nav-link text-gray-700 text-hover-primary fw-bold active">Introduction</a>
                                <a href="#auth" class="nav-link text-gray-700 text-hover-primary fw-bold">Authentication</a>
                                <div class="separator my-4"></div>
                                <div class="px-3 text-muted fw-bold fs-8 text-uppercase mb-2">Customers</div>
                                <a href="#list-customers" class="nav-link text-gray-600 text-hover-primary fs-7">List Customers</a>
                                <a href="#get-customer" class="nav-link text-gray-600 text-hover-primary fs-7">Get Customer</a>
                                <a href="#create-customer" class="nav-link text-gray-600 text-hover-primary fs-7">Create Customer</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <!--end::Col Sidebar-->

                <!--begin::Col Content-->
                <div class="col-xl-9">
                    <div class="card card-flush">
                        <div class="card-body p-lg-15">
                            
                            <!-- Intro -->
                            <section id="intro" class="mb-15">
                                <h1 class="text-gray-900 fw-bold fs-2hx mb-4">API Documentation</h1>
                                <p class="text-gray-600 fs-5 mb-8">Selamat datang di Dokumentasi API CRM WhatsApp. API ini memungkinkan Anda untuk mengelola customer, funnel, dan label secara terprogram melalui HTTP request.</p>
                                <div class="rounded border border-dashed border-primary bg-light-primary p-6">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-link fs-2 text-primary me-3"></i>
                                        <div>
                                            <span class="fw-bold text-gray-800 me-2">Base URL:</span>
                                            <code class="text-primary fw-bold">{{ url('/api/v1/') }}</code>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Authentication -->
                            <section id="auth" class="mb-15">
                                <h2 class="text-gray-900 fw-bold fs-2 mb-6 pb-2 border-bottom">Authentication</h2>
                                <p class="text-gray-600 mb-6">Semua permintaan API memerlukan API Key yang harus dikirimkan pada header permintaan.</p>
                                
                                <div class="bg-gray-900 rounded p-6 mb-4">
                                    <p class="text-gray-500 fs-7 fw-bold mb-3 text-uppercase">Custom Header (Direkomendasikan)</p>
                                    <pre class="m-0"><code class="text-success">X-API-KEY: your_api_key_here</code></pre>
                                </div>

                                <div class="bg-gray-900 rounded p-6">
                                    <p class="text-gray-500 fs-7 fw-bold mb-3 text-uppercase">Authorization Header</p>
                                    <pre class="m-0"><code class="text-success">Authorization: Bearer your_api_key_here</code></pre>
                                </div>
                            </section>

                            <div class="separator separator-dashed my-10"></div>

                            <!-- CUSTOMERS -->
                            <section id="customers">
                                <h2 class="text-primary fw-bold fs-1 mb-10">Customers Resource</h2>

                                <!-- LIST -->
                                <div id="list-customers" class="mb-15">
                                    <div class="d-flex align-items-center mb-5">
                                        <span class="badge badge-light-primary fw-bold fs-4 py-2 px-4 me-4">GET</span>
                                        <h3 class="text-gray-800 fw-bold fs-3 m-0">/customers</h3>
                                    </div>
                                    <p class="text-gray-600 mb-6">Mengambil daftar customer dengan pagination dan filter.</p>
                                    
                                    <h4 class="text-gray-500 fw-bold fs-7 text-uppercase mb-3">Query Parameters</h4>
                                    <div class="table-responsive mb-8">
                                        <table class="table table-row-bordered table-row-gray-300 align-middle">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="ps-4 rounded-start">Param</th>
                                                    <th>Type</th>
                                                    <th class="rounded-end">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-4"><code>search</code></td>
                                                    <td><span class="badge badge-light">string</span></td>
                                                    <td>Pencarian berdasarkan nama atau nomor telepon</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-4"><code>label_id</code></td>
                                                    <td><span class="badge badge-light">int</span></td>
                                                    <td>Filter berdasarkan ID Label</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-4"><code>limit</code></td>
                                                    <td><span class="badge badge-light">int</span></td>
                                                    <td>Jumlah data per halaman (default: 50)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <h4 class="text-gray-500 fw-bold fs-7 text-uppercase mb-3">Response</h4>
                                    <pre class="bg-gray-900 rounded p-6 text-success fs-7"><code>{
  "status": true,
  "data": {
    "customers": [
      {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "wa_number": "628123456789",
        "name": "John Doe"
      }
    ],
    "total": 1
  }
}</code></pre>
                                </div>

                                <!-- GET SINGLE -->
                                <div id="get-customer" class="mb-15">
                                    <div class="d-flex align-items-center mb-5">
                                        <span class="badge badge-light-primary fw-bold fs-4 py-2 px-4 me-4">GET</span>
                                        <h3 class="text-gray-800 fw-bold fs-3 m-0">/customers/{id}</h3>
                                    </div>
                                    <p class="text-gray-600 mb-6">Mengambil detail satu customer berdasarkan ID atau nomor WhatsApp.</p>
                                </div>

                                <!-- CREATE -->
                                <div id="create-customer">
                                    <div class="d-flex align-items-center mb-5">
                                        <span class="badge badge-light-success fw-bold fs-4 py-2 px-4 me-4">POST</span>
                                        <h3 class="text-gray-800 fw-bold fs-3 m-0">/customers</h3>
                                    </div>
                                    <p class="text-gray-600 mb-6">Menambahkan customer baru ke sistem.</p>
                                    
                                    <h4 class="text-gray-500 fw-bold fs-7 text-uppercase mb-3">Body Parameters</h4>
                                    <div class="table-responsive mb-8">
                                        <table class="table table-row-bordered table-row-gray-300 align-middle">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="ps-4 rounded-start">Param</th>
                                                    <th>Type</th>
                                                    <th>Required</th>
                                                    <th class="rounded-end">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-4"><code>wa_number</code></td>
                                                    <td><span class="badge badge-light">string</span></td>
                                                    <td><span class="text-danger fw-bold">Yes</span></td>
                                                    <td>Nomor WhatsApp (format 62...)</td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-4"><code>name</code></td>
                                                    <td><span class="badge badge-light">string</span></td>
                                                    <td>No</td>
                                                    <td>Nama Customer</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>

                        </div>
                    </div>
                </div>
                <!--end::Col Content-->
            </div>

        </div>
    </div>
</x-metronic-layout>

@push('js')
<script>
    // Simple smooth scroll
    document.querySelectorAll('#api_nav a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
            document.querySelectorAll('#api_nav a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endpush

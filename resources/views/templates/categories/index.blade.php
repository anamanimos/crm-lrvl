<x-metronic-layout>
    @php
        $title = 'Kategori Template';
    @endphp

    @push('css')
    <link href="{{ asset('assets/css/pages/template-category.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Kategori Template
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.templates.index') }}" class="text-muted text-hover-primary">Template</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Kategori</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.templates.index') }}" class="btn btn-sm fw-bold btn-light-primary">
                    <i class="ki-outline ki-arrow-left fs-4"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <div class="row g-5 g-xl-10">
                <!--begin::Col - Form Area (Left 4 Columns)-->
                <div class="col-lg-4">
                    <div class="card card-flush shadow-sm sticky-lg-top">
                        <div class="card-header">
                            <h3 class="card-title" id="form-title">Tambah Kategori</h3>
                        </div>
                        <form id="category-form" 
                              action="{{ route('admin.templates.category.store') }}" 
                              method="POST"
                              data-store-url="{{ route('admin.templates.category.store') }}"
                              data-update-url-base="{{ url('admin/templates/category/update') }}"
                              data-delete-url-base="{{ url('admin/templates/category/delete') }}">
                            @csrf
                            <div class="card-body py-5">
                                <div class="mb-5">
                                    <label class="required fs-6 fw-semibold mb-2">Nama Kategori</label>
                                    <input type="text" id="category-name" class="form-control form-control-solid" name="name" placeholder="Misal: Greeting" required />
                                    <div class="text-muted fs-7 mt-1">Nama kategori harus unik.</div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end py-6">
                                <button type="button" id="btn-cancel-edit" class="btn btn-light me-3 d-none">Batal</button>
                                <button type="submit" id="btn-submit" class="btn btn-primary">
                                    <span class="indicator-label">Simpan</span>
                                    <span class="indicator-progress">Mohon tunggu...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!--end::Col-->

                <!--begin::Col - Table Area (Right 8 Columns)-->
                <div class="col-lg-8">
                    <div class="card card-flush shadow-sm">
                        <div class="card-body py-4">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-200px">Nama Kategori</th>
                                        <th class="min-w-100px">Slug</th>
                                        <th class="text-end min-w-100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    @forelse ($categories as $category)
                                    <tr>
                                        <td>
                                            <span class="text-gray-800 fw-bold">{{ $category->name }}</span>
                                        </td>
                                        <td><code class="fs-8">{{ $category->slug }}</code></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1 btn-edit-category" 
                                                    data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                                                <i class="ki-outline ki-pencil fs-5"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-light-danger btn-sm btn-delete-category" 
                                                     data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                                                <i class="ki-outline ki-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-10 text-muted">Belum ada kategori</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>

        </div>
    </div>

    @push('js')
    <script src="{{ asset('assets/js/pages/template-category.js') }}"></script>
    @endpush
</x-metronic-layout>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Claim Admin - CRM SEVENCOLS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-center flex-column-fluid p-10">
            <div class="card w-lg-500px">
                <div class="card-body p-10">
                    <div class="text-center mb-11">
                        <h1 class="text-gray-900 fw-bolder mb-3">Akses Dibatasi</h1>
                        <div class="text-gray-500 fw-semibold fs-6">
                            Akun Anda berhasil dibuat namun belum aktif. <br>
                            Silakan hubungi admin untuk mendapatkan akses.
                        </div>
                    </div>

                    <div class="separator separator-content my-14">
                        <span class="w-250px text-gray-500 fw-semibold fs-7">Atau Claim Posisi Admin</span>
                    </div>

                    <form action="{{ route('auth.claim-admin.post') }}" method="POST">
                        @csrf
                        <div class="fv-row mb-8">
                            <input type="password" placeholder="Secret Claim Code" name="secret_code" autocomplete="off" 
                                   class="form-control bg-transparent @error('secret_code') is-invalid @enderror" required />
                            @error('secret_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-10">
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Claim Admin Access</span>
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-link link-primary fw-bold">Kembali ke Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

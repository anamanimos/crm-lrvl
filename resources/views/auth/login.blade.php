<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Login - CRM SEVENCOLS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <!-- Left Side - Branding -->
            <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center" 
                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
                    <a href="/" class="mb-12">
                        <h1 class="text-white fs-2qx fw-bold">CRM SEVENCOLS</h1>
                    </a>
                    <h1 class="d-none d-lg-block text-white fs-2qx fw-bold text-center mb-7">
                        Customer Relationship Management
                    </h1>
                    <div class="d-none d-lg-block text-white fs-base text-center">
                        Kelola customer dan komunikasi WhatsApp dalam satu platform
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <div class="w-lg-500px p-10">
                        <form class="form w-100" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="text-center mb-11">
                                <h1 class="text-gray-900 fw-bolder mb-3">Masuk</h1>
                                <div class="text-gray-500 fw-semibold fs-6">Silakan masuk untuk melanjutkan</div>
                            </div>
                            
                            @if($errors->any())
                            <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                                <div class="d-flex flex-column">
                                    @foreach ($errors->all() as $error)
                                        <span>{{ $error }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if (session('status'))
                            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                                <div class="d-flex flex-column">
                                    <span>{{ session('status') }}</span>
                                </div>
                            </div>
                            @endif
                            
                            <div class="fv-row mb-8">
                                <input type="text" placeholder="Username" name="username" value="{{ old('username') }}" autocomplete="off" 
                                       class="form-control bg-transparent @error('username') is-invalid @enderror" required autofocus />
                            </div>
                            
                            <div class="fv-row mb-3">
                                <input type="password" placeholder="Password" name="password" autocomplete="off" 
                                       class="form-control bg-transparent @error('password') is-invalid @enderror" required />
                            </div>
                            
                            <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me" />
                                    <label class="form-check-label text-gray-700" for="remember_me">
                                        Ingat Saya
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="link-primary">
                                        Lupa Password?
                                    </a>
                                @endif
                            </div>
                            
                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label">Masuk</span>
                                </button>
                            </div>

                            <div class="separator separator-content my-14">
                                <span class="w-125px text-gray-500 fw-semibold fs-7">Atau</span>
                            </div>

                            <div class="d-grid mb-10">
                                <a href="{{ route('oidc.redirect') }}" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                    <i class="ki-outline ki-entrance-left fs-2 me-3"></i>
                                    Masuk dengan SSO
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="w-lg-500px d-flex flex-stack px-10 mx-auto">
                    <div class="d-flex fw-semibold text-primary fs-base gap-5">
                        <span class="text-gray-500">{{ date('Y') }} &copy; SEVENCOLS Konveksi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
</body>
</html>

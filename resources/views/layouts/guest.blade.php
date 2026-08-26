<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Virtual Data Room'))</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-180.png') }}">

    {{-- Bootstrap --}}
    <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- Custom CSS --}}
    <link href="{{ asset('frontend/css/style.css') }}" rel="stylesheet">
    @stack('addOnCss')
</head>

<body class="login-body">
    <div id="toastBox" style="position: fixed; top:20px; right:20px; z-index:9999;"></div>

    <div class="container-fluid">
        <div class="row vh-100">

            {{-- LEFT SIDE (Branding) --}}
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center login-left">
                <div class="text-center text-white px-5 position-relative">

                    {{-- Logo --}}
                    <img src="{{ asset('admin/images/kingsmeal-agro-logo.png') }}" alt="Logo" class="login-logo mb-4">

                    {{-- <p class="mt-3 opacity-75">
                        Manage your system efficiently with a powerful dashboard.
                    </p> --}}

                </div>
            </div>

            {{-- RIGHT SIDE (FORM) --}}
            <div class="col-lg-6 d-flex align-items-center justify-content-center">

                <div class="login-card">

                    @yield('content')

                </div>

            </div>

        </div>
    </div>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    @stack('script')
</body>

</html>

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

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- CSS --}}
    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- <link href="{{ asset('admin/css/all.min.css') }}" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">

    @stack('addOnCss')
</head>

<body>

    <div class="app-wrapper">

        {{-- Sidebar --}}
        <aside class="sidebar">
            @include('admin.components.sidebar')
        </aside>

        {{-- Main --}}
        <div class="main-wrapper">
            <div id="toastBox" style="position: fixed; top:20px; right:20px; z-index:9999;"></div>
            {{-- Header --}}
            <header class="header">
                @include('admin.components.header')
            </header>

            {{-- Content --}}
            <main class="content">

                <div class="container-fluid">

                    {{-- @hasSection('page_title')
                        <div class="mb-4">
                            <h4 class="fw-bold text-dark text-capitalize">
                                @yield('page_title')
                            </h4>
                        </div>
                    @endif --}}

                    @yield('content')

                </div>

            </main>

            {{-- Footer --}}
            <footer class="footer text-center py-3">
                <small>
                    &copy; {{ now()->year }}
                    <strong>{{ config('app.name', 'Virtual Data Room') }}</strong>.
                    All rights reserved.
                </small>
            </footer>

        </div>

    </div>

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("{{ session('success') }}", "success");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("{{ session('error') }}", "danger");
            });
        </script>
    @endif
    <script src="{{ asset('admin/js/custom.js') }}"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script>

    @stack('script')

    <script>
    (function () {
        var header    = document.querySelector('.header');
        var threshold = 60;
        if (!header) return;
        window.addEventListener('scroll', function () {
            header.classList.toggle('header-scrolled', window.scrollY > threshold);
        }, { passive: true });
    })();
    </script>

</body>

</html>

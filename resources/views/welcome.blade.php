<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Virtual Data Room ') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-180.png') }}">

    {{-- Bootstrap --}}
    <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Custom CSS --}}
    <link href="{{ asset('frontend/css/style.css') }}" rel="stylesheet">
</head>

<body class="landing-body">
    <canvas id="networkCanvas"></canvas>
    <div class="network-bg">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

<div class="landing-wrapper d-flex align-items-center justify-content-center">

    <div class="text-center landing-card">

        {{-- Logo --}}
        <img src="{{ asset('frontend/images/logo.png') }}" class="landing-logo mb-3">

        {{-- Title --}}
        <h1 class="fw-bold mb-2">
            Welcome to Virtual Data Room 
        </h1>

        <p class="text-muted mb-4">
            Manage your system with a powerful and modern admin dashboard.
        </p>

        {{-- Buttons --}}
        <div class="d-flex justify-content-center gap-3">

            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4">
                    Go to Dashboard
                </a>
                <script>
                    // Already logged in — no need to make them click through.
                    window.location.replace("{{ url('/dashboard') }}");
                </script>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary px-4">
                    Login
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-outline-primary px-4">
                        Register
                    </a>
                @endif
            @endauth

        </div>

    </div>

</div>

<script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}"></script>

<script>
    const canvas = document.getElementById("networkCanvas");
    const ctx = canvas.getContext("2d");
    
    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    let dots = [];
    
    function initDots() {
        dots = [];
        for (let i = 0; i < 60; i++) {
            dots.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                dx: (Math.random() - 0.5),
                dy: (Math.random() - 0.5)
            });
        }
    }
    initDots();
    
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    
        dots.forEach(dot => {
            dot.x += dot.dx;
            dot.y += dot.dy;
    
            // Bounce edges
            if (dot.x < 0 || dot.x > canvas.width) dot.dx *= -1;
            if (dot.y < 0 || dot.y > canvas.height) dot.dy *= -1;
    
            ctx.beginPath();
            ctx.arc(dot.x, dot.y, 2, 0, Math.PI * 2);
            ctx.fillStyle = "rgba(255,255,255,0.7)";
            ctx.fill();
    
            dots.forEach(other => {
                let dist = Math.hypot(dot.x - other.x, dot.y - other.y);
                if (dist < 120) {
                    ctx.beginPath();
                    ctx.moveTo(dot.x, dot.y);
                    ctx.lineTo(other.x, other.y);
                    ctx.strokeStyle = "rgba(255,255,255,0.1)";
                    ctx.stroke();
                }
            });
        });
    
        requestAnimationFrame(draw);
    }
    
    draw();
    </script>

</body>
</html>
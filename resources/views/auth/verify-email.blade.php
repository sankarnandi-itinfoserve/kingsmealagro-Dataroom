@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')

{{-- Header --}}
<div class="text-center mb-4">
    <img src="/logo.png" class="login-logo mb-2">
    <h4 class="fw-bold">Verify your email</h4>
    <p class="text-muted small">
        Please verify your email address to continue
    </p>
</div>

{{-- Info Message --}}
<div class="alert alert-light border small">
    Thanks for signing up! Before getting started, please verify your email 
    by clicking the link we just sent. If you didn’t receive it, you can request another.
</div>

{{-- Success Message --}}
@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success">
        A new verification link has been sent to your email address.
    </div>
@endif

{{-- Actions --}}
<div class="d-grid gap-2">

    {{-- Resend Email --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100">
            Resend Verification Email
        </button>
    </form>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">
            Logout
        </button>
    </form>

</div>

@endsection
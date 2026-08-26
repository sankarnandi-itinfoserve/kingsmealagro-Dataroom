@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')

{{-- Info Text --}}
<div class="text-center mb-4">
    <img src="{{ asset('frontend/images/logo.png') }}" class="login-logo mb-2">
    <h4 class="fw-bold">Confirm your password</h4>
    <p class="text-muted small">Security check before proceeding</p>
</div>

<div class="mb-3 text-muted small">
    This is a secure area. Please confirm your password to continue.
</div>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    {{-- Password --}}
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               required autofocus>

        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('login') }}" class="small text-muted">
            Back to login
        </a>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        Confirm Password
    </button>

</form>

@endsection
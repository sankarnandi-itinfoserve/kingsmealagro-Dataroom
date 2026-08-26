@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')

{{-- Header --}}
<div class="text-center mb-4">
    <img src="/logo.png" class="login-logo mb-2">
    <h4 class="fw-bold">Reset your password</h4>
    <p class="text-muted small">Enter your new password below</p>
</div>

<form method="POST" action="{{ route('password.store') }}">
    @csrf

    {{-- Token --}}
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    {{-- Email --}}
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email"
               value="{{ old('email', $request->email) }}"
               class="form-control @error('email') is-invalid @enderror"
               required autofocus>

        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Password --}}
    <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               required>

        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation"
               class="form-control @error('password_confirmation') is-invalid @enderror"
               required>

        @error('password_confirmation')
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
        Reset Password
    </button>

</form>

@endsection

@push('script')
<script>
    document.querySelectorAll('input[type="password"]').forEach(input => {
        input.addEventListener('focus', () => {
            input.type = 'text';
        });
        input.addEventListener('blur', () => {
            input.type = 'password';
        });
    });
    </script>
@endpush
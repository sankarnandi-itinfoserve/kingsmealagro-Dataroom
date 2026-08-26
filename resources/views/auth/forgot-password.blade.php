@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')

    <div class="rg-wrap">

        {{-- Header --}}
        <div class="rg-header">
            <div class="rg-header-icon"><i class="fa-solid fa-key"></i></div>
            <div>
                <h5 class="rg-title">Forgot Password?</h5>
                <p class="rg-sub">Enter your email and we'll send you a reset link.</p>
            </div>
        </div>

        <div class="rg-form">

            {{-- Success status --}}
            @if (session('status'))
                <div class="rg-alert rg-alert-success">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="rg-label">Email Address</label>
                    <div class="rg-input-wrap">
                        <i class="fa-solid fa-envelope rg-input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="rg-input @error('email') rg-input-err @enderror" required autofocus>
                    </div>
                    @error('email')
                        <span class="rg-err">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="rg-submit-btn">
                    <i class="fa-solid fa-paper-plane me-2"></i>Send Reset Link
                </button>

            </form>

            <p class="rg-login-link mt-3">
                Remembered your password? <a href="{{ route('login') }}">Sign in</a>
            </p>

        </div>

    </div>

@endsection

@push('addOnCss')
    <style>
        .login-card {
            width: 66%;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }

        .rg-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(37, 52, 71, .13);
            overflow: hidden;
            width: 100%;
        }

        .rg-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 28px;
            background: linear-gradient(135deg, #1a2737 0%, #253447 100%);
        }

        .rg-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            flex-shrink: 0;
        }

        .rg-title {
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 2px;
            letter-spacing: -.2px;
        }

        .rg-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, .55);
            margin: 0;
        }

        .rg-form {
            padding: 24px 28px 28px;
        }

        .rg-alert {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
        }

        .rg-alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .rg-alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .rg-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .rg-input-wrap {
            position: relative;
        }

        .rg-input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #94a3b8;
            pointer-events: none;
        }

        .rg-input {
            width: 100%;
            height: 40px;
            padding: 0 12px 0 32px;
            border: 1.5px solid #dbe4f0;
            border-radius: 9px;
            font-size: 13px;
            color: #1e293b;
            background: #f9fafb;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .rg-input:focus {
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(100, 116, 139, .10);
        }

        .rg-input-err {
            border-color: #dc2626 !important;
        }

        .rg-err {
            display: block;
            font-size: 11.5px;
            color: #dc2626;
            margin-top: 3px;
        }

        .rg-submit-btn {
            width: 100%;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #253447, #1a2737);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 3px 12px rgba(37, 52, 71, .3);
            margin-bottom: 4px;
        }

        .rg-submit-btn:hover {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 5px 18px rgba(37, 52, 71, .38);
        }

        .rg-login-link {
            text-align: center;
            font-size: 12.5px;
            color: #64748b;
            margin: 0;
        }

        .rg-login-link a {
            color: #c0272d;
            font-weight: 700;
            text-decoration: none;
        }

        .rg-login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 991px) {
            .login-card {
                width: 90%;
            }
        }
    </style>
@endpush

@extends('layouts.guest')

@section('title', 'Login')

@section('content')

    <div class="rg-wrap">

        {{-- Header --}}
        <div class="rg-header">
            <div class="rg-header-icon"><i class="fa-solid fa-right-to-bracket"></i></div>
            <div>
                <h5 class="rg-title">Welcome Back</h5>
                <p class="rg-sub">Sign in to your account to continue.</p>
            </div>
        </div>

        <div class="rg-form">

            {{-- Alerts --}}
            @if ($errors->has('attempts'))
                <div class="rg-alert rg-alert-danger">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first('attempts') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rg-alert rg-alert-danger">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                </div>
            @endif
            @if (session('generic'))
                <div class="rg-alert rg-alert-danger">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('generic') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label class="rg-label">Email Address</label>
                    <div class="rg-input-wrap">
                        <i class="fa-solid fa-envelope rg-input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="rg-input @error('email') rg-input-err @enderror" required autocomplete="off">
                    </div>
                    @error('email')
                        <span class="rg-err">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label class="rg-label">Password</label>
                    <div class="rg-input-wrap">
                        <i class="fa-solid fa-lock rg-input-icon"></i>
                        <input type="password" id="loginPassword" name="password"
                            class="rg-input rg-input-eye @error('password') rg-input-err @enderror" required>
                        <button type="button" class="rg-eye-btn" onclick="togglePassword('loginPassword', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="rg-err">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Forgot --}}
                <div class="rg-row-end mb-4">
                    <a href="{{ route('password.request') }}" class="rg-forgot">Forgot password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit" class="rg-submit-btn">
                    Sign In
                </button>

            </form>





            {{-- <p class="rg-login-link mt-3">
                Don't have an account? <a href="{{ route('register') }}">Create one</a>
            </p> --}}

        </div>

    </div>

@endsection

@push('addOnCss')
    <style>
        /* Override login-card */
        .login-card {
            width: 66%;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }

        /* ── Wrapper ── */
        .rg-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(37, 52, 71, .13);
            overflow: hidden;
            width: 100%;
        }

        /* ── Header ── */
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

        /* ── Form body ── */
        .rg-form {
            padding: 24px 28px 28px;
        }

        /* ── Alerts ── */
        .rg-alert {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .rg-alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* ── Labels ── */
        .rg-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        /* ── Input wrap ── */
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

        .rg-input-eye {
            padding-right: 38px;
        }

        .rg-eye-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            color: #94a3b8;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
            transition: color .15s;
        }

        .rg-eye-btn:hover {
            color: #253447;
        }

        .rg-err {
            display: block;
            font-size: 11.5px;
            color: #dc2626;
            margin-top: 3px;
        }

        /* ── Forgot ── */
        .rg-row-end {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .rg-forgot {
            font-size: 12.5px;
            color: #c0272d;
            font-weight: 600;
            text-decoration: none;
        }

        .rg-forgot:hover {
            text-decoration: underline;
        }

        /* ── Submit ── */
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
            margin-bottom: 16px;
        }

        .rg-submit-btn:hover {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 5px 18px rgba(37, 52, 71, .38);
        }

        /* ── Footer link ── */
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

@push('script')
    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        }
        $(function() {
            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            @if (session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif
            @if ($errors->has('attempts'))
                showToast("{{ $errors->first('attempts') }}", 'error');
            @endif
        });
    </script>
@endpush

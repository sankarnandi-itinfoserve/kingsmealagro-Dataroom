@extends('admin.layouts.app')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')

    <div class="container-fluid pf-page">

        {{-- ── Hero Banner ── --}}
        <div class="pf-hero">
            <div class="pf-hero-bg"></div>
            <div class="pf-hero-content">
                <div class="pf-avatar-wrap">
                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="pf-avatar-img">
                    @else
                        <i class="fa-solid fa-circle-user pf-avatar-icon"></i>
                    @endif
                    <span class="pf-avatar-badge"><i class="fa-solid fa-circle-check"></i></span>
                </div>
                <div class="pf-hero-info">
                    <h4 class="pf-hero-name">{{ auth()->user()->full_name }}</h4>
                    <p class="pf-hero-email"><i class="fa-solid fa-envelope me-1"></i>{{ auth()->user()->email ?? '' }}</p>
                    <div class="pf-hero-roles">
                        @foreach (auth()->user()->roles as $role)
                            <span class="pf-role-chip">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="pf-hero-stats">
                    <div class="pf-stat">
                        <span class="pf-stat-value">{{ auth()->user()->created_at->format('M Y') }}</span>
                        <span class="pf-stat-label">Member Since</span>
                    </div>
                    {{-- <div class="pf-stat-divider"></div>
                    <div class="pf-stat">
                        <span class="pf-stat-value">{{ auth()->user()->roles->count() }}</span>
                        <span class="pf-stat-label">{{ Str::plural('Role', auth()->user()->roles->count()) }}</span>
                    </div> --}}
                </div>
            </div>
        </div>

        {{-- ── Forms ── --}}
        <div class="row g-4 pf-body">

            {{-- Profile Information --}}
            <div class="col-lg-6">
                <div class="pf-card">
                    <div class="pf-card-header">
                        <span class="pf-card-header-icon"><i class="fa-solid fa-user-pen"></i></span>
                        <div>
                            <p class="pf-card-title">Profile Information</p>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="pf-card">
                    <div class="pf-card-header">
                        <span class="pf-card-header-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <div>
                            <p class="pf-card-title">Change Password</p>
                            <p class="pf-card-sub">Use a strong, unique password to keep your account secure.</p>
                        </div>
                    </div>
                    <div class="pf-card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('addOnCss')
    <style>
        /* ── Page wrapper ── */
        .pf-page {
            padding-bottom: 40px;
        }

        /* ── Hero ── */
        .pf-hero {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 28px;
            box-shadow: 0 4px 24px rgba(37, 52, 71, .14);
        }

        .pf-hero-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #1a2737 0%, #253447 60%, #2e4a6e 100%);
        }

        .pf-hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .pf-hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 28px;
            padding: 32px 36px;
            flex-wrap: wrap;
        }

        /* Avatar */
        .pf-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .pf-avatar-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, .25);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .3);
            object-fit: cover;
            display: block;
        }

        .pf-avatar-icon {
            font-size: 90px;
            line-height: 1;
            display: block;
            color: rgba(255, 255, 255, .45);
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, .3));
        }

        .pf-avatar-badge {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #22c55e;
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #1a2737;
        }

        /* Hero info */
        .pf-hero-info {
            flex: 1;
            min-width: 180px;
        }

        .pf-hero-name {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            letter-spacing: -.3px;
        }

        .pf-hero-email {
            font-size: 13px;
            color: rgba(255, 255, 255, .65);
            margin: 0 0 10px;
        }

        .pf-hero-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .pf-role-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: rgba(192, 39, 45, .85);
            color: #fff;
            letter-spacing: .2px;
            border: 1px solid rgba(255, 255, 255, .15);
            text-transform: capitalize;
        }

        /* Stats */
        .pf-hero-stats {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .pf-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .pf-stat-value {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .pf-stat-label {
            font-size: 11px;
            color: rgba(255, 255, 255, .55);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .pf-stat-divider {
            width: 1px;
            height: 36px;
            background: rgba(255, 255, 255, .15);
        }

        /* ── Cards ── */
        .pf-card {
            background: #fff;
            border: 1px solid #e9eef6;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .07);
            height: 100%;
        }

        .pf-card-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #253447, #1a2737);
        }

        .pf-card-header-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .pf-card-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0 0 2px;
        }

        .pf-card-sub {
            font-size: 11.5px;
            color: rgba(255, 255, 255, .55);
            margin: 0;
        }

        .pf-card-body {
            padding: 24px 20px;
        }

        /* ── Form inputs inside cards ── */
        .pf-card-body .form-label,
        .pf-card-body label.block,
        .pf-card-body x-input-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }

        .pf-card-body .form-control,
        .pf-card-body input[type="text"],
        .pf-card-body input[type="email"],
        .pf-card-body input[type="password"] {
            height: 40px;
            border: 1.5px solid #dbe4f0;
            border-radius: 9px;
            font-size: 13px;
            padding: 0 12px;
            color: #1e293b;
            background: #f9fafb;
            transition: border-color .15s, box-shadow .15s, background .15s;
            width: 100%;
            outline: none;
        }

        .pf-card-body .form-control:focus,
        .pf-card-body input[type="text"]:focus,
        .pf-card-body input[type="email"]:focus,
        .pf-card-body input[type="password"]:focus {
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(100, 116, 139, .10);
        }

        /* ── Save buttons ── */
        .pf-card-body .btn-primary,
        .pf-card-body .pf-save-btn,
        .pf-card-body button[type="submit"] {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 22px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #253447, #1a2737);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 3px 10px rgba(37, 52, 71, .3);
            white-space: nowrap;
            text-decoration: none;
        }

        .pf-card-body .btn-primary:hover,
        .pf-card-body .pf-save-btn:hover,
        .pf-card-body button[type="submit"]:hover {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(37, 52, 71, .38);
            background: linear-gradient(135deg, #2e3f56, #253447);
            color: #fff;
        }

        /* ── Success message ── */
        .pf-card-body .text-success,
        .pf-card-body .text-sm.text-gray-600 {
            font-size: 12px;
            color: #16a34a !important;
            font-weight: 600;
        }

        /* ── Error messages ── */
        .pf-card-body .text-danger,
        .pf-card-body .text-sm.text-red-600 {
            font-size: 11.5px;
            color: #dc2626 !important;
            margin-top: 3px;
            display: block;
        }

        /* ── Section headers inside partials ── */
        .pf-card-body section>header {
            display: none;
        }

        /* ── Alpine/Tailwind spacing helpers ── */
        .pf-card-body .mt-6 {
            margin-top: 0 !important;
        }

        .pf-card-body .space-y-6>*+* {
            margin-top: 16px;
        }

        .pf-card-body .mt-1 {
            margin-top: 4px !important;
        }

        .pf-card-body .mt-4 {
            margin-top: 20px !important;
        }

        .pf-card-body .mt-2 {
            margin-top: 6px !important;
        }

        .pf-card-body .flex {
            display: flex;
        }

        .pf-card-body .items-center {
            align-items: center;
        }

        .pf-card-body .gap-4 {
            gap: 16px;
        }

        .pf-card-body .block {
            display: block;
        }

        .pf-card-body .w-full {
            width: 100%;
        }

        @media (max-width: 767px) {
            .pf-hero-content {
                padding: 24px 20px;
            }

            .pf-hero-stats {
                margin-left: 0;
                width: 100%;
                justify-content: flex-start;
            }

            .pf-hero-name {
                font-size: 18px;
            }
        }
    </style>
@endpush

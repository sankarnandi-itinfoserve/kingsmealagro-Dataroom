@extends('admin.layouts.app')

@section('title', 'Create Project')
@section('page_title', 'Create Project')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-3 d-flex align-items-start gap-2" style="font-size:13.5px;">
            <i class="fa-solid fa-circle-exclamation mt-1 flex-shrink-0"></i>
            <div>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Main form col ────────────────────────────────────────────────────── --}}
        <div class="col-lg-7 col-xl-6 mx-auto">
            <form action="{{ route('projects.store') }}" method="POST" id="projectCreateForm">
                @csrf

                {{-- Section 01: Project Details --}}
                <div class="prj-form-card">
                    <div class="prj-form-section-header">
                        <div class="prj-section-title">Project Details</div>
                    </div>
                    <div class="prj-form-section-body">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="prj-label">
                                    Project Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="previewName"
                                    class="prj-input @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="" required autocomplete="off">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                        </div>

                        {{-- Submit row --}}
                        <div class="prj-form-actions mt-4">
                            <button type="submit" class="prj-submit-btn" id="submitBtn">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                Create Project
                            </button>
                            <a href="{{ route('projects.index') }}" class="prj-cancel-btn">
                                Cancel
                            </a>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        /* ── Page header ──────────────────────────────────────────────────────────── */
        .prj-create-header {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .07);
        }

        .prj-create-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #253447;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .prj-create-header-body {
            flex: 1;
            min-width: 0;
        }

        .prj-create-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 3px;
        }

        .prj-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #94a3b8;
        }

        .prj-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .prj-breadcrumb a:hover {
            color: #253447;
        }

        .prj-breadcrumb i {
            font-size: 9px;
        }

        .prj-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background .13s, border-color .13s, color .13s;
            flex-shrink: 0;
        }

        .prj-back-btn:hover {
            background: #253447;
            border-color: #253447;
            color: #fff;
        }

        /* ── Form cards ───────────────────────────────────────────────────────────── */
        .prj-form-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            margin-bottom: 16px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .prj-form-section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 22px;
            background: #2D3E50;
            border-bottom: none;
        }

        .prj-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        .prj-form-section-body {
            padding: 22px;
        }

        /* ── Inputs ───────────────────────────────────────────────────────────────── */
        .prj-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .prj-input {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: 13.5px;
            color: #1e293b;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
        }

        .prj-input:focus {
            border-color: #253447;
            box-shadow: 0 0 0 3px rgba(37, 52, 71, .09);
        }

        .prj-input.is-invalid {
            border-color: #dc2626;
        }

        .prj-input.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .1);
        }

        select.prj-input {
            cursor: pointer;
        }

        .prj-input-group {
            position: relative;
        }

        .prj-input-prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 12px;
            pointer-events: none;
            z-index: 1;
        }

        /* ── Submit row ───────────────────────────────────────────────────────────── */
        .prj-form-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 4px;
        }

        .prj-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 28px;
            border-radius: 10px;
            background: #253447;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .13s, box-shadow .13s, transform .1s;
            box-shadow: 0 4px 14px rgba(37, 52, 71, .22);
        }

        .prj-submit-btn:hover {
            background: #1a2737;
            box-shadow: 0 6px 18px rgba(37, 52, 71, .28);
            transform: translateY(-1px);
        }

        .prj-submit-btn:active {
            transform: translateY(0);
        }

        .prj-cancel-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: transparent;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: background .12s, border-color .12s, color .12s;
        }

        .prj-cancel-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #374151;
        }

        /* ── Sidebar cards ────────────────────────────────────────────────────────── */
        .prj-sidebar-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .prj-sidebar-card-header {
            padding: 13px 18px;
            font-size: 12.5px;
            font-weight: 700;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .prj-sidebar-card-body {
            padding: 18px;
        }

        /* ── Preview card ─────────────────────────────────────────────────────────── */
        .prj-preview-card {}

        .prj-preview-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.3;
            min-height: 22px;
            word-break: break-word;
        }

        .prj-preview-meta {
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .prj-preview-dates {
            font-size: 11.5px;
            color: #94a3b8;
        }

        /* ── Status badge (also used in index) ───────────────────────────────────── */
        .prj-status-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .prj-status-active {
            background: #dcfce7;
            color: #16a34a;
        }

        .prj-status-closed {
            background: #fee2e2;
            color: #dc2626;
        }

        .prj-status-archived {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* ── Checklist ────────────────────────────────────────────────────────────── */
        .prj-checklist {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .prj-checklist li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid #f8fafc;
        }

        .prj-checklist li:last-child {
            border-bottom: none;
        }

        .prj-check-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .prj-check-sp {
            background: #eff6ff;
            color: #2563eb;
        }

        .prj-check-db {
            background: #f0fdf4;
            color: #16a34a;
        }

        .prj-check-title {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }

        .prj-check-sub {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* ── Tip card ─────────────────────────────────────────────────────────────── */
        .prj-tip-card {}
    </style>
@endpush

@push('script')
    <script>
        $(function() {

            /* ── Submit loading state ─────────────────────────────────────────────── */
            $('#projectCreateForm').on('submit', function() {
                var $btn = $('#submitBtn');
                $btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Creating…').prop('disabled', true);
            });

        });
    </script>
@endpush

@extends('admin.layouts.app')
@section('title', 'Invite User')
@section('page_title', 'Invite Users')

@section('content')

<div class="container-fluid fb-browser-page">

    {{-- ── Top grid: form + side panel ── --}}
    <div class="inv-layout">

        {{-- Left: Form card --}}
        <div class="inv-form-card fb-browser-card">

            {{-- Card header --}}
            <div class="inv-card-header">
                <div class="inv-card-header-icon">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <div>
                    <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                        <span class="fb-crumb-current">Invite Users</span>
                    </nav>
                    <p class="inv-card-sub">Send a secure sign-up link to grant access to the data room.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('invitations.store') }}" id="inviteForm">
                @csrf

                {{-- Email field --}}
                <div class="inv-field-group">
                    <label class="inv-label" for="inviteEmail">
                        <i class="fa-solid fa-at me-1" style="color:#c0272d;"></i>
                        Recipient Email Address
                    </label>
                    <div class="inv-input-wrap">
                        <input
                            type="email"
                            name="email"
                            id="inviteEmail"
                            class="inv-input @error('email') is-invalid @enderror"
                            placeholder="e.g. colleague@company.com"
                            value="{{ old('email') }}"
                            required
                            autocomplete="off"
                        >
                        <span class="inv-input-icon"><i class="fa-solid fa-envelope"></i></span>
                    </div>
                    @error('email')
                        <div class="inv-error">
                            <i class="fa fa-circle-exclamation me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Info box --}}
                <div class="inv-info-box">
                    <div class="inv-info-icon-wrap">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <p class="inv-info-title">What happens next?</p>
                        <ul class="inv-info-list">
                            <li>A secure sign-up link is emailed to the address above.</li>
                            <li>The link expires in <strong>48 hours</strong>.</li>
                            <li>The recipient completes registration and gains access.</li>
                        </ul>
                    </div>
                </div>

                @can('manage invite_users')
                    <button type="submit" class="inv-submit-btn" id="inviteSubmitBtn">
                        <i class="fa-solid fa-paper-plane me-2"></i>
                        Send Invitation
                    </button>
                @else
                    <div class="inv-no-perm">
                        <i class="fa fa-lock me-2"></i>
                        You don't have permission to send invitations.
                    </div>
                @endcan

            </form>
        </div>

        {{-- Right: Side panel --}}
        <div class="inv-side-panel">

            <div class="inv-side-card">
                <div class="inv-side-icon" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#c0272d;">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="inv-side-text">
                    <p class="inv-side-title">Invite by Email</p>
                    <p class="inv-side-sub">Enter any valid email address. The system sends a one-time secure sign-up link.</p>
                </div>
            </div>

            <div class="inv-side-card">
                <div class="inv-side-icon" style="background:linear-gradient(135deg,#eef1f5,#dde3ec);color:#253447;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="inv-side-text">
                    <p class="inv-side-title">Secure Access</p>
                    <p class="inv-side-sub">Each invitation token is unique and expires after 48 hours for security.</p>
                </div>
            </div>

            <div class="inv-side-card">
                <div class="inv-side-icon" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#9b1c21;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div class="inv-side-text">
                    <p class="inv-side-title">Role Assignment</p>
                    <p class="inv-side-sub">Roles and permissions can be assigned to the user after they join.</p>
                </div>
            </div>

            {{-- Quick stat --}}
            <div class="inv-stat-card">
                <div class="inv-stat-row">
                    <span class="inv-stat-dot" style="background:#c0272d;"></span>
                    <span class="inv-stat-label">Invitations sent via secure email</span>
                </div>
                <div class="inv-stat-row mt-2">
                    <span class="inv-stat-dot" style="background:#253447;"></span>
                    <span class="inv-stat-label">Access granted upon registration</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Sent Invitations table ── --}}
    <div class="inv-list-card fb-browser-card mt-4">

        <div class="inv-list-header">
            <div class="inv-list-header-icon">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <div>
                <p class="inv-list-title">Sent Invitations</p>
                <p class="inv-list-sub">Track all pending, accepted, and expired invitations.</p>
            </div>
        </div>

        {{-- Status legend --}}
        <div class="inv-legend-bar">
            <span class="inv-legend-chip inv-legend-accepted">
                <i class="fas fa-check-circle me-1"></i>Accepted
            </span>
            <span class="inv-legend-chip inv-legend-pending">
                <i class="fas fa-clock me-1"></i>Pending
            </span>
            <span class="inv-legend-chip inv-legend-expired">
                <i class="fas fa-times-circle me-1"></i>Expired
            </span>
        </div>

        <div class="table-responsive">
            <table class="table inv-table" id="invitationTable">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Email</th>
                        <th>Expires At</th>
                        <th style="width:120px;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>

</div>

@endsection


@push('addOnCss')
<style>
    

</style>
@endpush


@push('script')
@if(session('success'))
<script>showToast("{{ session('success') }}", 'success');</script>
@endif

<script>
$(function () {

    // ── DataTable ──────────────────────────────
    $('#invitationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('invitations.list') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'email',      name: 'email', orderSequence: ['asc', 'desc', ''] },
            { data: 'expires_at', name: 'expires_at', orderSequence: ['asc', 'desc', ''] },
            { data: 'action',     orderable: false, searchable: false },
        ],
        order: [[2, 'desc']],
        pageLength: 10,
    });

    // ── Resend ─────────────────────────────────
    $(document).on('click', '.resendBtn', function () {
        let url = "{{ route('invite.resend', ':id') }}".replace(':id', $(this).data('id'));
        Swal.fire({
            title: 'Resend Invitation?',
            text: 'A fresh secure link will be generated and sent.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#253447',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, resend it!'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(url, { _token: "{{ csrf_token() }}" }, function (res) {
                if (res.status === 'error') { showToast(res.message, 'danger'); return; }
                showToast(res.message, 'success');
                $('#invitationTable').DataTable().ajax.reload();
            }).fail(() => showToast('Something went wrong', 'danger'));
        });
    });

    // ── Delete ─────────────────────────────────
    $(document).on('click', '.deleteBtn', function () {
        let url = "{{ route('invite.destroyinvite', ':id') }}".replace(':id', $(this).data('id'));
        Swal.fire({
            title: 'Delete this invitation?',
            html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>This invite will be permanently removed.',
            width: '380px', showCancelButton: true,
            confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel', customClass: { popup: 'swal-theme' }, reverseButtons: true,
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url, type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success(res) {
                    if (res.status === 'error') { showToast(res.message, 'danger'); return; }
                    showToast(res.message, 'success');
                    setTimeout(() => location.reload(), 1200);
                },
                error() { showToast('Delete failed', 'danger'); }
            });
        });
    });

});

// ── Disable submit button on send ──────────
document.getElementById('inviteForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('inviteSubmitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Sending...';
    }
});
</script>
@endpush

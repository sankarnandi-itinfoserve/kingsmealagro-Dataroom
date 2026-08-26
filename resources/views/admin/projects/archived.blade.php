@extends('admin.layouts.app')

@section('title', 'Project Archive')
@section('page_title', 'Project Archive')

@section('content')


{{-- ── Info banner ──────────────────────────────────────────────────────────── --}}
<div class="prj-archive-banner">
    <i class="fa-solid fa-circle-info"></i>
    <span>Closed projects are <strong>read-only</strong>. Files remain accessible to permitted users within their retention period. Admins can restore a project to active at any time.</span>
</div>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="prj-form-card">
    <div class="table-responsive">
        <table class="table fb-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Archived On</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-dark" style="font-size:13.5px;">{{ $project->name }}</span>
                        </div>
                        @if ($project->creator)
                            <div class="text-muted" style="font-size:11.5px;">
                                by {{ trim($project->creator->fname . ' ' . $project->creator->lname) }}
                            </div>
                        @endif
                    </td>
                    <td style="font-size:13px;">
                        {{ optional($project->deleted_at)->format('M d, Y') }}
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <form action="{{ route('projects.restoreDeleted', $project->id) }}" method="POST"
                                  class="d-inline" data-restore-name="{{ $project->name }}">
                                @csrf
                                <button type="button" class="prj-restore-btn prj-restore-deleted-btn" title="Restore">
                                    <i class="fa-solid fa-trash-arrow-up me-1"></i> Restore
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-box-archive fa-2x mb-2 d-block opacity-25"></i>
                        No archived projects.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($projects->hasPages())
        <div class="d-flex justify-content-end px-3 py-2">
            {{ $projects->links() }}
        </div>
    @endif
</div>

@endsection

@push('addOnCss')
<style>
/* reuse from create/edit */
.prj-create-header {
    display: flex; align-items: center; gap: 16px;
    background: #fff; border-radius: 14px; padding: 18px 22px;
    margin-bottom: 16px; box-shadow: 0 2px 12px rgba(37,52,71,.07);
}
.prj-create-header-icon {
    width: 44px; height: 44px; border-radius: 10px;
    color: #fff; display: flex; align-items: center;
    justify-content: center; font-size: 18px; flex-shrink: 0;
}
.prj-create-header-body { flex: 1; min-width: 0; }
.prj-create-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 3px; }
.prj-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #94a3b8; }
.prj-breadcrumb a { color: #64748b; text-decoration: none; }
.prj-breadcrumb a:hover { color: #253447; }
.prj-breadcrumb i { font-size: 9px; }
.prj-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 8px; border: 1px solid #e2e8f0;
    background: #f8fafc; color: #475569; font-size: 13px; font-weight: 500;
    text-decoration: none; transition: background .13s, border-color .13s, color .13s; flex-shrink: 0;
}
.prj-back-btn:hover { background: #253447; border-color: #253447; color: #fff; }

/* archive-specific */
.prj-archive-banner {
    display: flex; align-items: flex-start; gap: 10px;
    background: rgba(37,52,71,.06); border: 1px solid rgba(37,52,71,.15);
    border-radius: 10px; padding: 12px 16px;
    font-size: 13px; color: #253447; margin-bottom: 16px;
}
.prj-archive-banner i { margin-top: 2px; flex-shrink: 0; }


.prj-form-card {
    background: #fff; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(37,52,71,.06);
    border: 1px solid #f1f5f9; overflow: hidden;
}

.fb-table thead th {
    font-size: 11.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .4px; color: #64748b;
    background: #f8fafc; padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
}
.fb-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f8fafc; }
.fb-table tbody tr:last-child td { border-bottom: none; }
.fb-table tbody tr:hover td { background: #f8fafc; }

.fb-row-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 7px;
    border: 1px solid #e2e8f0; background: #f8fafc;
    color: #64748b; font-size: 12px; text-decoration: none;
    transition: background .12s, color .12s, border-color .12s;
}
.fb-row-btn:hover { background: #253447; border-color: #253447; color: #fff; }

.prj-restore-btn {
    display: inline-flex; align-items: center;
    padding: 6px 14px; border-radius: 8px;
    border: 1.5px solid #16a34a; background: #f0fdf4;
    color: #16a34a; font-size: 12.5px; font-weight: 600;
    cursor: pointer; transition: background .13s, color .13s;
}
.prj-restore-btn:hover { background: #16a34a; color: #fff; }

.prj-ret-badge {
    display: inline-flex; align-items: center;
    padding: 3px 9px; border-radius: 99px;
    font-size: 11.5px; font-weight: 600;
}
.prj-ret-ok      { background: #f0fdf4; color: #16a34a; }
.prj-ret-soon    { background: #fef9c3; color: #b45309; }
.prj-ret-expired { background: #fee2e2; color: #dc2626; }

.prj-restore-deleted-btn {
    display: inline-flex; align-items: center;
    padding: 6px 14px; border-radius: 8px;
    border: 1.5px solid #dc2626; background: #fef2f2;
    color: #dc2626; font-size: 12.5px; font-weight: 600;
    cursor: pointer; transition: background .13s, color .13s;
}
.prj-restore-deleted-btn:hover { background: #dc2626; color: #fff; }
</style>
@endpush

@push('script')
<script>
$(function () {
    $(document).on('click', '.prj-restore-btn', function () {
        var form = $(this).closest('form');
        var name = form.data('restore-name');
        Swal.fire({
            title: 'Restore Project?',
            html: '<div class="swal-theme-icon" style="background:rgba(37,52,71,.08);color:#253447;"><i class="fa-solid fa-rotate-left"></i></div>"' + name + '" will be moved back to Active.',
            width: '380px',
            showCancelButton: true,
            confirmButtonColor: '#253447',
            confirmButtonText: 'Yes, restore',
            cancelButtonText: 'Cancel',
            customClass: { popup: 'swal-theme' },
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });

    $(document).on('click', '.prj-restore-deleted-btn', function () {
        var form = $(this).closest('form');
        var name = form.data('restore-name');
        Swal.fire({
            title: 'Restore Deleted Project?',
            html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash-arrow-up"></i></div>"' + name + '" will be recovered and moved back to Active.',
            width: '380px',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, restore',
            cancelButtonText: 'Cancel',
            customClass: { popup: 'swal-theme' },
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush

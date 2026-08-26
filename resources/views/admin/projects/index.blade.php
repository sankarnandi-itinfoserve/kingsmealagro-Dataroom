@extends('admin.layouts.app')

@section('title', 'Projects Management')
@section('page_title', 'Projects Management')

@section('content')

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card">

            {{-- Header row --}}
            <div class="fb-header-row">
                <div>
                    <div class="fb-nav-line">
                        <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                            <span class="fb-crumb-current">Projects Management</span>
                        </nav>
                    </div>
                </div>

                <div class="fb-header-actions">
                    <a href="{{ route('projects.create') }}" class="fb-tool-btn fb-tool-btn-primary">
                        <i class="fa-solid fa-folder-plus"></i> Create New Project
                    </a>

                    <div class="fb-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="projectSearchInput" placeholder="Search projects…">
                    </div>

                    {{-- <div class="fb-view-toggle" role="group" aria-label="View mode">
                        <button type="button" class="fb-view-btn active" data-view="list">
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <button type="button" class="fb-view-btn" data-view="grid">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                    </div> --}}
                </div>
            </div>

            <div class="fb-layout">
                <section class="fb-main">

                    {{-- List view --}}
                    <div id="projectListView" class="fb-view-panel">
                        <div class="table-responsive">
                            <table class="table fb-table align-middle" id="projectTable">
                                <thead>
                                    <tr>
                                        <th style="width:1%; white-space:nowrap;">#</th>
                                        <th class="sortable col-has-filter" data-sort="name">
                                            <div class="col-th-inner">
                                                <span>Name <i class="fa-solid fa-sort sort-icon"></i></span>
                                                <button type="button" class="col-filter-btn" data-col="name"
                                                    title="Filter"><i class="fa-solid fa-filter"></i></button>
                                            </div>
                                        </th>
                                        <th style="white-space:nowrap;">File Room</th>
                                        <th style="white-space:nowrap;">Analytics</th>
                                        <th class="fb-col-actions" style="white-space:nowrap;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="projectListBody">
                                    @forelse ($projects as $project)
                                        <tr class="project-row" data-name="{{ strtolower($project->name) }}">
                                            <td class="text-muted" style="font-size:13px;">{{ $projects->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="fb-name-cell">
                                                    <a href="{{ route('projects.edit', $project) }}"
                                                        class="prj-name-edit-link"
                                                        title="Edit Project">{{ $project->name }}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('shared.folders') }}#path={{ $project->id }}"
                                                    class="fb-fileroom-btn" title="Open File Room">
                                                    <i class="fa-solid fa-folder-open"></i> File Room
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('analytics.project', $project) }}"
                                                    class="fb-analytics-btn" title="View Analytics">
                                                    <i class="fa-solid fa-chart-line"></i> Analytics
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fb-row-actions">
                                                    <a href="{{ route('projects.edit', $project) }}" class="fb-row-btn"
                                                        title="Edit">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                                        class="d-inline" data-delete-name="{{ $project->name }}">
                                                        @csrf @method('DELETE')
                                                        <button type="button" class="fb-row-btn fb-delete-btn"
                                                            title="Delete">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="fa-solid fa-diagram-project fa-2x mb-2 d-block opacity-25"></i>
                                                No projects found.
                                                <a href="{{ route('projects.create') }}">Create one now.</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($projects->hasPages())
                            <div class="d-flex justify-content-end px-2 pb-2">
                                {{ $projects->links() }}
                            </div>
                        @endif
                    </div>

                    {{-- Grid view --}}
                    <div id="projectGridView" class="fb-view-panel d-none">
                        <div class="row g-3" id="projectGridBody">
                            @forelse ($projects as $project)
                                <div class="col-xl-3 col-lg-4 col-md-6 project-grid-item"
                                    data-name="{{ strtolower($project->name) }}">
                                    <div class="prj-grid-card">
                                        <div class="prj-grid-card-body">
                                            <p class="prj-grid-card-name" title="{{ $project->name }}">
                                                {{ $project->name }}</p>
                                        </div>
                                        <div class="prj-grid-card-footer">
                                            <a href="{{ route('projects.show', $project) }}" class="prj-grid-btn"
                                                title="View">
                                                <i class="fa-solid fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('projects.edit', $project) }}"
                                                class="prj-grid-btn prj-grid-btn-ghost" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                                class="d-inline" data-delete-name="{{ $project->name }}">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    class="prj-grid-btn prj-grid-btn-danger fb-delete-btn" title="Delete">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="fa-solid fa-diagram-project fa-2x mb-2 d-block opacity-25"></i>
                                    No projects found.
                                    <a href="{{ route('projects.create') }}">Create one now.</a>
                                </div>
                            @endforelse
                        </div>

                        @if ($projects->hasPages())
                            <div class="d-flex justify-content-end px-2 pb-2 mt-3">
                                {{ $projects->links() }}
                            </div>
                        @endif
                    </div>

                </section>
            </div>
        </div>
    </div>

    {{-- ── Column filter panels ── --}}
    <div class="col-filter-panel" data-col-panel="name">
        <div class="cfp-header">Filter by Name</div>
        <div class="cfp-body">
            <input type="text" class="cfp-text-input" data-col-input="name" placeholder="Type to filter…"
                autocomplete="off">
        </div>
        <div class="cfp-footer">
            <button type="button" class="cfp-reset" data-col="name">Reset</button>
            <button type="button" class="cfp-apply" data-col="name"><i
                    class="fa-solid fa-check me-1"></i>Filter</button>
        </div>
    </div>


@endsection

@push('addOnCss')
    <style>
        .fb-fileroom-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 7px;
            background: #0d9488;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: all .15s;
        }

        .fb-fileroom-btn i {
            color: #f59e0b;
            font-size: 12px;
        }

        .fb-analytics-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 7px;
            background: #0d65a8;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: all .15s;
        }

        .fb-analytics-btn:hover {
            background: #0a5288;
            color: #fff;
        }

        .fb-analytics-btn i {
            color: #fff;
            font-size: 12px;
        }


        .prj-name-edit-link,
        .prj-template-edit-link {
            color: #253447;
            font-size: 13px;
            font-weight: 600;
            text-decoration: underline;
        }

        .prj-name-edit-link:hover,
        .prj-template-edit-link:hover {
            text-decoration-color: currentColor;
        }

        /* ── Toolbar row ─────────────────────────────────────────────────────── */
        .fb-bulk-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        /* ── Status filter tabs ───────────────────────────────────────────────── */
        .fb-status-tab {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: background .12s, color .12s, border-color .12s;
        }

        .fb-status-tab:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .fb-status-tab.active {
            background: #253447;
            color: #fff;
            border-color: #253447;
        }

        .fb-status-tab.fb-status-active.active {
            background: #16a34a;
            border-color: #16a34a;
        }

        .fb-status-tab.fb-status-closed.active {
            background: #dc2626;
            border-color: #dc2626;
        }

        .fb-status-tab.fb-status-archived.active {
            background: #6b7280;
            border-color: #6b7280;
        }

        .fb-status-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 50px;
            background: rgba(255, 255, 255, .25);
            font-size: 11px;
            font-weight: 700;
        }

        .fb-status-tab:not(.active) .fb-status-count {
            background: #e2e8f0;
            color: #64748b;
        }

        /* ── Project grid cards ───────────────────────────────────────────────── */
        .prj-grid-card {
            background: #fff;
            border: 1px solid #e9eef6;
            border-radius: 14px;
            padding: 18px 16px 14px;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: box-shadow .18s, border-color .18s, transform .18s;
        }

        .prj-grid-card:hover {
            box-shadow: 0 8px 28px rgba(37, 52, 71, .11);
            border-color: #c7d2e0;
            transform: translateY(-2px);
        }

        .prj-grid-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .prj-grid-folder-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(37, 52, 71, .07);
            color: #253447;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .prj-grid-card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .prj-grid-card-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .prj-grid-card-meta {
            font-size: 12px;
            color: #64748b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .prj-grid-card-meta i {
            font-size: 10px;
            color: #94a3b8;
        }

        .prj-grid-card-footer {
            display: flex;
            align-items: center;
            gap: 6px;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
        }

        .prj-grid-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: #253447;
            color: #fff;
            transition: opacity .14s;
        }

        .prj-grid-btn:hover {
            opacity: .85;
            color: #fff;
        }

        .prj-grid-btn-ghost {
            background: #f1f5f9;
            color: #475569;
        }

        .prj-grid-btn-ghost:hover {
            background: #e2e8f0;
            color: #253447;
            opacity: 1;
        }

        .prj-grid-btn-danger {
            background: #fee2e2;
            color: #dc2626;
            margin-left: auto;
        }

        .prj-grid-btn-danger:hover {
            background: #fecaca;
            opacity: 1;
        }

        /* ── Status badge in rows ──────────────────────────────────────────────── */
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
            background: #16a34a;
            color: #fff;
        }

        .prj-status-closed {
            background: #dc2626;
            color: #fff;
        }

        .prj-status-archived {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* ── Delete button hover ─────────────────────────────────────────────── */
        .fb-delete-btn:hover {
            border-color: #fca5a5 !important;
            background: #fef2f2 !important;
            color: #dc2626 !important;
        }

        /* ── Close (archive) button hover ───────────────────────────────────── */
        .fb-close-btn:hover {
            border-color: #6b7280 !important;
            background: #f3f4f6 !important;
            color: #374151 !important;
        }

        /* ── Nav line / breadcrumb (reuse drive styles) ──────────────────────── */
        .fb-nav-line {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ── Column filter ───────────────────────────────────────────────────── */
        .col-has-filter {
            position: relative;
        }

        .col-th-inner {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
            white-space: nowrap;
        }

        .col-filter-btn {
            background: none;
            border: none;
            padding: 3px 5px;
            cursor: pointer;
            color: #b0bec5;
            border-radius: 4px;
            font-size: 10px;
            line-height: 1;
            flex-shrink: 0;
            transition: all .15s;
        }

        .col-filter-btn:hover {
            color: #253447;
            background: rgba(37, 52, 71, .1);
        }

        .col-filter-btn.active {
            color: #c0272d;
            background: #fef2f2;
        }

        .col-filter-panel {
            display: none;
            position: fixed;
            width: 220px;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            box-shadow: 0 8px 28px rgba(37, 52, 71, .15);
            z-index: 9999;
            overflow: hidden;
        }

        .col-filter-panel.open {
            display: block;
        }

        .cfp-text-input {
            width: 100%;
            height: 34px;
            padding: 0 10px;
            border: 1.5px solid #e2e8f0;
            border-radius: 7px;
            font-size: 12.5px;
            color: #1e293b;
            outline: none;
            box-sizing: border-box;
        }

        .cfp-text-input:focus {
            border-color: #253447;
            box-shadow: 0 0 0 2px rgba(37, 52, 71, .1);
        }

        .cfp-header {
            padding: 9px 13px 7px;
            font-size: 11px;
            font-weight: 700;
            color: #253447;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .cfp-body {
            padding: 10px 13px;
            max-height: 190px;
            overflow-y: auto;
        }

        .cfp-checkboxes {
            padding: 8px 13px;
        }

        .cfp-check-label {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 6px 4px;
            cursor: pointer;
            font-size: 12px;
            color: #334155;
            border-radius: 5px;
            transition: background .1s;
        }

        .cfp-check-label:hover {
            background: #f1f5f9;
        }

        .cfp-check-label input[type="checkbox"] {
            accent-color: #253447;
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .cfp-footer {
            display: flex;
            gap: 6px;
            padding: 8px 12px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .cfp-reset {
            flex: 1;
            padding: 6px 0;
            border: 1px solid #dbe4f0;
            border-radius: 6px;
            background: #fff;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }

        .cfp-reset:hover {
            border-color: #94a3b8;
            color: #334155;
        }

        .cfp-apply {
            flex: 1;
            padding: 6px 0;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #253447, #1a2737);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s;
        }

        .cfp-apply:hover {
            opacity: .85;
        }

        /* ── Sortable columns ────────────────────────────────────────────────── */
        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable i {
            margin-left: 6px;
            color: #94a3b8;
        }

        .fb-col-actions {
            width: 130px;
        }

        /* ── Toolbar separator ───────────────────────────────────────────────── */
        .fb-tool-btn-primary {
            background: #253447 !important;
            color: #fff !important;
            border-color: #253447 !important;
            text-decoration: none !important;
        }

        .fb-tool-btn-primary:hover {
            background: #1a2737 !important;
            border-color: #1a2737 !important;
            color: #fff !important;
        }

    </style>
@endpush

@push('script')
    <script>
        $(function() {

            /* ── View toggle ─────────────────────────────────────────────────────── */
            $(document).on('click', '.fb-view-btn', function() {
                $('.fb-view-btn').removeClass('active');
                $(this).addClass('active');
                if ($(this).data('view') === 'list') {
                    $('#projectListView').removeClass('d-none');
                    $('#projectGridView').addClass('d-none');
                } else {
                    $('#projectGridView').removeClass('d-none');
                    $('#projectListView').addClass('d-none');
                }
            });

            /* ── Live search ─────────────────────────────────────────────────────── */
            $('#projectSearchInput').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                $('.project-row, .project-grid-item').each(function() {
                    $(this).toggle(($(this).data('name') || '').toString().includes(q));
                });
            });

            /* ── Column filters ─────────────────────────────────────────────────── */
            let colFilters = {
                name: ''
            };

            function applyColFilters() {
                const q = $('#projectSearchInput').val().toLowerCase().trim();
                $('.project-row, .project-grid-item').each(function() {
                    const name = ($(this).data('name') || '').toString();
                    const matchSearch = !q || name.includes(q);
                    const matchName = !colFilters.name || name.includes(colFilters.name);
                    $(this).toggle(matchSearch && matchName);
                });
            }

            // Override live search to respect col filters
            $('#projectSearchInput').off('input').on('input', applyColFilters);

            // Open panel
            $(document).on('click', '.col-filter-btn', function(e) {
                e.stopPropagation();
                const col = $(this).data('col');
                const panel = $('[data-col-panel="' + col + '"]');
                const isOpen = panel.hasClass('open');
                $('.col-filter-panel').removeClass('open');
                if (!isOpen) {
                    const rect = this.getBoundingClientRect();
                    const thRect = $(this).closest('th')[0].getBoundingClientRect();
                    const pw = 220;
                    let left = panel.hasClass('cfp-right') ? rect.right - pw : thRect.left;
                    if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
                    if (left < 8) left = 8;
                    panel.css({
                        top: rect.bottom + 4,
                        left
                    });
                    panel.addClass('open');
                    panel.find('.cfp-text-input').focus();
                }
            });

            $(document).on('click', '.col-filter-panel', e => e.stopPropagation());
            $(document).on('click', () => $('.col-filter-panel').removeClass('open'));
            $(window).on('scroll resize', () => $('.col-filter-panel').removeClass('open'));
            $(document).on('keydown', '.cfp-text-input', function(e) {
                if (e.key === 'Enter') $(this).closest('.col-filter-panel').find('.cfp-apply').trigger(
                    'click');
            });

            // Apply
            $(document).on('click', '.cfp-apply', function() {
                const col = $(this).data('col');
                const panel = $('[data-col-panel="' + col + '"]');
                const textInput = panel.find('.cfp-text-input');
                if (textInput.length) {
                    colFilters[col] = textInput.val().toLowerCase().trim();
                    $('[data-col="' + col + '"].col-filter-btn').toggleClass('active', colFilters[col] !==
                        '');
                } else {
                    colFilters[col] = panel.find('input[type="checkbox"]:checked').map(function() {
                        return $(this).val();
                    }).get();
                    $('[data-col="' + col + '"].col-filter-btn').toggleClass('active', colFilters[col]
                        .length > 0);
                }
                panel.removeClass('open');
                applyColFilters();
            });

            // Reset
            $(document).on('click', '.cfp-reset', function() {
                const col = $(this).data('col');
                const panel = $('[data-col-panel="' + col + '"]');
                const textInput = panel.find('.cfp-text-input');
                if (textInput.length) {
                    textInput.val('');
                    colFilters[col] = '';
                } else {
                    panel.find('input[type="checkbox"]').prop('checked', false);
                    colFilters[col] = [];
                }
                panel.removeClass('open');
                $('[data-col="' + col + '"].col-filter-btn').removeClass('active');
                applyColFilters();
            });

            /* ── Column sort (list view) ──
               3-click cycle per column: ascending → descending → default
               (original, server-provided order) → ascending → ... ─────────── */
            let sortKey = '',
                sortDir = null;
            const projectListTbody = $('#projectListBody');
            const originalProjectRowOrder = projectListTbody.find('tr.project-row').get();

            $(document).on('click', '.sortable', function() {
                const key = $(this).data('sort');

                if (sortKey !== key) {
                    sortDir = 'asc';
                } else if (sortDir === 'asc') {
                    sortDir = 'desc';
                } else if (sortDir === 'desc') {
                    sortDir = null;
                } else {
                    sortDir = 'asc';
                }
                sortKey = sortDir ? key : '';

                $('.sortable .sort-icon').attr('class', 'fa-solid fa-sort sort-icon');

                if (!sortDir) {
                    $.each(originalProjectRowOrder, function(_, row) {
                        projectListTbody.append(row);
                    });
                    return;
                }

                const rows = projectListTbody.find('tr.project-row').get();

                rows.sort(function(a, b) {
                    let av = $(a).find('td').eq(colIndex(key)).text().trim().toLowerCase();
                    let bv = $(b).find('td').eq(colIndex(key)).text().trim().toLowerCase();
                    return sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
                });

                $.each(rows, function(_, row) {
                    projectListTbody.append(row);
                });

                $(this).find('.sort-icon').attr('class', 'fa-solid fa-sort-' + (sortDir === 'asc' ? 'up' : 'down') + ' sort-icon');
            });

            function colIndex(key) {
                const map = {
                    name: 1
                };
                return map[key] ?? 1;
            }

            /* ── Close (archive) with SweetAlert ────────────────────────────────── */
            $(document).on('click', '.fb-close-btn', function() {
                var form = $(this).closest('form');
                var name = form.data('close-name');
                Swal.fire({
                    title: 'Close Project?',
                    html: '<div class="swal-theme-icon" style="background:rgba(37,52,71,.08);color:#253447;"><i class="fa-solid fa-box-archive"></i></div>"<strong>' +
                        name + '</strong>" will be moved to the archive and become read-only.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#253447',
                    confirmButtonText: 'Yes, close it',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            });

            /* ── Delete with SweetAlert ──────────────────────────────────────────── */
            $(document).on('click', '.fb-delete-btn', function() {
                const form = $(this).closest('form');
                const name = form.data('delete-name');
                Swal.fire({
                    title: 'Delete Project?',
                    html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>"<strong>' +
                        name + '</strong>" will be permanently removed.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            });

        });
    </script>
@endpush

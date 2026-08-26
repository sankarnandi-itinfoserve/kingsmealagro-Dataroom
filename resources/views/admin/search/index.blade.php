@extends('admin.layouts.app')
@section('title', 'Search')
@section('page_title', 'Search')

@section('content')

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card">

            {{-- ── Search header ── --}}
            <div class="srch-page-head">
                <div class="srch-page-title">
                    <i class="fa-solid fa-magnifying-glass srch-title-icon"></i>
                    <span>Search</span>
                </div>
            </div>

            {{-- ── Search form ── --}}
            <form method="GET" action="{{ route('search.index') }}" id="searchForm">

                {{-- Primary search bar --}}
                <div class="srch-bar-wrap">
                    <i class="fa-solid fa-magnifying-glass srch-bar-icon"></i>
                    <input
                        type="text"
                        name="query"
                        id="searchQuery"
                        class="srch-bar-input"
                        placeholder="Search files and folders…"
                        value="{{ $query ?? '' }}"
                        autocomplete="off"
                        autofocus
                    >
                    @if (!empty($query))
                        <a href="{{ route('search.index') }}" class="srch-bar-clear" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>

                {{-- Filter row --}}
                <div class="srch-filter-row">
                    <div class="srch-filter-group">
                        <label class="srch-filter-label" for="filterType">File Type</label>
                        <select name="type" id="filterType" class="srch-filter-select">
                            <option value="all"    {{ ($type ?? 'all') === 'all'    ? 'selected' : '' }}>All Types</option>
                            <option value="folder" {{ ($type ?? '') === 'folder'   ? 'selected' : '' }}>Folders Only</option>
                            <option value="file"   {{ ($type ?? '') === 'file'     ? 'selected' : '' }}>Files Only</option>
                        </select>
                    </div>

                    <div class="srch-filter-group">
                        <label class="srch-filter-label" for="filterDateFrom">Date From</label>
                        <input
                            type="date"
                            name="date_from"
                            id="filterDateFrom"
                            class="srch-filter-input"
                            value="{{ $dateFrom ?? '' }}"
                        >
                    </div>

                    <div class="srch-filter-group">
                        <label class="srch-filter-label" for="filterDateTo">Date To</label>
                        <input
                            type="date"
                            name="date_to"
                            id="filterDateTo"
                            class="srch-filter-input"
                            value="{{ $dateTo ?? '' }}"
                        >
                    </div>

                    <div class="srch-filter-actions">
                        <button type="submit" class="srch-btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Search
                        </button>
                        @if (!empty($query) || !empty($dateFrom) || !empty($dateTo) || (!empty($type) && $type !== 'all'))
                            <a href="{{ route('search.index') }}" class="srch-btn-reset">
                                <i class="fa-solid fa-rotate-left"></i>
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

            </form>

            {{-- ── Results section ── --}}
            <div class="srch-results-section">

                @if (empty($query))

                    {{-- No query state --}}
                    <div class="srch-empty-state">
                        <div class="srch-empty-icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <p class="srch-empty-title">Enter a search term above to find files and folders.</p>
                        <p class="srch-empty-sub">You can filter by file type and date range to narrow your results.</p>
                    </div>

                @elseif ($results->isEmpty())

                    {{-- Empty results --}}
                    <div class="srch-empty-state">
                        <div class="srch-empty-icon srch-empty-icon--noresult">
                            <i class="fa-solid fa-file-circle-xmark"></i>
                        </div>
                        <p class="srch-empty-title">No results found for &ldquo;{{ $query }}&rdquo;.</p>
                        <p class="srch-empty-sub">Try a different term or adjust the filters above.</p>
                    </div>

                @else

                    {{-- Result count + summary --}}
                    <div class="srch-results-meta">
                        <span class="srch-results-count">
                            {{ $results->total() }} result{{ $results->total() !== 1 ? 's' : '' }} for
                            <strong>&ldquo;{{ $query }}&rdquo;</strong>
                        </span>
                        @if (!empty($type) && $type !== 'all')
                            <span class="srch-filter-chip">
                                <i class="fa-solid fa-filter"></i>
                                {{ ucfirst($type) }}s only
                            </span>
                        @endif
                        @if (!empty($dateFrom) || !empty($dateTo))
                            <span class="srch-filter-chip">
                                <i class="fa-regular fa-calendar"></i>
                                @if (!empty($dateFrom) && !empty($dateTo))
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}
                                @elseif (!empty($dateFrom))
                                    From {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }}
                                @else
                                    Until {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}
                                @endif
                            </span>
                        @endif
                    </div>

                    {{-- Results table --}}
                    <div class="table-responsive srch-table-wrap">
                        <table class="table fb-table srch-table align-middle">
                            <thead>
                                <tr>
                                    <th class="srch-col-name">Name</th>
                                    <th class="srch-col-type">Type</th>
                                    <th class="srch-col-size">Size</th>
                                    <th class="srch-col-modified">Last Modified</th>
                                    <th class="srch-col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $item)
                                    @php
                                        $isFolder = ($item->type ?? 'file') === 'folder';
                                        $ext      = !$isFolder ? strtolower(pathinfo($item->name ?? '', PATHINFO_EXTENSION)) : '';

                                        if ($isFolder) {
                                            $iconClass = 'fa-folder';
                                            $iconColor = 'fb-folder-icon';
                                        } elseif ($ext === 'pdf') {
                                            $iconClass = 'fa-file-pdf';
                                            $iconColor = 'fb-file-pdf-icon';
                                        } elseif (in_array($ext, ['doc', 'docx'])) {
                                            $iconClass = 'fa-file-word';
                                            $iconColor = 'fb-file-word-icon';
                                        } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                                            $iconClass = 'fa-file-excel';
                                            $iconColor = 'fb-file-excel-icon';
                                        } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                            $iconClass = 'fa-file-powerpoint';
                                            $iconColor = 'fb-file-ppt-icon';
                                        } elseif (in_array($ext, ['zip', 'rar', '7z'])) {
                                            $iconClass = 'fa-file-zipper';
                                            $iconColor = 'fb-file-zip-icon';
                                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                                            $iconClass = 'fa-file-image';
                                            $iconColor = 'fb-file-img-icon';
                                        } else {
                                            $iconClass = 'fa-file';
                                            $iconColor = 'fb-file-icon';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fb-name-cell">
                                                <i class="fa-solid {{ $iconClass }} {{ $iconColor }}"></i>
                                                <span class="srch-item-name">{{ $item->name ?? 'Untitled' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($isFolder)
                                                <span class="srch-type-badge srch-type-folder">
                                                    <i class="fa-solid fa-folder"></i> Folder
                                                </span>
                                            @else
                                                <span class="srch-type-badge srch-type-file">
                                                    <i class="fa-solid fa-file"></i> File
                                                </span>
                                            @endif
                                        </td>
                                        <td class="srch-size-cell">
                                            {{ $isFolder ? '—' : ($item->size ?? '—') }}
                                        </td>
                                        <td class="srch-date-cell">
                                            {{ isset($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('M j, Y  g:i A') : '—' }}
                                        </td>
                                        <td>
                                            <div class="fb-row-actions">
                                                @if (!$isFolder)
                                                    <a
                                                        href="{{ url('/files/' . base64_encode($item->id) . '/preview') }}"
                                                        class="fb-row-btn"
                                                        title="Preview file"
                                                    >
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($results->hasPages())
                        <div class="srch-pagination">
                            {{ $results->appends(request()->query())->links() }}
                        </div>
                    @endif

                @endif

            </div>
            {{-- /results --}}

        </div>
    </div>

@endsection

@push('addOnCss')
    <style>

        /* ── Page header ── */
        .srch-page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .srch-page-title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .srch-title-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a2737 0%, #253447 100%);
            color: #fff;
            border-radius: 9px;
            font-size: 15px;
            flex-shrink: 0;
        }

        /* ── Search bar ── */
        .srch-bar-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .srch-bar-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
        }

        .srch-bar-input {
            width: 100%;
            height: 50px;
            border: 1.5px solid #dbe4f0;
            border-radius: 12px;
            padding: 0 44px 0 46px;
            font-size: 15px;
            color: #1e293b;
            background: #f9fafb;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .srch-bar-input:focus {
            border-color: #253447;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 52, 71, .08);
        }

        .srch-bar-input::placeholder {
            color: #94a3b8;
        }

        .srch-bar-clear {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            text-decoration: none;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background .12s, color .12s;
        }

        .srch-bar-clear:hover {
            background: #f1f5f9;
            color: #475569;
        }

        /* ── Filter row ── */
        .srch-filter-row {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e9eef6;
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .srch-filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .srch-filter-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .srch-filter-select,
        .srch-filter-input {
            height: 36px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 0 10px;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            outline: none;
            min-width: 140px;
            transition: border-color .14s, box-shadow .14s;
        }

        .srch-filter-select:focus,
        .srch-filter-input:focus {
            border-color: #253447;
            box-shadow: 0 0 0 3px rgba(37, 52, 71, .07);
        }

        .srch-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            align-self: flex-end;
        }

        .srch-btn-primary {
            height: 36px;
            padding: 0 18px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #253447 0%, #1a2737 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 2px 8px rgba(37, 52, 71, .22);
            transition: opacity .14s, transform .1s, box-shadow .14s;
        }

        .srch-btn-primary:hover {
            opacity: .9;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(37, 52, 71, .30);
        }

        .srch-btn-reset {
            height: 36px;
            padding: 0 14px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            background: #fff;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: border-color .14s, background .14s, color .14s;
        }

        .srch-btn-reset:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
            color: #334155;
        }

        /* ── Results section ── */
        .srch-results-section {
            min-height: 220px;
        }

        /* ── Empty / prompt states ── */
        .srch-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
            text-align: center;
        }

        .srch-empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #94a3b8;
            margin-bottom: 18px;
        }

        .srch-empty-icon--noresult {
            color: #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
        }

        .srch-empty-title {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 6px;
        }

        .srch-empty-sub {
            font-size: 13px;
            color: #94a3b8;
            margin: 0;
        }

        /* ── Results meta bar ── */
        .srch-results-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #edf2f7;
        }

        .srch-results-count {
            font-size: 13px;
            color: #475569;
        }

        .srch-results-count strong {
            color: #1e293b;
        }

        .srch-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: #2563eb;
        }

        /* ── Table overrides ── */
        .srch-table-wrap {
            border: 1px solid #e9eef6;
            border-radius: 10px;
            overflow: hidden;
        }

        .srch-table {
            margin-bottom: 0 !important;
        }

        .srch-col-name     { min-width: 200px; }
        .srch-col-type     { width: 110px;  white-space: nowrap; }
        .srch-col-size     { width: 90px;   white-space: nowrap; }
        .srch-col-modified { width: 170px;  white-space: nowrap; }
        .srch-col-actions  { width: 80px;   white-space: nowrap; }

        .srch-item-name {
            font-weight: 500;
            color: #1e293b;
        }

        .srch-size-cell,
        .srch-date-cell {
            font-variant-numeric: tabular-nums;
            color: #64748b;
            font-size: 12px;
        }

        /* ── Type badges ── */
        .srch-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .srch-type-folder {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .srch-type-file {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        /* ── Pagination ── */
        .srch-pagination {
            display: flex;
            justify-content: flex-end;
            padding-top: 16px;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .srch-filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .srch-filter-actions {
                margin-left: 0;
                justify-content: flex-start;
            }

            .srch-filter-select,
            .srch-filter-input {
                min-width: unset;
                width: 100%;
            }
        }

    </style>
@endpush

@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

    @php
        $firstName = $authUser->fname ?? 'Admin';
        $fullName = trim(($authUser->fname ?? '') . ' ' . ($authUser->lname ?? ''));
        $initials = strtoupper(substr($firstName, 0, 1));
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $avatarPalette = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];
    @endphp

    {{-- ══════════════════════════════════════════════════════════
     HERO BANNER
══════════════════════════════════════════════════════════ --}}
    <div class="db-hero mb-4">
        <div class="db-hero-bg"></div>
        <div class="db-hero-inner">

            {{-- Greeting --}}
            <div class="db-hero-greet">
                @if ($authUser->avatar)
                    <div class="db-hero-avatar" style="padding:0; overflow:hidden;">
                        <img src="{{ asset('storage/' . $authUser->avatar) }}" alt="{{ $initials }}"
                            style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                    </div>
                @else
                    <div class="db-hero-avatar">
                        <i class="fa-solid fa-circle-user" style="font-size:34px;line-height:1;"></i>
                    </div>
                @endif
                <div>
                    <div class="db-hero-title">{{ $greeting }}, <span>{{ $firstName }}</span></div>
                    <div class="db-hero-sub">Welcome to NLG-VDR</div>
                </div>
            </div>

            {{-- Stat chips inside hero --}}
            <div class="db-hero-stats">
                @foreach ([['val' => $activeProjects, 'lbl' => 'Active Projects', 'icon' => 'fa-diagram-project', 'color' => '#38bdf8', 'bg' => 'rgba(56,189,248,.18)'], ['val' => $totalUsers, 'lbl' => 'Users', 'icon' => 'fa-users', 'color' => '#a78bfa', 'bg' => 'rgba(167,139,250,.18)'], ['val' => $totalFiles, 'lbl' => 'Total Files', 'icon' => 'fa-file-lines', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,.18)'], ['val' => $loginsToday, 'lbl' => 'Logins Today', 'icon' => 'fa-arrow-right-to-bracket', 'color' => '#34d399', 'bg' => 'rgba(52,211,153,.18)']] as $i => $stat)
                    @if ($i > 0)
                        <div class="db-hstat-sep"></div>
                    @endif
                    <div class="db-hstat">
                        <div class="db-hstat-icon" style="background:{{ $stat['bg'] }};color:{{ $stat['color'] }};">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="db-hstat-val">{{ number_format($stat['val']) }}</div>
                            <div class="db-hstat-lbl">{{ $stat['lbl'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     RECENT PROJECTS  +  ARCHIVE BOX
══════════════════════════════════════════════════════════ --}}
    @php
        // Archived project count is computed in DashboardController (a project
        // is a root folder under the Documents drive; archived == soft-deleted).
        $archivedCount = $archivedProjects;
        $top2Projects = $recentProjects->take(2);
    @endphp
    <div class="mb-4">
        <div class="row g-3">
            {{-- Project card 1 & 2 --}}
            @forelse ($top2Projects as $proj)
                @php
                    $ago = $proj->updated_at?->diffForHumans() ?? '—';
                    $cnt = $fileCounts[$proj->id] ?? 0;
                @endphp
                <div class="col-12 col-sm-6 col-xl-3">
                    <a href="{{ route('projects.edit', $proj) }}" class="db-archive-card">
                        <div class="db-pcard-row">
                            <div class="db-pcard-body">
                                <div class="db-pcard-name" title="{{ $proj->name }}">{{ $proj->name }}</div>
                                <div class="db-pcard-meta">{{ $cnt }}
                                    {{ Str::plural('file', $cnt) }}</div>
                            </div>
                        </div>
                        <div class="db-pcard-foot">
                            <span>{{ $ago }}</span>
                            <span class="db-pcard-badge">Active</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="db-pcard db-pcard-empty">
                        <i class="fa-solid fa-folder-open"></i>
                        <span>No active projects yet.</span>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="db-pcard db-pcard-empty"></div>
                </div>
            @endforelse

            {{-- Archive box --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('projects.archived') }}" class="db-archive-card db-archive-card-closed">
                    <div class="db-archive-card-row">
                        <div>
                            <div class="db-archive-card-count">{{ $archivedCount }}</div>
                            <div class="db-pcard-name">Closed {{ Str::plural('project', $archivedCount) }}</div>
                        </div>
                    </div>
                    <div class="db-archive-card-link">
                        Browse archive <i class="fa-solid fa-arrow-right ms-1" style="font-size:10px;"></i>
                    </div>
                </a>
            </div>

            {{-- New Project CTA --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('projects.create') }}" class="db-newproj-card">
                    <div class="db-newproj-row">
                        <div class="db-newproj-icon"><i class="fa-solid fa-plus"></i></div>
                        <div>
                            <div class="db-newproj-title">Ready for a New Project?</div>
                            <div class="db-newproj-sub">Start a new data room</div>
                        </div>
                    </div>
                    <div class="db-newproj-link">Create now <i class="fa-solid fa-arrow-right ms-1"
                            style="font-size:10px;"></i></div>
                </a>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     QUICK ACTIONS  +  RECENT USERS
══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Quick Actions --}}
        <div class="col-12 col-xl-7">
            <div class="db-card h-100">
                <div class="db-card-head border-0 pb-0">
                    <div class="db-card-title">Quick Access</div>
                </div>
                <div class="db-actions-grid">
                    <a href="{{ route('shared.folders') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-folder-tree"></i></div>
                        <div class="db-at-lbl">File Room</div>
                    </a>
                    <a href="{{ route('users.index') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="db-at-lbl">Users</div>
                    </a>
                    <a href="{{ route('analytics.index') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="db-at-lbl">Analytics</div>
                    </a>
                    <a href="{{ route('projects.archived') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-box-archive"></i></div>
                        <div class="db-at-lbl">Archive</div>
                    </a>
                    <a href="{{ route('favorite.folders') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-regular fa-star"></i></div>
                        <div class="db-at-lbl">Favourites</div>
                    </a>
                    <a href="{{ route('activity-logs.index') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div class="db-at-lbl">Activity Logs</div>
                    </a>
                    <a href="{{ route('roles.index') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="db-at-lbl">Roles</div>
                    </a>
                    <a href="{{ route('projects.create') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-diagram-project"></i></div>
                        <div class="db-at-lbl">New Project</div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Users --}}
        <div class="col-12 col-xl-5">
            <div class="db-card h-100">
                <div class="db-card-head">
                    <div class="db-card-title">Recent Users</div>
                    <a href="{{ route('users.index') }}" class="db-viewall">View all</a>
                </div>
                <div class="db-user-list">
                    @foreach ($recentUsers as $u)
                        @php
                            $uInitials = strtoupper(substr($u->fname ?? $u->email, 0, 1));
                            $uColor = $avatarPalette[crc32($u->email) % count($avatarPalette)];
                            $uName = trim(($u->fname ?? '') . ' ' . ($u->lname ?? '')) ?: $u->email;
                        @endphp
                        <div class="db-user-row">
                            <a href="{{ url('/analytics/users/' . $u->id) }}" target="_blank" rel="noopener" title="View analytics">
                                @if ($u->avatar)
                                    <div class="db-u-avatar" style="background:#f1f5f9; padding:0; overflow:hidden;">
                                        <img src="{{ $u->avatar_url }}" alt="{{ $uInitials }}"
                                            style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                                    </div>
                                @else
                                    <div class="db-u-avatar" style="background:#f1f5f9;">
                                        <i class="fa-solid fa-circle-user"
                                            style="font-size:34px;color:#94a3b8;line-height:1;"></i>
                                    </div>
                                @endif
                            </a>
                            <div class="db-u-meta">
                                <a href="{{ url('/analytics/users/' . $u->id) }}" target="_blank" rel="noopener" class="db-u-name-link">
                                    <div class="db-u-name">{{ $uName }}</div>
                                </a>
                                <div class="db-u-email">{{ $u->email }}</div>
                            </div>
                            <div class="db-u-status {{ is_null($u->deleted_at) ? 'db-u-active' : 'db-u-inactive' }}">
                                {{ is_null($u->deleted_at) ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
     RECENTLY ACCESSED FILES
══════════════════════════════════════════════════════════ --}}
    <div class="db-card">
        <div class="db-card-head">
            <div>
                <div class="db-card-title">Recently Accessed Files</div>
                <div class="db-card-sub">Documents you recently opened — visible only to you.</div>
            </div>
            <div class="d-flex gap-2">
                <button class="db-scroll-btn" id="filesPrev" title="Previous"><i
                        class="fa-solid fa-chevron-left"></i></button>
                <button class="db-scroll-btn" id="filesNext" title="Next"><i
                        class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="db-files-wrap">
            <div class="db-files-track" id="filesTrack">
                @forelse ($recentFiles as $recent)
                    @php
                        $fname = $recent->folder->name;
                        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                        [$bg, $fa, $clr] = match (true) {
                            in_array($ext, ['doc', 'docx']) => ['#dbeafe', 'fa-file-word', '#2563eb'],
                            in_array($ext, ['xls', 'xlsx']) => ['#dcfce7', 'fa-file-excel', '#16a34a'],
                            in_array($ext, ['ppt', 'pptx']) => ['#ffedd5', 'fa-file-powerpoint', '#ea580c'],
                            $ext === 'pdf' => ['#fee2e2', 'fa-file-pdf', '#dc2626'],
                            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => [
                                '#fae8ff',
                                'fa-file-image',
                                '#9333ea',
                            ],
                            in_array($ext, ['zip', 'rar', '7z']) => ['#f1f5f9', 'fa-file-zipper', '#64748b'],
                            default => ['#f1f5f9', 'fa-file', '#64748b'],
                        };
                    @endphp
                    <a href="{{ route('files.preview', base64_encode($recent->folder->id)) }}" class="db-file-card"
                        title="{{ $fname }}">
                        <div class="db-file-thumb" style="background:{{ $bg }};color:{{ $clr }};">
                            <i class="fa-solid {{ $fa }}"></i>
                        </div>
                        <div class="db-file-info">
                            <div class="db-file-name">{{ Str::limit($fname, 20) }}</div>
                            <div class="db-file-ext">{{ strtoupper($ext) ?: 'FILE' }}</div>
                        </div>
                    </a>
                @empty
                    <div class="db-files-empty">
                        <i class="fa-regular fa-clock"></i>
                        <span>No recently viewed files yet. Open a document to see it here.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection

@push('addOnCss')
    @include('admin.dashboard._styles')
@endpush

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Project cards scroller
            var ps = document.getElementById('projScroller');

            // File scroller
            var ft = document.getElementById('filesTrack');
            var prev = document.getElementById('filesPrev');
            var next = document.getElementById('filesNext');

            if (ft && prev) prev.addEventListener('click', function() {
                ft.scrollBy({
                    left: -300,
                    behavior: 'smooth'
                });
            });
            if (ft && next) next.addEventListener('click', function() {
                ft.scrollBy({
                    left: 300,
                    behavior: 'smooth'
                });
            });
        });
    </script>
@endpush

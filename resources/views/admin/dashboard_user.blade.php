@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

    @php
        $firstName = $authUser->fname ?? 'there';
        $initials = strtoupper(substr($firstName, 0, 1));
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
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
                    <div class="db-hero-sub">Welcome to Kingsmealagro Data Room</div>
                </div>
            </div>

            {{-- Stat chips inside hero — scoped to exactly what's been granted to this user --}}
            <div class="db-hero-stats">
                @foreach ([['val' => $activeProjects, 'lbl' => 'Your Folders', 'icon' => 'fa-diagram-project', 'color' => '#38bdf8', 'bg' => 'rgba(56,189,248,.18)'], ['val' => $favoritesCount, 'lbl' => 'Favorites', 'icon' => 'fa-star', 'color' => '#a78bfa', 'bg' => 'rgba(167,139,250,.18)'], ['val' => $totalFiles, 'lbl' => 'Total Files', 'icon' => 'fa-file-lines', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,.18)'], ['val' => $loginsToday, 'lbl' => 'Logins Today', 'icon' => 'fa-arrow-right-to-bracket', 'color' => '#34d399', 'bg' => 'rgba(52,211,153,.18)']] as $i => $stat)
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
     RECENT PROJECTS  +  FAVORITES / FILE ROOM SHORTCUTS
══════════════════════════════════════════════════════════ --}}
    @php
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
                    <a href="{{ route('shared.folders') }}#path={{ $proj->id }}" class="db-archive-card">
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
                        <span>No folders shared with you yet.</span>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="db-pcard db-pcard-empty"></div>
                </div>
            @endforelse

            {{-- Favorites box --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('favorite.folders') }}" class="db-archive-card db-archive-card-favorite">
                    <div class="db-archive-card-row">
                        <div>
                            <div class="db-archive-card-count">{{ $favoritesCount }}</div>
                            <div class="db-pcard-name">{{ Str::plural('Favorite', $favoritesCount) }}</div>
                        </div>
                    </div>
                    <div class="db-archive-card-link">
                        Browse favorites <i class="fa-solid fa-arrow-right ms-1" style="font-size:10px;"></i>
                    </div>
                </a>
            </div>

            {{-- Browse File Room CTA --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('shared.folders') }}" class="db-newproj-card">
                    <div class="db-newproj-row">
                        <div class="db-newproj-icon"><i class="fa-solid fa-folder-tree"></i></div>
                        <div>
                            <div class="db-newproj-title">Browse the File Room</div>
                            <div class="db-newproj-sub">See everything shared with you</div>
                        </div>
                    </div>
                    <div class="db-newproj-link">Open now <i class="fa-solid fa-arrow-right ms-1"
                            style="font-size:10px;"></i></div>
                </a>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
     QUICK ACTIONS  +  RECENT ACTIVITY
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
                    <a href="{{ route('favorite.folders') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-regular fa-star"></i></div>
                        <div class="db-at-lbl">Favourites</div>
                    </a>
                    <a href="{{ route('my-activity.index') }}" class="db-action-tile">
                        <div class="db-at-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div class="db-at-lbl">My Activity</div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="col-12 col-xl-5">
            <div class="db-card h-100">
                <div class="db-card-head">
                    <div class="db-card-title">Recent Activity</div>
                    <a href="{{ route('my-activity.index') }}" class="db-viewall">View all</a>
                </div>
                <div class="db-user-list">
                    @forelse ($recentActivity as $log)
                        @php
                            // This panel is already scoped to just the current
                            // user, so restating their own name on every row
                            // ("John Doe downloaded...") is pure noise — strip
                            // the leading "{actor} " the description was built
                            // with and capitalize what's left instead.
                            $shortDesc = $log->user_name
                                ? \Illuminate\Support\Str::after($log->description, $log->user_name . ' ')
                                : $log->description;
                            $shortDesc = \Illuminate\Support\Str::ucfirst($shortDesc);
                        @endphp
                        <div class="db-user-row">
                            <span class="db-act-badge {{ $log->action }}">{{ str_replace('_', ' ', $log->action) }}</span>
                            <div class="db-u-meta">
                                <div class="db-act-desc" title="{{ $log->description }}">{{ $shortDesc }}</div>
                            </div>
                            <div class="db-act-time">{{ $log->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="db-act-empty">
                            <i class="fa-regular fa-clock"></i>
                            <span>No activity yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

@endsection

@push('addOnCss')
    @include('admin.dashboard._styles')
@endpush

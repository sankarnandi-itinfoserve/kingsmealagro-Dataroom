<div class="sidebar_inner">

    {{-- Logo --}}
    <div class="logo text-center">
        <img src="{{ asset('admin/images/kingsmeal-agro-logo.png') }}" alt="Logo" class="sidebar-logo">
    </div>


    <div class="menu p-3">
        {{-- <div class="sidebar-close-row">
        <button type="button" id="closeSidebar" class="sidebar-close-btn" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div> --}}

        <ul class="menu-list">
            <li>
                <a href="{{ route('dashboard') }}" data-tooltip="Dashboard"
                    class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>
            <li id="treeViewMenu"
                class="has-submenu {{ request()->routeIs('shared.folders') || isset($activeFileId) ? 'open' : '' }}">
                <a id="treeViewMenuLink" href="{{ route('shared.folders') }}" data-tooltip="Shared Folders"
                    class="{{ request()->routeIs('shared.folders') ? 'active menu-item' : '' }}">
                    <i class="fa fa-folder"></i>
                    <span class="menu-label">Shared Folders</span>
                    @if (request()->routeIs('shared.folders') || isset($activeFileId))
                        <span class="ms-auto d-flex align-items-center gap-2">
                            <span id="fbSearchBtn" class="fb-root-btn" title="Search folders &amp; files">
                                <i class="fa fa-search"></i>
                            </span>
                            <span id="fbHomeBtn" class="fb-root-btn" title="Go to Shared Folders home">
                                <i class="fa fa-house"></i>
                            </span>
                            <span id="fbGoToRootBtn" class="fb-root-btn" title="Collapse tree">
                                <i class="fa fa-minus"></i>
                            </span>
                            <i class="fa fa-chevron-down toggle-icon"></i>
                        </span>
                    @endif
                </a>

                <ul class="submenu">
                    <li>
                        <div class="px-0">
                            <div id="fbTreeSearchWrap" class="fb-tree-search-wrap d-none">
                                <i class="fa fa-search fb-tree-search-icon"></i>
                                <input type="text" id="fbTreeSearchInput" class="fb-tree-search-input"
                                    placeholder="Search folders &amp; files..." autocomplete="off">
                            </div>
                            <div id="fbTreeView" class="fb-tree-view"></div>
                        </div>
                    </li>
                </ul>
            </li>
            @hasanyrole('admin|super-admin')
                <li>
                    <a href="{{ route('projects.index') }}" data-tooltip="Folders Management"
                        class="menu-item {{ request()->routeIs('projects.index') || request()->routeIs('projects.create') || request()->routeIs('projects.edit') || request()->routeIs('projects.show') || request()->routeIs('projects.store') || request()->routeIs('projects.update') ? 'active' : '' }}">
                        <i class="fa-solid fa-diagram-project"></i>
                        <span class="menu-label">Folders Management</span>
                    </a>
                </li>
            @endhasanyrole
            <li>
                <a href="{{ route('favorite.folders') }}" data-tooltip="Favorites"
                    class="menu-item {{ request()->routeIs('favorite.folders') ? 'active' : '' }}">
                    <i class="fa fa-star"></i>
                    <span class="menu-label">Favorites</span>
                </a>
            </li>
            @hasanyrole('admin|super-admin')
                <li>
                    <a href="{{ route('analytics.index') }}" data-tooltip="Analytics"
                        class="menu-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="menu-label">Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('activity-logs.index') }}" data-tooltip="Activity Logs"
                        class="menu-item {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="menu-label">Activity Logs</span>
                    </a>
                </li>
            @endhasanyrole
            @unless (auth()->user()->hasAnyRole(['admin', 'super-admin']))
                <li>
                    <a href="{{ route('my-activity.index') }}" data-tooltip="My Activity"
                        class="menu-item {{ request()->routeIs('my-activity.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="menu-label">My Activity</span>
                    </a>
                </li>
            @endunless
            @hasanyrole('admin|super-admin')
                <li
                    class="has-submenu {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('invitations.*') || request()->routeIs('groups.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)" data-tooltip="Settings"
                        class="menu-link {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('invitations.*') || request()->routeIs('groups.*') ? 'active' : '' }}">

                        <i class="fa fa-cog"></i>
                        <span class="menu-label">Settings</span>
                        <i class="fa fa-chevron-down ms-auto toggle-icon "></i>
                    </a>

                    <ul class="submenu">
                        @can('view users_roles')
                            <li>
                                <a href="{{ route('roles.index') }}"
                                    class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                    <i class="fa fa-user-shield"></i>
                                    <span class="menu-label">Roles &amp; Permissions</span>
                                </a>
                            </li>
                        @endcan

                        @can('view users')
                            <li>
                                <a href="{{ route('users.index') }}"
                                    class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="fa fa-users"></i>
                                    <span class="menu-label">Users</span>
                                </a>
                            </li>
                        @endcan

                        @can('view groups')
                            <li>
                                <a href="{{ route('groups.index') }}"
                                    class="menu-item {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-people-group"></i>
                                    <span class="menu-label">Groups</span>
                                </a>
                            </li>
                        @endcan

                    </ul>
                </li>

                <li>
                    <a href="{{ route('projects.archived') }}" data-tooltip="Archive"
                        class="menu-item {{ request()->routeIs('projects.archived') ? 'active' : '' }}">
                        <i class="fa-solid fa-box-archive"></i>
                        <span class="menu-label">Archive</span>
                    </a>
                </li>
            @endhasanyrole

        </ul>
    </div>

</div>

<style>
    /* Tree View - Sidebar styles (professional, compact) */
    .fb-tree-view {
        max-height: 360px;
        overflow: auto;
        padding: 8px;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.8);
    }

    .fb-tree-node {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        border-radius: 6px;
        padding: 2px 8px;
        cursor: pointer;
        transition: background .12s ease, color .12s ease, transform .06s ease;
        user-select: none;
    }

    .fb-tree-node {
        position: relative;
    }

    .fb-tree-node:hover {
        background: rgba(255, 255, 255, 0.07);
        color: #fff;
        transform: translateY(-1px);
    }

    .fb-tree-node.active {
        background: rgba(192, 49, 42, 0.35);
        color: #fff;
        font-weight: 600;
    }

    /* connector lines for tree view */
    .fb-tree-view {
        position: relative;
    }

    .fb-tree-node::before {
        /* vertical dotted connector line */
        content: "";
        position: absolute;
        left: calc(var(--depth-left, 0px) + 9px);
        top: 0;
        bottom: 0;
        width: 0;
        border-left: 1px dotted rgba(255, 255, 255, 0.2);
        /* subtle on dark background */
        z-index: 0;
    }

    .fb-tree-node::after {
        /* horizontal dotted connector to label */
        content: "";
        position: absolute;
        left: calc(var(--depth-left, 0px) + 9px);
        top: 50%;
        width: 14px;
        height: 0;
        border-top: 1px dotted rgba(255, 255, 255, 0.2);
        /* subtle on dark background */
        transform: translateY(-50%);
        z-index: 0;
    }

    .fb-tree-toggle,
    .fb-tree-label {
        position: relative;
        z-index: 2;
    }

    .fb-tree-toggle {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        width: 18px;
        height: 18px;
        min-width: 18px;
        padding: 0;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-weight: 700;
        box-shadow: none;
    }

    .fb-tree-label {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1 1 auto;
        font-size: 13px;
        overflow: hidden;
    }

    .fb-tree-label i {
        width: 18px;
        text-align: center;
        flex-shrink: 0;
    }

    /* Long folder names truncate with an ellipsis instead of wrapping —
       wrapped text made that row taller than its siblings and threw off
       the tree's vertical rhythm (toggle/folder icons no longer lined up
       in a straight column from one row to the next). Also covers <a>
       (file name links) — this tree panel sits inside a .submenu-classed
       container, so a plain <a> here would otherwise inherit that
       unrelated global rule's button-like padding/flex sizing meant for
       nav links, inflating row height. */
    .fb-tree-label span,
    .fb-tree-label a {
        display: inline-block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 0;
        vertical-align: middle;
    }

    /* scrollbar styling */
    .fb-tree-view::-webkit-scrollbar {
        width: 8px;
    }

    .fb-tree-view::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 6px;
    }

    .fb-tree-view::-webkit-scrollbar-track {
        background: transparent;
    }

    .fb-root-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border-radius: 4px;
        cursor: pointer;
        opacity: 0.65;
        transition: opacity 0.15s, background 0.15s;
        flex-shrink: 0;
        border: 1px solid;
    }

    .fb-root-btn:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.18);
    }

    .fb-root-btn i {
        font-size: 10px;
        line-height: 1;
    }

    .fb-tree-search-wrap {
        position: relative;
        margin: 0 8px 8px;
    }

    .fb-tree-search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: rgba(255, 255, 255, 0.45);
    }

    .fb-tree-search-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 6px;
        color: #fff;
        font-size: 13px;
        padding: 6px 10px 6px 28px;
        outline: none;
    }

    .fb-tree-search-input::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .fb-tree-search-input:focus {
        border-color: rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.12);
    }

    /* Scoped to the tree expand/collapse toggle and the small root/collapse
       buttons only — this used to be a bare, unscoped .fa-plus/.fa-minus
       rule with !important, which silently forced EVERY plus/minus icon
       site-wide (Add Role, Add Company, Save Role, etc.) down to x-small. */
    .fb-tree-toggle .fa-add:before,
    .fb-tree-toggle .fa-plus:before,
    .fb-root-btn .fa-add:before,
    .fb-root-btn .fa-plus:before {
        font-size: x-small !important;
    }

    .fb-tree-toggle .fa-minus:before,
    .fb-tree-toggle .fa-subtract:before,
    .fb-root-btn .fa-minus:before,
    .fb-root-btn .fa-subtract:before {
        font-size: x-small !important;
    }

    @media (max-width: 768px) {
        .fb-tree-view {
            max-height: 220px;
        }

        .fb-tree-node {
            padding: 6px;
            font-size: 13px;
        }
    }
</style>
@push('script')
    <script>
        $(function() {
            var $link = $('#treeViewMenuLink');
            if (!$link.length) return;

            $link.on('click', function(e) {
                try {
                    var href = $link.attr('href');
                    var target = new URL(href, window.location.origin);
                    if (window.location.pathname === target.pathname) {
                        e.preventDefault();
                        var $li = $('#treeViewMenu');
                        $li.toggleClass('open');
                        $link.toggleClass('active');
                    }
                } catch (err) {
                    // noop
                }
            });

            $('#fbGoToRootBtn').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (typeof window._fbToggleTreeCollapse === 'function') {
                    window._fbToggleTreeCollapse();
                }
            });

            $('#fbHomeBtn').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (typeof window._fbGoToRoot === 'function') {
                    window._fbGoToRoot();
                } else {
                    window.location.href = $link.attr('href');
                }
            });

            var $searchWrap = $('#fbTreeSearchWrap');
            var $searchInput = $('#fbTreeSearchInput');
            var SEARCH_STORAGE_KEY = 'fb_tree_search_query';

            function runTreeSearch(query) {
                if (typeof window._fbTreeSearch === 'function') {
                    window._fbTreeSearch(query);
                }
            }

            $('#fbSearchBtn').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var opening = $searchWrap.hasClass('d-none');
                $searchWrap.toggleClass('d-none');
                $searchInput.val('');
                runTreeSearch('');
                sessionStorage.removeItem(SEARCH_STORAGE_KEY);
                if (opening) {
                    $('#treeViewMenu').addClass('open');
                    $link.addClass('active');
                    $searchInput.trigger('focus');
                }
            });

            $searchInput.on('click', function(e) {
                e.stopPropagation();
            });

            $searchInput.on('input', function() {
                var val = $(this).val();
                runTreeSearch(val);
                if (val) {
                    sessionStorage.setItem(SEARCH_STORAGE_KEY, val);
                } else {
                    sessionStorage.removeItem(SEARCH_STORAGE_KEY);
                }
            });

            // Restore an in-progress search across navigation — clicking a
            // search result in the tree (e.g. a file link) is a full page
            // load, which would otherwise silently drop the query and show
            // the unfiltered tree on arrival.
            var savedQuery = sessionStorage.getItem(SEARCH_STORAGE_KEY);
            if (savedQuery) {
                $searchWrap.removeClass('d-none');
                $searchInput.val(savedQuery);
                $('#treeViewMenu').addClass('open');
                $link.addClass('active');
                runTreeSearch(savedQuery);
            }
        });
    </script>
@endpush
@unless (request()->routeIs('shared.folders'))
    {{-- The Drive page builds/owns this tree itself (with full list/grid
         interactivity). Everywhere else, render a lightweight read-only
         version — only has real content when the page passed rootFolderData
         (currently just the file preview page), so it stays an empty no-op
         on pages that never touch the sidebar tree. --}}
    @push('script')
        <script>
            $(function() {
                var $tree = $('#fbTreeView');
                if (!$tree.length) return;

                @php
                    $treeRootFolder = $rootFolderData ?? ['id' => 0, 'type' => 'folder', 'name' => 'Documents', 'children' => []];
                    $treeActiveFileId = $activeFileId ?? null;
                @endphp
                var rootFolder = @json($treeRootFolder);
                var activeFileId = @json($treeActiveFileId);
                var sharedFoldersUrl = '{{ route('shared.folders') }}';
                var expandedKeys = new Set();
                var searchQuery = '';

                // True if this item's own name matches, or (for a folder) any
                // descendant's does — a folder must still render while a
                // matching item is buried inside it.
                function nodeMatchesSearch(item) {
                    if (!searchQuery) return true;
                    if ((item.name || '').toLowerCase().includes(searchQuery)) return true;
                    return (item.children || []).some(nodeMatchesSearch);
                }

                function nodeTreeKey(node) {
                    return `${node.type || 'folder'}:${node.id}`;
                }

                function getItemIcon(row) {
                    if (row.type === 'folder') return {
                        fa: 'fa-folder',
                        cls: 'fb-folder-icon'
                    };
                    var ext = (row.ext || row.name.split('.').pop()).toLowerCase();
                    if (ext === 'pdf') return {
                        fa: 'fa-file-pdf',
                        cls: 'fb-file-pdf-icon'
                    };
                    if (['doc', 'docx'].includes(ext)) return {
                        fa: 'fa-file-word',
                        cls: 'fb-file-word-icon'
                    };
                    if (['xls', 'xlsx', 'csv'].includes(ext)) return {
                        fa: 'fa-file-excel',
                        cls: 'fb-file-excel-icon'
                    };
                    if (['ppt', 'pptx'].includes(ext)) return {
                        fa: 'fa-file-powerpoint',
                        cls: 'fb-file-ppt-icon'
                    };
                    if (['zip', 'rar', '7z'].includes(ext)) return {
                        fa: 'fa-file-zipper',
                        cls: 'fb-file-zip-icon'
                    };
                    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) return {
                        fa: 'fa-file-image',
                        cls: 'fb-file-img-icon'
                    };
                    return {
                        fa: 'fa-file',
                        cls: 'fb-file-icon'
                    };
                }

                // Ancestor FOLDER nodes (root excluded) leading down to targetId,
                // not including targetId itself — null if targetId isn't found.
                function findAncestorFolders(node, targetId, trail) {
                    trail = trail || [];
                    var children = node.children || [];
                    for (var i = 0; i < children.length; i++) {
                        var child = children[i];
                        if (String(child.id) === String(targetId)) return trail;
                        if (child.type === 'folder') {
                            var found = findAncestorFolders(child, targetId, trail.concat([child]));
                            if (found) return found;
                        }
                    }
                    return null;
                }

                if (activeFileId !== null) {
                    (findAncestorFolders(rootFolder, activeFileId) || []).forEach(function(folder) {
                        expandedKeys.add(nodeTreeKey(folder));
                    });
                }

                function buildRows(node, depth) {
                    var children = (node.children || [])
                        .filter(nodeMatchesSearch)
                        .slice()
                        .sort(function(a, b) {
                            return (a.name || '').localeCompare(b.name || '', undefined, {
                                sensitivity: 'base',
                                numeric: true
                            });
                        });

                    return children.map(function(item) {
                        var isFolder = item.type === 'folder';
                        var hasChildren = isFolder && (item.children || []).length > 0;
                        var itemKey = nodeTreeKey(item);
                        // While searching, every folder that survived the filter
                        // above necessarily contains a match somewhere inside it —
                        // force it open so that match is actually visible.
                        var isExpanded = hasChildren && (!!searchQuery || expandedKeys.has(itemKey));
                        var isActiveFile = !isFolder && activeFileId !== null && String(item.id) === String(
                            activeFileId);
                        var icon = getItemIcon(item);
                        var nameHtml;
                        if (isFolder) {
                            var pathIds = (findAncestorFolders(rootFolder, item.id) || []).map(f => f.id)
                                .concat([item.id]).join(',');
                            nameHtml =
                                `<a href="${sharedFoldersUrl}#path=${pathIds}" class="fb-name-link" title="${item.name}">${item.name}</a>`;
                        } else {
                            nameHtml =
                                `<a href="/files/${btoa(String(item.id))}/preview" class="fb-name-link" title="${item.name}">${item.name}</a>`;
                        }

                        var row = `
                            <div class="fb-tree-node ${isActiveFile ? 'active' : ''}" data-tree-key="${itemKey}" style="padding-left:${depth * 14 + 20}px; --depth-left: ${depth * 14}px;">
                                <span class="fb-tree-spacer"></span>
                                ${hasChildren
                                    ? `<button type="button" class="fb-tree-toggle" data-tree-toggle="${itemKey}" style="left:${depth * 14}px"><i class="fa-solid ${isExpanded ? 'fa-minus' : 'fa-plus'}"></i></button>`
                                    : ''}
                                <span class="fb-tree-label">
                                    <i class="fa-solid ${icon.fa} ${icon.cls}"></i>
                                    ${nameHtml}
                                </span>
                            </div>
                        `;

                        if (!isFolder || !hasChildren || !isExpanded) return row;
                        return row + buildRows(item, depth + 1);
                    }).join('');
                }

                function render() {
                    $tree.html(buildRows(rootFolder, 0));
                    var active = $tree.find('.fb-tree-node.active')[0];
                    if (active) active.scrollIntoView({
                        block: 'nearest'
                    });
                }

                $tree.on('click', '[data-tree-toggle]', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var key = $(this).data('tree-toggle');
                    if (expandedKeys.has(key)) expandedKeys.delete(key);
                    else expandedKeys.add(key);
                    render();
                });

                window._fbTreeSearch = function(query) {
                    searchQuery = (query || '').trim().toLowerCase();
                    render();
                };

                // A search result link is a full page load — read back
                // whatever query was in progress so the tree lands here
                // already filtered, matching what the search box shows.
                searchQuery = (sessionStorage.getItem('fb_tree_search_query') || '').trim().toLowerCase();

                render();
            });
        </script>
    @endpush
@endunless

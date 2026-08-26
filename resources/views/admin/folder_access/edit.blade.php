@extends('admin.layouts.app')
@section('title', 'Folder Access')
@section('page_title', 'Folder Access')

@php
    $targetName = $type === 'group' ? $target->name : ($target->full_name ?? $target->email);
    $backRoute = $type === 'group' ? route('groups.index') : route('users.index');
@endphp

@push('addOnCss')
    <style>
        .fa-header-sub {
            font-size: 12.5px;
            color: rgba(255, 255, 255, .6);
        }

        .fa-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            background: #fff;
            border: 1.5px solid #dbe4f0;
            border-radius: 9px;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
        }

        .fa-back-btn:hover {
            background: #f1f5f9;
            color: #253447;
        }

        .fa-save-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #253447, #1a2737);
            border: none;
            border-radius: 9px;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(37, 52, 71, .25);
            transition: opacity .15s;
        }

        .fa-save-btn:hover {
            opacity: .9;
        }

        .fa-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            border-bottom: 1px solid #eef2f7;
        }

        .fa-toolbar-btn {
            padding: 6px 14px;
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            cursor: pointer;
            transition: all .15s;
        }

        .fa-toolbar-btn:hover {
            background: #eef2f7;
            color: #253447;
        }

        .fa-tree-wrap {
            padding: 12px 24px 28px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .fa-node {
            position: relative;
        }

        .fa-node-row {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 0;
            margin-left: calc(var(--depth) * 22px);
        }

        .fa-toggle {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: none;
            color: #94a3b8;
            cursor: pointer;
            flex-shrink: 0;
            transition: transform .15s, color .15s;
        }

        .fa-toggle:hover {
            color: #253447;
        }

        .fa-toggle.open i {
            transform: rotate(90deg);
        }

        .fa-toggle-spacer {
            width: 20px;
            flex-shrink: 0;
        }

        .fa-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            padding: 3px 8px;
            border-radius: 7px;
            transition: background .12s;
        }

        .fa-check-label:hover {
            background: #f1f5f9;
        }

        .fa-check-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #253447;
            cursor: pointer;
            flex-shrink: 0;
        }

        .fa-node-icon {
            font-size: 13px;
            flex-shrink: 0;
        }

        .fa-node-name {
            font-size: 13px;
            color: #1e293b;
            font-weight: 600;
        }

        .fa-children {
            display: none;
        }

        .fa-children.open {
            display: block;
        }

        .fa-empty {
            text-align: center;
            padding: 48px 24px;
            color: #94a3b8;
        }

        .fa-empty i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card rol-card">

            <div class="rol-card-header">
                <div class="rol-card-header-icon">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div class="flex-grow-1">
                    <div style="color:#fff;font-size:15px;font-weight:700;">Folder Access</div>
                    <div class="fa-header-sub">
                        {{ $type === 'group' ? 'Group' : 'User' }}: {{ $targetName }}
                    </div>
                </div>
                <div class="rol-header-actions">
                    <a href="{{ $backRoute }}" class="fa-back-btn">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <form id="folderAccessForm" action="{{ route('folder-access.update', ['type' => $type, 'id' => $id]) }}"
                method="POST">
                @csrf

                <div class="fa-toolbar">
                    <button type="button" class="fa-toolbar-btn" id="faToggleExpand">
                        <i class="fa-solid fa-chevron-down me-1"></i> Expand All
                    </button>
                    <button type="button" class="fa-toolbar-btn" id="faCheckAll">
                        <i class="fa-solid fa-square-check me-1"></i> Check All
                    </button>
                    <button type="button" class="fa-toolbar-btn" id="faUncheckAll">
                        <i class="fa-regular fa-square me-1"></i> Uncheck All
                    </button>
                    <button type="submit" class="fa-save-btn ms-auto">
                        <i class="fa fa-check me-1"></i> Save Access
                    </button>
                </div>

                <div class="fa-tree-wrap" id="faTreeWrap">
                    @if ($rootFolders->isEmpty())
                        <div class="fa-empty">
                            <i class="fa-solid fa-folder-open"></i>
                            <p>No folders found.</p>
                        </div>
                    @else
                        @include('admin.folder_access._tree', [
                            'nodes' => $rootFolders,
                            'grantedFolderIds' => $grantedFolderIds,
                            'depth' => 0,
                        ])
                    @endif
                </div>
            </form>

        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrap = document.getElementById('faTreeWrap');

            wrap.addEventListener('click', function(e) {
                const toggle = e.target.closest('.fa-toggle');
                if (!toggle) return;

                const children = toggle.closest('.fa-node').querySelector(':scope > .fa-children');
                if (!children) return;

                toggle.classList.toggle('open');
                children.classList.toggle('open');
            });

            let faExpanded = false;
            document.getElementById('faToggleExpand').addEventListener('click', function() {
                faExpanded = !faExpanded;
                wrap.querySelectorAll('.fa-children').forEach(el => el.classList.toggle('open', faExpanded));
                wrap.querySelectorAll('.fa-toggle').forEach(el => el.classList.toggle('open', faExpanded));
                this.innerHTML = faExpanded ?
                    '<i class="fa-solid fa-chevron-right me-1"></i> Collapse All' :
                    '<i class="fa-solid fa-chevron-down me-1"></i> Expand All';
            });

            wrap.addEventListener('change', function(e) {
                const cb = e.target;
                if (cb.type !== 'checkbox') return;

                const node = cb.closest('.fa-node');
                const depth = parseInt(node.dataset.depth, 10);

                // Every item below the top level needs its immediate parent
                // checked first — keeps grants readable top-down instead of
                // scattered orphan picks buried in the tree.
                if (cb.checked && depth > 0) {
                    const parentNode = node.parentElement.closest('.fa-node');
                    const parentCheckbox = parentNode?.querySelector(':scope > .fa-node-row input[type="checkbox"]');
                    if (parentCheckbox && !parentCheckbox.checked) {
                        alert('Please check the parent folder first before granting access to this item.');
                        cb.checked = false;
                        return;
                    }
                }

                // Root-level folders never auto-check their children — those
                // are broad top-level containers, so each subfolder
                // underneath needs its own deliberate check. Any deeper
                // subfolder DOES cascade to its own descendants as a
                // convenience; admins can uncheck individual items
                // afterward to carve out exceptions.
                if (depth === 0) return;

                const childrenWrap = node.querySelector(':scope > .fa-children');
                if (!childrenWrap) return;

                childrenWrap.querySelectorAll('input[type="checkbox"]').forEach(child => {
                    child.checked = cb.checked;
                });
            });

            document.getElementById('faCheckAll').addEventListener('click', function() {
                wrap.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
            });

            document.getElementById('faUncheckAll').addEventListener('click', function() {
                wrap.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            });

            document.getElementById('folderAccessForm').addEventListener('submit', function() {
                showToast('Saving folder access…', 'success');
            });

            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
        });
    </script>
@endpush

<div class="fabu-wrap">

    <div class="fabu-header">
        <h5 class="fabu-title"><i class="fa-solid fa-folder-open me-2"></i>Folder Access</h5>
        <button type="button" class="btn-close" onclick="closeModal()"></button>
    </div>

    <div class="fabu-body">
        <p class="fabu-sub">
            Granting access to <strong>{{ $users->count() }}</strong> user{{ $users->count() === 1 ? '' : 's' }}:
            <span class="fabu-user-list">{{ $users->pluck('full_name')->implode(', ') }}</span>
        </p>

        <form id="bulkFolderAccessForm">
            @csrf
            @foreach ($users as $u)
                <input type="hidden" name="user_ids[]" value="{{ $u->id }}">
            @endforeach

            <div class="fabu-toolbar">
                <button type="button" class="fabu-toolbar-btn" id="fabuToggleExpand">
                    <i class="fa-solid fa-chevron-down me-1"></i> Expand All
                </button>
                <button type="button" class="fabu-toolbar-btn" id="fabuCheckAll">
                    <i class="fa-solid fa-square-check me-1"></i> Check All
                </button>
                <button type="button" class="fabu-toolbar-btn" id="fabuUncheckAll">
                    <i class="fa-regular fa-square me-1"></i> Uncheck All
                </button>
            </div>

            <div class="fabu-tree-wrap" id="fabuTreeWrap">
                @if ($rootFolders->isEmpty())
                    <div class="fabu-empty">
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

    <div class="fabu-footer">
        <button type="button" class="fabu-cancel-btn" onclick="closeModal()">Cancel</button>
        <button type="button" class="fabu-save-btn" id="fabuSaveBtn" onclick="submitBulkFolderAccess()">
            <i class="fa fa-check me-1"></i> Save Access
        </button>
    </div>

</div>

<style>
    .fabu-wrap {
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 3.5rem);
        overflow: hidden;
    }

    .fabu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 24px;
        background: linear-gradient(135deg, #253447 0%, #1a2737 100%);
        flex-shrink: 0;
    }

    .fabu-title {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        margin: 0;
    }

    .fabu-header .btn-close {
        filter: invert(1) brightness(2);
    }

    .fabu-body {
        padding: 20px 24px;
        overflow-y: auto;
        flex: 1 1 auto;
        min-height: 0;
    }

    .fabu-sub {
        font-size: 12.5px;
        color: #64748b;
        margin-bottom: 14px;
    }

    .fabu-user-list {
        color: #253447;
        font-weight: 600;
    }

    .fabu-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eef2f7;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .fabu-toolbar-btn {
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

    .fabu-toolbar-btn:hover {
        background: #eef2f7;
        color: #253447;
    }

    .fabu-tree-wrap {
        max-height: 45vh;
        overflow-y: auto;
    }

    .fabu-empty {
        text-align: center;
        padding: 40px 24px;
        color: #94a3b8;
    }

    .fabu-empty i {
        font-size: 36px;
        margin-bottom: 10px;
        display: block;
    }

    .fabu-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 24px;
        border-top: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .fabu-cancel-btn {
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 9px;
        cursor: pointer;
    }

    .fabu-save-btn {
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
    }

    .fabu-save-btn:disabled {
        opacity: .7;
        cursor: not-allowed;
    }

    /* ── Tree nodes (shared with the single-target Folder Access page) ── */
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
</style>

<script>
    (function() {
        const wrap = document.getElementById('fabuTreeWrap');
        if (!wrap) return;

        wrap.addEventListener('click', function(e) {
            const toggle = e.target.closest('.fa-toggle');
            if (!toggle) return;

            const children = toggle.closest('.fa-node').querySelector(':scope > .fa-children');
            if (!children) return;

            toggle.classList.toggle('open');
            children.classList.toggle('open');
        });

        let fabuExpanded = false;
        document.getElementById('fabuToggleExpand').addEventListener('click', function() {
            fabuExpanded = !fabuExpanded;
            wrap.querySelectorAll('.fa-children').forEach(el => el.classList.toggle('open', fabuExpanded));
            wrap.querySelectorAll('.fa-toggle').forEach(el => el.classList.toggle('open', fabuExpanded));
            this.innerHTML = fabuExpanded ?
                '<i class="fa-solid fa-chevron-right me-1"></i> Collapse All' :
                '<i class="fa-solid fa-chevron-down me-1"></i> Expand All';
        });

        // Root-level folders never auto-check their children; any deeper
        // subfolder cascades to its own descendants as a convenience. Every
        // item below the top level also needs its immediate parent checked
        // first. See edit.blade.php for the same behavior on the
        // single-target page.
        wrap.addEventListener('change', function(e) {
            const cb = e.target;
            if (cb.type !== 'checkbox') return;

            const node = cb.closest('.fa-node');
            const depth = parseInt(node.dataset.depth, 10);

            if (cb.checked && depth > 0) {
                const parentNode = node.parentElement.closest('.fa-node');
                const parentCheckbox = parentNode?.querySelector(':scope > .fa-node-row input[type="checkbox"]');
                if (parentCheckbox && !parentCheckbox.checked) {
                    alert('Please check the parent folder first before granting access to this item.');
                    cb.checked = false;
                    return;
                }
            }

            if (depth === 0) return;

            const childrenWrap = node.querySelector(':scope > .fa-children');
            if (!childrenWrap) return;

            childrenWrap.querySelectorAll('input[type="checkbox"]').forEach(child => {
                child.checked = cb.checked;
            });
        });

        document.getElementById('fabuCheckAll').addEventListener('click', function() {
            wrap.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
        });

        document.getElementById('fabuUncheckAll').addEventListener('click', function() {
            wrap.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        });
    })();

    function submitBulkFolderAccess() {
        let form = $('#bulkFolderAccessForm');
        let saveBtn = $('#fabuSaveBtn');
        let originalHtml = saveBtn.html();

        saveBtn.prop('disabled', true).html('<span class="ue-btn-spinner"></span>Saving…');

        $.ajax({
            url: "{{ route('folder-access.bulkUpdateUsers') }}",
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success(res) {
                if (res.status === 'success') {
                    showToast(res.message, 'success');
                    closeModal();
                    if (typeof clearUserSelection === 'function') clearUserSelection();
                    $('#users-table').DataTable().ajax.reload(null, false);
                } else {
                    showToast(res.message || 'Something went wrong', 'danger');
                    saveBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error(xhr) {
                let msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                showToast(msg, 'danger');
                saveBtn.prop('disabled', false).html(originalHtml);
            }
        });
    }
</script>

@foreach ($nodes as $node)
    <div class="fa-node" style="--depth: {{ $depth }}" data-depth="{{ $depth }}">
        <div class="fa-node-row">
            @if ($node->children->isNotEmpty())
                <button type="button" class="fa-toggle" aria-label="Toggle">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @else
                <span class="fa-toggle-spacer"></span>
            @endif

            @php
                // Same extension → icon/color mapping as the Drive page's
                // getItemIcon(), so a file looks the same here as it does
                // everywhere else in the app.
                $isFile = ($node->type ?? 'folder') === 'file';
                $ext = $isFile ? strtolower(pathinfo($node->name ?? '', PATHINFO_EXTENSION)) : '';
                [$faIcon, $iconCls] = match (true) {
                    !$isFile => ['fa-folder', 'fb-folder-icon'],
                    $ext === 'pdf' => ['fa-file-pdf', 'fb-file-pdf-icon'],
                    in_array($ext, ['doc', 'docx']) => ['fa-file-word', 'fb-file-word-icon'],
                    in_array($ext, ['xls', 'xlsx', 'csv']) => ['fa-file-excel', 'fb-file-excel-icon'],
                    in_array($ext, ['ppt', 'pptx']) => ['fa-file-powerpoint', 'fb-file-ppt-icon'],
                    in_array($ext, ['zip', 'rar', '7z']) => ['fa-file-zipper', 'fb-file-zip-icon'],
                    in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) => ['fa-file-image', 'fb-file-img-icon'],
                    default => ['fa-file', 'fb-file-icon'],
                };
            @endphp
            <label class="fa-check-label">
                <input type="checkbox" name="folder_ids[]" value="{{ $node->id }}"
                    {{ in_array($node->id, $grantedFolderIds) ? 'checked' : '' }}>
                <i class="fa-solid {{ $faIcon }} fa-node-icon {{ $iconCls }}"></i>
                <span class="fa-node-name">{{ $node->name }}</span>
            </label>
        </div>

        @if ($node->children->isNotEmpty())
            <div class="fa-children">
                @include('admin.folder_access._tree', [
                    'nodes' => $node->children,
                    'grantedFolderIds' => $grantedFolderIds,
                    'depth' => $depth + 1,
                ])
            </div>
        @endif
    </div>
@endforeach

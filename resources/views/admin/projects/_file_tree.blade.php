@php
    $depth = $depth ?? 0;
    $visibleNodes = $nodes->sortBy([['type', 'desc'], ['name', 'asc']]);
@endphp

@foreach ($visibleNodes as $node)
    @php
        $hasChildren = $node->type === 'folder' && $node->childrenRecursive->isNotEmpty();
    @endphp
    <div class="pft-node{{ $hasChildren ? ' has-children' : '' }}">
        <div class="pft-item">
            @if ($hasChildren)
                <button type="button" class="pft-toggle" aria-expanded="false">
                    <i class="fa fa-chevron-right"></i>
                </button>
            @else
                <span class="pft-spacer"></span>
            @endif

            @if ($node->type === 'file')
                @php
                    $ext = strtolower(pathinfo($node->name, PATHINFO_EXTENSION));
                    $fileIcon = match (true) {
                        in_array($ext, ['doc', 'docx']) => ['fa-file-word', '#2563eb'],
                        in_array($ext, ['xls', 'xlsx']) => ['fa-file-excel', '#16a34a'],
                        in_array($ext, ['ppt', 'pptx']) => ['fa-file-powerpoint', '#ea580c'],
                        $ext === 'pdf' => ['fa-file-pdf', '#dc2626'],
                        in_array($ext, ['png', 'jpg', 'jpeg', 'gif']) => ['fa-file-image', '#7c3aed'],
                        in_array($ext, ['zip', 'rar']) => ['fa-file-zipper', '#b45309'],
                        default => ['fa-file', '#94a3b8'],
                    };
                @endphp
                <i class="fa-solid {{ $fileIcon[0] }} pft-file-icon" style="color:{{ $fileIcon[1] }};"></i>
                <a href="{{ route('files.preview', base64_encode($node->id)) }}"
                    class="pft-name pft-name-link" title="{{ $node->name }}">{{ $node->name }}</a>
            @else
                <i class="fa fa-folder pft-folder-icon"></i>
                <span class="pft-name">{{ $node->name }}</span>
            @endif

            <span class="pft-size">{{ $node->totalSizeFormatted() }}</span>

            {{-- Always the last flex child so it stays anchored to the row's
                 right edge, regardless of whether a badge (variable width,
                 not always present) precedes it — keeps the icons in a
                 straight column across every row. --}}
            @if ($node->type === 'folder')
                <span class="pft-row-actions">
                    <button type="button" class="pft-row-action-btn" data-row-add-folder="{{ $node->id }}" title="Add sub-folder here">
                        <i class="fa-solid fa-folder-plus"></i>
                    </button>
                    <button type="button" class="pft-row-action-btn" data-row-add-file="{{ $node->id }}" title="Add file here">
                        <i class="fa-solid fa-file-circle-plus"></i>
                    </button>
                </span>
            @endif
        </div>

        @if ($hasChildren)
            <div class="pft-children">
                @include('admin.projects._file_tree', [
                    'nodes' => $node->childrenRecursive,
                    'depth' => $depth + 1,
                ])
            </div>
        @endif
    </div>
@endforeach

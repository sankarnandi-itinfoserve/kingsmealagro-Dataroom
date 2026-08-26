<?php

namespace App\Traits;

use App\Models\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait FormatsActivityLogTrait
{
    /**
     * Each log's description, HTML-safe, with the item's name (the trailing
     * "quoted label" describe() always ends with) turned into a link to that
     * item — only for Folder-model entries where the file/folder still
     * exists (a deleted item has nowhere useful to link to). Files go
     * straight to their preview page; folders jump to their drive location.
     *
     * Also builds a breadcrumb path string per log (e.g. "Shared Folders /
     * Test / file.docx") — withTrashed() so a deleted item's location is
     * still shown even though it no longer has a live link.
     */
    private function buildLinkedDescriptions(Collection $logs): ?array
    {
        try {
            $folderIds = $logs
                ->where('model_type', Folder::class)
                ->pluck('model_id')
                ->filter()
                ->unique();

            $folders = $folderIds->isEmpty() ? collect() : Folder::withTrashed()->whereIn('id', $folderIds)->get()->keyBy('id');

            $descriptions = [];
            $paths = [];
            foreach ($logs as $log) {
                $url = null;
                $isFileItem = false;
                [$icon, $color] = ['fa-file', '#64748b'];

                if ($log->model_type === Folder::class && $log->model_id) {
                    $item = $folders->get($log->model_id);
                    if ($item) {
                        $isFileItem = true;
                        $paths[$log->id] = collect($item->getBreadcrumb())->pluck('name')->implode(' / ');
                        [$icon, $color] = $this->fileIcon($item);

                        if (!$item->trashed()) {
                            $url = $item->type === 'file'
                                ? route('files.preview', base64_encode($item->id))
                                : route('shared.folders') . (($path = collect($item->getBreadcrumb())->pluck('id')->implode(',')) ? '#path=' . $path : '');
                        }
                    }
                }

                $descriptions[$log->id] = $this->linkify($log->description, $url, $icon, $color, $isFileItem);
            }

            return [$descriptions, $paths];
        } catch (\Exception $e) {
            Log::error('FormatsActivityLogTrait::buildLinkedDescriptions failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Same icon/color-per-extension convention used on the preview and drive
     * pages — real file-type colors read as far more polished here than one
     * flat generic icon for every attachment.
     */
    private function fileIcon(Folder $item): ?array
    {
        try {
            if ($item->type !== 'file') {
                return ['fa-folder', '#d97706'];
            }

            $ext = strtolower(pathinfo($item->name ?? '', PATHINFO_EXTENSION));

            return match ($ext) {
                'pdf' => ['fa-file-pdf', '#dc2626'],
                'doc', 'docx' => ['fa-file-word', '#2563eb'],
                'xls', 'xlsx' => ['fa-file-excel', '#16a34a'],
                'ppt', 'pptx' => ['fa-file-powerpoint', '#ea580c'],
                'png', 'jpg', 'jpeg', 'gif' => ['fa-file-image', '#7c3aed'],
                'zip', 'rar' => ['fa-file-zipper', '#b45309'],
                'txt' => ['fa-file-lines', '#64748b'],
                default => ['fa-file', '#64748b'],
            };
        } catch (\Exception $e) {
            Log::error('FormatsActivityLogTrait::fileIcon failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * The trailing "quoted label" describe() always ends with becomes a chip
     * (colored file-type icon + name) for Folder-model entries only — every
     * other model (Company, Role, etc.) keeps the plain quoted text, since a
     * file-type icon has no meaning there. Clickable (<a>) when the item
     * still exists; a plain non-clickable chip (still showing its real
     * extension icon) when it's been deleted, since there's nowhere to link.
     */
    private function linkify(string $description, ?string $url, string $icon, string $color, bool $isFileItem): ?string
    {
        try {
            if (!$isFileItem || !preg_match('/^(.*)"([^"]+)"(\s*)$/s', $description, $m)) {
                return e($description);
            }

            [, $prefix, $label, $trailing] = $m;

            $inner = '<i class="fa-solid ' . e($icon) . '" style="color:' . e($color) . ';"></i>' . e($label);

            $chip = $url
                ? '<a href="' . e($url) . '" target="_blank" rel="noopener" class="al-desc-link">' . $inner . '</a>'
                : '<span class="al-desc-link al-desc-link-inactive">' . $inner . '</span>';

            return e($prefix) . $chip . e($trailing);
        } catch (\Exception $e) {
            Log::error('FormatsActivityLogTrait::linkify failed: ' . $e->getMessage());
            return null;
        }
    }
}

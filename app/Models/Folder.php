<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * parent_item_id is a plain self-referential FK holding the parent
     * Folder row's own primary key. A NULL parent_item_id means the row is a
     * root-level project folder.
     */
    protected $fillable = [
        'name', 'parent_item_id', 'type', 'created_by', 'size',
    ];

    protected $casts = [];

    public function parent()
    {
        try {
            return $this->belongsTo(Folder::class, 'parent_item_id');
        } catch (\Exception $e) {
            Log::error('Folder::parent failed: ' . $e->getMessage());
            return null;
        }
    }

    public function children()
    {
        try {
            return $this->hasMany(Folder::class, 'parent_item_id');
        } catch (\Exception $e) {
            Log::error('Folder::children failed: ' . $e->getMessage());
            return null;
        }
    }

    public function creator()
    {
        try {
            return $this->belongsTo(User::class, 'created_by');
        } catch (\Exception $e) {
            Log::error('Folder::creator failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Relative path (on the 'public' disk) to this file's bytes on local
     * disk. Deterministic from the row's own id + its name's extension —
     * folders have no physical location (null), only files do.
     */
    public function localDiskPath(): ?string
    {
        try {
            if ($this->type !== 'file') {
                return null;
            }

            $ext = pathinfo($this->name, PATHINFO_EXTENSION);

            return 'project_folders/' . $this->id . ($ext !== '' ? '.' . $ext : '');
        } catch (\Exception $e) {
            Log::error('Folder::localDiskPath failed: ' . $e->getMessage());
            return null;
        }
    }

    public function favorites()
    {
        try {
            return $this->hasMany(Favorite::class);
        } catch (\Exception $e) {
            Log::error('Folder::favorites failed: ' . $e->getMessage());
            return null;
        }
    }

    public function accessGrants()
    {
        try {
            return $this->hasMany(FolderAccess::class);
        } catch (\Exception $e) {
            Log::error('Folder::accessGrants failed: ' . $e->getMessage());
            return null;
        }
    }

    // 🔥 Recursive tree
    // public function childrenRecursive()
    // {
    //     return $this->children()->with('childrenRecursive');
    // }

    // 🔥 Breadcrumb
    public function getBreadcrumb()
    {
        try {
            $breadcrumb = [];
            $folder     = $this;
            $visited    = [];

            while ($folder) {
                if (in_array($folder->id, $visited)) break; // prevent infinite loop
                $visited[] = $folder->id;
                array_unshift($breadcrumb, $folder);

                // withTrashed so the chain still resolves for a soft-deleted
                // folder whose ancestors were cascade-deleted along with it.
                $folder = $folder->parent_item_id
                    ? static::withTrashed()->find($folder->parent_item_id)
                    : null;
            }

            return $breadcrumb;
        } catch (\Exception $e) {
            Log::error('Folder::getBreadcrumb failed: ' . $e->getMessage());
            return null;
        }
    }

    public function getTotalSize()
    {
        try {
            $folderSize = (int) ($this->size ?? 0);

            foreach ($this->children as $child) {
                $folderSize += $child->getTotalSize();
            }

            return $folderSize;
        } catch (\Exception $e) {
            Log::error('Folder::getTotalSize failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Same recursive total as getTotalSize(), but reads whichever children
     * relation is already eager-loaded (childrenRecursive, if present) —
     * for callers walking an already in-memory tree, so this doesn't
     * trigger a fresh query per node.
     */
    public function totalSizeBytes(): int
    {
        try {
            $bytes = (int) ($this->size ?? 0);
            $children = $this->relationLoaded('childrenRecursive') ? $this->childrenRecursive : $this->children;

            foreach ($children as $child) {
                $bytes += $child->totalSizeBytes();
            }

            return $bytes;
        } catch (\Exception $e) {
            Log::error('Folder::totalSizeBytes failed: ' . $e->getMessage());
            return 0;
        }
    }

    public function totalSizeFormatted(): string
    {
        try {
            $bytes = $this->totalSizeBytes();
            if ($bytes <= 0) {
                return '0 KB';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $power = (int) floor(log($bytes, 1024));
            $power = max(0, min($power, count($units) - 1));
            $value = $bytes / (1024 ** $power);

            return $power === 0
                ? number_format($value, 0) . ' ' . $units[$power]
                : number_format($value, 1) . ' ' . $units[$power];
        } catch (\Exception $e) {
            Log::error('Folder::totalSizeFormatted failed: ' . $e->getMessage());
            return '0 KB';
        }
    }
    public function shares()
    {
        try {
            return $this->hasMany(FolderShare::class);
        } catch (\Exception $e) {
            Log::error('Folder::shares failed: ' . $e->getMessage());
            return null;
        }
    }
    public function isSharedWith($email)
    {
        try {
            return $this->shares()->where('email', $email)->exists();
        } catch (\Exception $e) {
            Log::error('Folder::isSharedWith failed: ' . $e->getMessage());
            return null;
        }
    }
    public function canAccess()
    {
        try {
            return $this->creator_id === auth()->id() ||
                $this->shares()->where('email', auth()->user()->email)->exists();
        } catch (\Exception $e) {
            Log::error('Folder::canAccess failed: ' . $e->getMessage());
            return null;
        }
    }
    public function childrenRecursive()
    {
        try {
            return $this->hasMany(Folder::class, 'parent_item_id')
                ->with('childrenRecursive');
        } catch (\Exception $e) {
            Log::error('Folder::childrenRecursive failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Return a flat Collection of all descendant folders/files (recursive)
     * Usage: $folder->descendants();
     */
    public function descendants()
    {
        try {
            $all = collect();

            foreach ($this->children as $child) {
                $all->push($child);
                $all = $all->merge($child->descendants());
            }

            return $all;
        } catch (\Exception $e) {
            Log::error('Folder::descendants failed: ' . $e->getMessage());
            return null;
        }
    }
}

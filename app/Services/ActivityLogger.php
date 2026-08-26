<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Models that never get an activity-log row, even though they're
     * ordinary Eloquent models that fire created/updated/deleted events.
     * These are internal bookkeeping / high-frequency / already-logged-
     * elsewhere records, not something an admin reading "what happened in
     * the system" cares about — logging them would just bury the real
     * activity in noise.
     */
    private const EXCLUDED_MODELS = [
        \App\Models\ActivityLog::class,      // avoid logging our own writes (infinite loop)
        \App\Models\RecentFile::class,       // view-count tracker, updated on every file open
        \App\Models\Favorite::class,          // trivial UI toggle, not an audited activity
    ];

    public static function handle(string $action, Model $model): void
    {
        try {
            $class = get_class($model);

            if (in_array($class, self::EXCLUDED_MODELS, true)) {
                return;
            }

            // A UserAuthLog row IS a login — surface it as its own action rather
            // than the generic "created" so it gets its own badge/filter, same
            // treatment as SoftDeletes::restore() getting its own 'restored'.
            if ($model instanceof \App\Models\UserAuthLog && $action === 'created') {
                $action = 'login';
            }

            // Deleting a soft-deleted row a second time (or other edge cases)
            // can leave $model without a primary key; nothing useful to log.
            if ($model->getKey() === null) {
                return;
            }

            $properties = self::properties($action, $model);

            // Saves that only touch excluded bookkeeping fields (e.g. Auth::logout()
            // cycling remember_token) leave an empty diff — nothing an admin reading
            // the log would call an "activity", so skip rather than log noise.
            if ($action === 'updated' && empty($properties)) {
                return;
            }

            // SoftDeletes::restore() saves deleted_at=null (firing this 'updated'
            // event) and then fires its own dedicated 'restored' event — without
            // this guard every restore would log twice.
            if (
                $action === 'updated' &&
                count($properties) === 1 &&
                array_key_exists('deleted_at', $properties) &&
                array_key_exists('new', $properties['deleted_at']) &&
                $properties['deleted_at']['new'] === null
            ) {
                return;
            }

            $user = self::actorFor($model);

            ActivityLog::create([
                'user_id'     => $user?->id,
                'user_name'   => self::actorName($user),
                'action'      => $action,
                'model_type'  => $class,
                'model_id'    => $model->getKey(),
                'description' => self::describe($action, $model, $user),
                'properties'  => $properties,
                'ip_address'  => request()?->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('ActivityLogger::handle failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout has no model/row of its own (Auth::logout() is a pure session
     * operation) — nothing to hang an eloquent.* event off of, so this is
     * called directly from an Illuminate\Auth\Events\Logout listener instead.
     */
    public static function logLogout(?\App\Models\User $user): void
    {
        try {
            if (!$user) {
                return;
            }

            ActivityLog::create([
                'user_id'     => $user->id,
                'user_name'   => self::actorName($user),
                'action'      => 'logout',
                'model_type'  => \App\Models\UserAuthLog::class,
                'model_id'    => null,
                'description' => self::actorName($user) . ' logged out',
                'properties'  => [],
                'ip_address'  => request()?->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('ActivityLogger::logLogout failed: ' . $e->getMessage());
        }
    }

    /**
     * A password change's own eloquent 'updated' event always ends up with an
     * empty properties diff — properties() deliberately strips the password
     * field itself, since old/new hashes have no business being logged — and
     * an empty diff is normally treated as no-op noise and skipped entirely
     * (see the empty-$properties guard in handle()). So this is called
     * explicitly from PasswordController (self-service change) and the
     * PasswordReset event listener (forgot-password flow) instead of relying
     * on the generic eloquent listener.
     */
    public static function logPasswordChanged(?\App\Models\User $user, string $via): void
    {
        try {
            if (!$user) {
                return;
            }

            $actor = self::actorName($user);
            $description = $via === 'reset'
                ? "{$actor} reset their password via forgot password"
                : "{$actor} changed their password";

            ActivityLog::create([
                'user_id'     => $user->id,
                'user_name'   => $actor,
                'action'      => 'password_changed',
                'model_type'  => \App\Models\User::class,
                'model_id'    => $user->id,
                'description' => $description,
                'properties'  => [],
                'ip_address'  => request()?->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('ActivityLogger::logPasswordChanged failed: ' . $e->getMessage());
        }
    }

    /**
     * A download has no model mutation to hang an eloquent.* event off of
     * (it's a pure read), so this is called explicitly from wherever a file
     * or folder actually gets sent to the browser — single-file download and
     * the bulk zip download.
     */
    public static function logDownload(?\App\Models\User $user, Folder $item): void
    {
        try {
            if (!$user) {
                return;
            }

            $actor = self::actorName($user);
            $kind  = $item->type === 'file' ? 'file' : 'folder';

            ActivityLog::create([
                'user_id'     => $user->id,
                'user_name'   => $actor,
                'action'      => 'downloaded',
                'model_type'  => Folder::class,
                'model_id'    => $item->id,
                'description' => "{$actor} downloaded {$kind} \"{$item->name}\"",
                'properties'  => [],
                'ip_address'  => request()?->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('ActivityLogger::logDownload failed: ' . $e->getMessage());
        }
    }

    /**
     * Who to attribute this activity to. Normally the signed-in session user —
     * but a UserAuthLog row for the Azure/MFA flow is created BEFORE
     * Auth::login() runs, so auth()->user() would still be null there. That
     * row's own user_id is always correct regardless of session state.
     */
    private static function actorFor(Model $model): ?\App\Models\User
    {
        try {
            if ($model instanceof \App\Models\UserAuthLog) {
                return $model->user_id ? \App\Models\User::find($model->user_id) : null;
            }

            return auth()->user();
        } catch (\Exception $e) {
            Log::error('ActivityLogger::actorFor failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function actorName(?\App\Models\User $user): ?string
    {
        try {
            if (!$user) {
                return 'System';
            }

            $name = trim(($user->fname ?? '') . ' ' . ($user->lname ?? ''));

            return $name !== '' ? $name : ($user->displayName ?? $user->email ?? 'Unknown user');
        } catch (\Exception $e) {
            Log::error('ActivityLogger::actorName failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pick a human-readable label for the affected record — the first
     * attribute present that identifies it to a person reading the log.
     */
    private static function label(Model $model): ?string
    {
        try {
            foreach (['name', 'title', 'displayName', 'item_name', 'folder_name', 'email', 'code'] as $attr) {
                if (!empty($model->{$attr})) {
                    return (string) $model->{$attr};
                }
            }

            return '#' . $model->getKey();
        } catch (\Exception $e) {
            Log::error('ActivityLogger::label failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function verb(string $action, Model $model): ?string
    {
        try {
            if ($model instanceof Folder) {
                return match ($action) {
                    'created'  => $model->type === 'file' ? 'uploaded' : 'created',
                    'updated'  => 'updated',
                    'deleted'  => 'deleted',
                    'restored' => 'restored',
                    default    => $action,
                };
            }

            return match ($action) {
                'created'  => 'created',
                'updated'  => 'updated',
                'deleted'  => 'deleted',
                'restored' => 'restored',
                default    => $action,
            };
        } catch (\Exception $e) {
            Log::error('ActivityLogger::verb failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function describe(string $action, Model $model, ?\App\Models\User $user): ?string
    {
        try {
            $actor = self::actorName($user);

            if ($model instanceof \App\Models\UserAuthLog) {
                $via = $model->logon_type ? " via {$model->logon_type}" : '';
                return "{$actor} logged in{$via}";
            }

            $parts = explode('\\', get_class($model));
            $modelLabel = end($parts) ?: 'item';

            $verb  = self::verb($action, $model);
            $label = self::label($model);

            return "{$actor} {$verb} {$modelLabel} \"{$label}\"";
        } catch (\Exception $e) {
            Log::error('ActivityLogger::describe failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * A snapshot of what changed — the dirty attributes for updates (with
     * old + new values), or the key attributes for create/delete.
     */
    private static function properties(string $action, Model $model): ?array
    {
        try {
            if ($action === 'updated' || $action === 'restored') {
                $changes = [];
                foreach ($model->getChanges() as $key => $new) {
                    if (in_array($key, ['id', 'user_id', 'updated_at', 'password', 'remember_token', 'device_token'], true)) {
                        continue;
                    }
                    $changes[$key] = [
                        'old' => $model->getOriginal($key),
                        'new' => $new,
                    ];
                }
                return $changes;
            }

            return collect($model->getAttributes())
                ->except(['id', 'user_id', 'password', 'remember_token', 'device_token'])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ActivityLogger::properties failed: ' . $e->getMessage());
            return null;
        }
    }
}

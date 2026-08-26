<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        try {
            //
        } catch (\Exception $e) {
            Log::error('AppServiceProvider::register failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
        // System-wide activity log — Eloquent fires these framework events
        // for every model's create/update/delete, so this one listener
        // covers folders, files, users, roles, templates, companies, etc.
        // without hand-instrumenting every controller action.
        foreach (['created', 'updated', 'deleted', 'restored'] as $action) {
            Event::listen("eloquent.{$action}: *", function ($eventName, array $data) use ($action) {
                $model = $data[0] ?? null;
                if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                    ActivityLogger::handle($action, $model);
                }
            });
        }

        // Logout has no model/row of its own to hang an eloquent.* event off
        // of — Auth::logout() is a pure session operation — so it's caught
        // via Laravel's own auth lifecycle event instead.
        Event::listen(Logout::class, function (Logout $event) {
            ActivityLogger::logLogout($event->user);
        });

        // Same reasoning as Logout above — NewPasswordController forceFills
        // password + remember_token together, so the eloquent 'updated' diff
        // ends up empty (both fields are stripped from logged properties)
        // and would otherwise be silently skipped as no-op noise.
        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            ActivityLogger::logPasswordChanged($event->user, 'reset');
        });

        } catch (\Exception $e) {
            Log::error('AppServiceProvider::boot failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Illuminate\Support\Facades\Log;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        try {
            // Telescope::night();

            $this->hideSensitiveRequestDetails();

            $isLocal = $this->app->environment('local');

            Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
                return $isLocal ||
                       $entry->isReportableException() ||
                       $entry->isFailedRequest() ||
                       $entry->isFailedJob() ||
                       $entry->isScheduledTask() ||
                       $entry->hasMonitoredTag();
            });
        } catch (\Exception $e) {
            Log::error('TelescopeServiceProvider::register failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        try {
            if ($this->app->environment('local')) {
                return;
            }

            Telescope::hideRequestParameters(['_token']);

            Telescope::hideRequestHeaders([
                'cookie',
                'x-csrf-token',
                'x-xsrf-token',
            ]);
        } catch (\Exception $e) {
            Log::error('TelescopeServiceProvider::hideSensitiveRequestDetails failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        try {
            Gate::define('viewTelescope', function (User $user) {
                return in_array($user->email, [
                    //
                ]);
            });
        } catch (\Exception $e) {
            Log::error('TelescopeServiceProvider::gate failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

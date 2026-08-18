<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($rootUrl = config('app.url')) {
            $rootUrl = rtrim($rootUrl, '/');
            URL::forceRootUrl($rootUrl);

            if (str_starts_with($rootUrl, 'https://')) {
                URL::forceScheme('https');
                config(['session.secure' => true]);
            }
        }

        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });
    }
}

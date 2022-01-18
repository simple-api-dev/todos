<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mailer', function ($app) {
            $app->configure('services');
            return $app->loadComponent('mail', 'Illuminate\Mail\MailServiceProvider', 'mailer');
        });
    }

    /**
     * Configure global rate limiter
     *
     * @return void
     */
    public function boot()
    {
        app(RateLimiter::class)->for('global', function () {
            return Limit::perMinute(20)->by(request()->ip());
        });
    }
}

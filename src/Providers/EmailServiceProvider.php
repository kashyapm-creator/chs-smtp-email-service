<?php

namespace Chs\EmailService\Providers;

use Chs\EmailService\Contracts\EmailServiceInterface;
use Chs\EmailService\Services\DynamicEmailService;
use Chs\EmailService\Services\MockEmailService;
use Chs\EmailService\Services\SendPulseEmailService;
use Chs\EmailService\Services\Smtp2GoEmailService;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/chs-email.php', 'chs-email');

        $this->app->singleton(SendPulseEmailService::class);
        $this->app->singleton(Smtp2GoEmailService::class);
        $this->app->singleton(MockEmailService::class);

        $this->app->singleton(DynamicEmailService::class);

        $this->app->bind(EmailServiceInterface::class, DynamicEmailService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/chs-email.php' => config_path('chs-email.php'),
            ], 'chs-email-config');
        }
    }
}

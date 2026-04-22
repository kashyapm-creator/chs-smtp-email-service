<?php

namespace Chs\EmailService\Providers;

use Chs\EmailService\Services\Email\DynamicEmailService;
use Chs\EmailService\Services\Email\EmailServiceInterface;
use Chs\EmailService\Services\Email\MockEmailService;
use Chs\EmailService\Services\Email\SendPulseEmailService;
use Chs\EmailService\Services\Email\Smtp2GoEmailService;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SendPulseEmailService::class);
        $this->app->singleton(Smtp2GoEmailService::class);
        $this->app->singleton(MockEmailService::class);

        $this->app->singleton(DynamicEmailService::class);

        $this->app->bind(EmailServiceInterface::class, DynamicEmailService::class);
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/email_templates.php', 'email_templates'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish Config
            $this->publishes([
                __DIR__.'/../config/email_templates.php' => config_path('email_templates.php'),
            ], 'chs-email-config');

            // Publish Provider (to app/Providers)
            $this->publishes([
                __DIR__.'/EmailServiceProvider.php' => app_path('Providers/EmailServiceProvider.php'),
            ], 'chs-email-provider');

            // Publish Services (to app/Services/Email)
            $this->publishes([
                __DIR__.'/../Services/Email' => app_path('Services/Email'),
            ], 'chs-email-services');

            // Publish Everything at once
            $this->publishes([
                __DIR__.'/../config/email_templates.php' => config_path('email_templates.php'),
                __DIR__.'/EmailServiceProvider.php' => app_path('Providers/EmailServiceProvider.php'),
                __DIR__.'/../Services/Email' => app_path('Services/Email'),
            ], 'chs-email-full');

            $this->commands([
                \Chs\EmailService\Console\Commands\InstallEmailService::class,
            ]);
        }
    }
}

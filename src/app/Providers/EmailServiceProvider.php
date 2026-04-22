<?php

namespace Chs\EmailService\app\Providers;

use Chs\EmailService\app\Services\Email\DynamicEmailService;
use Chs\EmailService\app\Services\Email\EmailServiceInterface;
use Chs\EmailService\app\Services\Email\MockEmailService;
use Chs\EmailService\app\Services\Email\SendPulseEmailService;
use Chs\EmailService\app\Services\Email\Smtp2GoEmailService;
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
            __DIR__.'/../../config/email_templates.php', 'email_templates'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/email_templates.php' => config_path('email_templates.php'),
            ], 'chs-email-config');
        }
    }
}

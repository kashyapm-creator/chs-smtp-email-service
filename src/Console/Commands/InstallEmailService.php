<?php

namespace Chs\EmailService\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallEmailService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-service:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the CHS Email Service package assets';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing CHS Email Service...');

        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'chs-email-config',
            '--force' => true,
        ]);

        $this->success('CHS Email Service installed successfully!');
        
        $this->info('You can now configure your email templates in config/email_templates.php');
    }

    /**
     * Helper to output success messages.
     */
    protected function success(string $message): void
    {
        $this->line("<info>✔</info> {$message}");
    }
}

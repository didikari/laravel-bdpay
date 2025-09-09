<?php

namespace BDPay\LaravelBDPay\Console\Commands;

use Illuminate\Console\Command;

class PublishBDPayAssets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bdpay:publish {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Publish all BDPay package assets (config, migrations, routes)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Publishing BDPay package assets...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--provider' => 'BDPay\LaravelBDPay\BDPayServiceProvider',
            '--tag' => 'bdpay-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--provider' => 'BDPay\LaravelBDPay\BDPayServiceProvider',
            '--tag' => 'bdpay-migrations',
            '--force' => $this->option('force'),
        ]);

        // Publish routes
        $this->call('vendor:publish', [
            '--provider' => 'BDPay\LaravelBDPay\BDPayServiceProvider',
            '--tag' => 'bdpay-routes',
            '--force' => $this->option('force'),
        ]);

        $this->info('BDPay assets published successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your .env file with BDPay credentials');
        $this->line('2. Run: php artisan migrate');
        $this->line('3. Add webhook routes to your routes/web.php if needed');
        $this->line('4. Start using BDPay services!');

        return Command::SUCCESS;
    }
}

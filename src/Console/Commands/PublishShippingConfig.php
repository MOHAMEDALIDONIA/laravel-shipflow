<?php

namespace Mohamedali\LaravelShipping\Console\Commands;

use Illuminate\Console\Command;
use Mohamedali\LaravelShipping\ShippingServiceProvider;

class PublishShippingConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:publish-config {--force : Overwrite existing config file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the laravel-shipping configuration file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $params = [
            '--provider' => ShippingServiceProvider::class,
            '--tag'      => 'config',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $this->info('Shipping configuration published successfully.');

        return self::SUCCESS;
    }
}

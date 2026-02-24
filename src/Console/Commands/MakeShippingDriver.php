<?php

namespace Mohamedali\LaravelShipping\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeShippingDriver extends Command
{
    protected $signature = 'make:shipping-driver {name}';
    protected $description = 'Create a new shipping driver with its payload class and register it in config';

    public function handle()
    {
        $name = $this->argument('name');
        $className = Str::studly($name);
        $snakeName = Str::snake(str_replace('Driver', '', $className));

        if (!Str::endsWith($className, 'Driver')) {
            $driverClassName = $className . 'Driver';
        } else {
            $driverClassName = $className;
            $className = str_replace('Driver', '', $className);
        }

        $payloadClassName = $className . 'Payload';

        // Paths for generated files
        $driverPath = app_path('Shipping/Drivers/' . $driverClassName . '.php');
        $payloadPath = app_path('Shipping/Payloads/' . $payloadClassName . '.php');

        // Check if files already exist
        if (File::exists($driverPath)) {
            $this->error("Driver {$driverClassName} already exists at {$driverPath}!");
            return;
        }

        if (File::exists($payloadPath)) {
            $this->error("Payload {$payloadClassName} already exists at {$payloadPath}!");
            return;
        }

        // Ensure directories exist
        File::ensureDirectoryExists(app_path('Shipping/Drivers'));
        File::ensureDirectoryExists(app_path('Shipping/Payloads'));

        // Generate Driver file
        $driverStub = File::get(__DIR__ . '/../stubs/driver.stub');
        $driverContent = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ name }}', '{{ payloadClass }}'],
            ['App\\Shipping\\Drivers', $driverClassName, $snakeName, $payloadClassName],
            $driverStub
        );
        File::put($driverPath, $driverContent);

        // Fix the payload import in driver to use App\Payloads namespace
        $driverContent = str_replace(
            'use Mohamedali\\LaravelShipping\\Payloads\\' . $payloadClassName,
            'use App\\Shipping\\Payloads\\' . $payloadClassName,
            $driverContent
        );
        File::put($driverPath, $driverContent);

        // Generate Payload file
        $payloadStub = File::get(__DIR__ . '/../stubs/payload.stub');
        $payloadContent = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ name }}'],
            ['App\\Payloads', $payloadClassName, $snakeName],
            $payloadStub
        );
        File::put($payloadPath, $payloadContent);

        // Auto-register in config/shipping.php
        $this->registerInConfig($snakeName, $driverClassName);

        $this->newLine();
        $this->info("✅ Shipping driver created successfully!");
        $this->newLine();
        $this->line("  📁 Files created:");
        $this->line("     Driver:  <comment>{$driverPath}</comment>");
        $this->line("     Payload: <comment>{$payloadPath}</comment>");
        $this->newLine();
        $this->line("  📝 Next steps:");
        $this->line("     1. Define your payload keys and validation rules in <comment>{$payloadClassName}</comment>");
        $this->line("     2. Implement the <comment>sendRequest()</comment> method in <comment>{$driverClassName}</comment>");
        $this->line("     3. Add your API credentials to <comment>.env</comment> and <comment>config/shipping.php</comment>");
        $this->newLine();
    }

    /**
     * Register the new driver in the shipping config file.
     */
    protected function registerInConfig(string $name, string $driverClassName): void
    {
        $configPath = config_path('shipping.php');

        if (!File::exists($configPath)) {
            $this->warn("Config file not found at {$configPath}. Please add the driver manually.");
            return;
        }

        $configContent = File::get($configPath);

        // Check if already registered
        if (Str::contains($configContent, "'{$name}'")) {
            $this->warn("Driver '{$name}' is already registered in config/shipping.php");
            return;
        }

        // Build the new provider entry
        $newProvider = "\n        '{$name}' => [\n" .
            "            'driver' => \\App\\Shipping\\Drivers\\{$driverClassName}::class,\n" .
            "            'base_url' => env('" . Str::upper($name) . "_BASE_URL'),\n" .
            "            'api_key' => env('" . Str::upper($name) . "_API_KEY'),\n" .
            "        ],";

        // Insert before the closing bracket of 'providers' array
        // Find the last ],  before the final ];
        $lastProviderPos = strrpos($configContent, '],');
        if ($lastProviderPos !== false) {
            // Find the next ], after the last provider block
            $insertPos = $lastProviderPos + 2;
            $configContent = substr($configContent, 0, $insertPos) . $newProvider . substr($configContent, $insertPos);
            File::put($configPath, $configContent);
            $this->info("📌 Driver registered in config/shipping.php under providers.{$name}");
        } else {
            $this->warn("Could not auto-register driver in config. Please add it manually.");
        }
    }
}

<?php

declare(strict_types=1);

namespace Jean\AwsKms;

use Illuminate\Support\ServiceProvider;

class AwsKmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Add configuration with prefix kakao-channel
        $this->mergeConfigFrom(__DIR__ . '/../config/aws-kms.php', 'aws-kms');
    }

    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/aws-kms.php' => config_path('aws-kms.php'),
        ], 'config');

        // Publish database
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
        
        $this->publishes([
            __DIR__ . '/../database/seeders/' => database_path('seeders'),
        ], 'seeders');
    }
}

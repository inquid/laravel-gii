<?php

namespace Inquid\LaravelGii;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Inquid\LaravelGii\Commands\LaravelGiiCommand;

class LaravelGiiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-gii')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_gii_table')
            ->hasCommand(LaravelGiiCommand::class);
    }
}

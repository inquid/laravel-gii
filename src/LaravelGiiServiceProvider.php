<?php

namespace Inquid\LaravelGii;

use Inquid\LaravelGii\Commands\OrionTypescriptCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasCommand(OrionTypescriptCommand::class);
    }
}

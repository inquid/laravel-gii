<?php

namespace Inquid\LaravelGii\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Inquid\LaravelGii\LaravelGii
 */
class LaravelGii extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Inquid\LaravelGii\LaravelGii::class;
    }
}

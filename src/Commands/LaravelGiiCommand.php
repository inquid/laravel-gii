<?php

namespace Inquid\LaravelGii\Commands;

use Illuminate\Console\Command;

class LaravelGiiCommand extends Command
{
    public $signature = 'laravel-gii';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

<?php

namespace LaravelApproval\LaravelApproval\Commands;

use Illuminate\Console\Command;

class LaravelApprovalCommand extends Command
{
    public $signature = 'laravel-approval';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

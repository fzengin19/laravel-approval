<?php

namespace FatihZengin\LaravelApproval\Commands;

use Illuminate\Console\Command;

class ApprovalCommand extends Command
{
    public $signature = 'approval:setup';

    public $description = 'Onay sistemi kurulum komutu';

    public function handle(): int
    {
        $this->comment('Laravel Approval paketi kuruldu!');

        return self::SUCCESS;
    }
}

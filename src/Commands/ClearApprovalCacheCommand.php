<?php

namespace LaravelApproval\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearApprovalCacheCommand extends Command
{
    protected $signature = 'approval:clear-cache {--discovery : Clear only model discovery cache}';

    protected $description = 'Clear approval status cache and model discovery cache';

    public function handle(): int
    {
        $discovery = $this->option('discovery');

        // Clear model discovery cache
        if ($discovery) {
            Cache::forget('approval_models_discovered');
            $this->info('✅ Model discovery cache cleared');
            return self::SUCCESS;
        }

        // Clear all approval cache
        Cache::flush();
        $this->info('✅ All cache cleared successfully');

        return self::SUCCESS;
    }
} 
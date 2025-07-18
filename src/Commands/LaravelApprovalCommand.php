<?php

namespace LaravelApproval\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaravelApprovalCommand extends Command
{
    protected $signature = 'approval:status {--model= : Specific model class to check}';

    protected $description = 'Show approval system status and statistics';

    public function handle(): int
    {
        $this->info('Laravel Approval Package Status');
        $this->line('');

        // Check configuration
        $this->checkConfiguration();

        // Check database
        $this->checkDatabase();

        // Show statistics
        $this->showStatistics();

        return self::SUCCESS;
    }

    protected function checkConfiguration(): void
    {
        $this->info('📋 Configuration Check:');
        
        $config = Config::get('approval');
        if (!$config) {
            $this->error('❌ Approval configuration not found!');
            return;
        }

        $this->line('✅ Configuration file loaded');
        
        $models = $config['models'] ?? [];
        $this->line("📊 Configured models: " . count($models));
        
        foreach ($models as $modelClass => $settings) {
            if ($modelClass === 'default') {
                $this->line("   - Default settings");
                continue;
            }
            
            $column = $settings['column'] ?? 'none';
            $this->line("   - {$modelClass} (column: {$column})");
        }
        
        $this->line('');
    }

    protected function checkDatabase(): void
    {
        $this->info('🗄️ Database Check:');
        
        // Check approvals table
        if (Schema::hasTable('approvals')) {
            $this->line('✅ Approvals table exists');
            
            $count = DB::table('approvals')->count();
            $this->line("📊 Total approval records: {$count}");
            
            // Show status distribution
            $statuses = DB::table('approvals')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();
                
            foreach ($statuses as $status) {
                $this->line("   - {$status->status}: {$status->count}");
            }
        } else {
            $this->warn('⚠️ Approvals table not found. Run migrations first.');
        }
        
        $this->line('');
    }

    protected function showStatistics(): void
    {
        $this->info('📈 Statistics:');
        
        $models = Config::get('approval.models', []);
        
        foreach ($models as $modelClass => $settings) {
            if ($modelClass === 'default' || !class_exists($modelClass)) {
                continue;
            }
            
            try {
                $pendingCount = $modelClass::pending()->count();
                $approvedCount = $modelClass::approved()->count();
                $rejectedCount = $modelClass::rejected()->count();
                $totalCount = $modelClass::count();
                
                $this->line("📊 {$modelClass}:");
                $this->line("   - Total: {$totalCount}");
                $this->line("   - Pending: {$pendingCount}");
                $this->line("   - Approved: {$approvedCount}");
                $this->line("   - Rejected: {$rejectedCount}");
                
            } catch (\Exception $e) {
                $this->warn("⚠️ Could not get statistics for {$modelClass}: " . $e->getMessage());
            }
        }
        
        $this->line('');
    }
}

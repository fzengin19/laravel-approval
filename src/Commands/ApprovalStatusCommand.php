<?php

namespace LaravelApproval\Commands;

use Illuminate\Console\Command;
use LaravelApproval\Facades\Approval;

class ApprovalStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:status {--model= : Specific model class to show statistics for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show approval statistics for models';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->option('model');

        if ($model) {
            $this->showModelStatistics($model);
        } else {
            $this->showAllStatistics();
        }

        return self::SUCCESS;
    }

    /**
     * Show statistics for a specific model.
     */
    protected function showModelStatistics(string $modelClass): void
    {
        if (! class_exists($modelClass)) {
            $this->error("Model class '{$modelClass}' does not exist.");

            return;
        }

        $statistics = Approval::getStatistics($modelClass);

        $this->info("Approval Statistics for {$modelClass}");
        $this->newLine();

        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total', $statistics['total'], '100%'],
                ['Approved', $statistics['approved'], $statistics['approved_percentage'].'%'],
                ['Pending', $statistics['pending'], $statistics['pending_percentage'].'%'],
                ['Rejected', $statistics['rejected'], $statistics['rejected_percentage'].'%'],
            ]
        );
    }

    /**
     * Show statistics for all models.
     */
    protected function showAllStatistics(): void
    {
        $statistics = Approval::getAllStatistics();

        if (empty($statistics)) {
            $this->info('No models configured for approval statistics.');

            return;
        }

        $this->info('Approval Statistics for All Models');
        $this->newLine();

        $rows = [];
        foreach ($statistics as $modelClass => $stats) {
            $rows[] = [
                $modelClass,
                $stats['total'],
                $stats['approved'],
                $stats['pending'],
                $stats['rejected'],
                $stats['approved_percentage'].'%',
            ];
        }

        $this->table(
            ['Model', 'Total', 'Approved', 'Pending', 'Rejected', 'Approved %'],
            $rows
        );
    }
}

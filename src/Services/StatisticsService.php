<?php

namespace LaravelApproval\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Contracts\StatisticsServiceInterface;
use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;

class StatisticsService implements StatisticsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStatistics(string $modelClass): array
    {
        $total = $modelClass::count();
        $approved = $modelClass::approved()->count();
        $pending = $modelClass::pending()->count();
        $rejected = $modelClass::rejected()->count();

        return $this->formatStatisticsPayload($total, $approved, $pending, $rejected);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllStatistics(): array
    {
        $statistics = [];
        $models = config('approvals.models', []);

        foreach (array_keys($models) as $modelClass) {
            $statistics[$modelClass] = $this->getStatistics($modelClass);
        }

        return $statistics;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelStatistics(ApprovableInterface $model): array
    {
        return $this->getStatistics(get_class($model));
    }

    /**
     * {@inheritdoc}
     */
    public function getApprovalPercentage(string $modelClass): float
    {
        return $this->getStatistics($modelClass)['approved_percentage'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionPercentage(string $modelClass): float
    {
        return $this->getStatistics($modelClass)['rejected_percentage'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingPercentage(string $modelClass): float
    {
        return $this->getStatistics($modelClass)['pending_percentage'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailedStatistics(string $modelClass): array
    {
        if ($modelClass::whereHas('approvals')->count() === 0) {
            return [];
        }

        $basicStats = $this->getStatistics($modelClass);

        $latestApprovals = $modelClass::with('latestApproval')
            ->whereHas('latestApproval')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $rejectionReasons = Approval::where('approvable_type', $modelClass)
            ->where('status', ApprovalStatus::REJECTED)
            ->whereNotNull('rejection_reason')
            ->selectRaw('rejection_reason, COUNT(*) as count')
            ->groupBy('rejection_reason')
            ->orderBy('count', 'desc')
            ->get();

        return array_merge($basicStats, [
            'latest_approvals' => $latestApprovals,
            'rejection_reasons' => $rejectionReasons,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatisticsForDateRange(string $modelClass, string $startDate, string $endDate): array
    {
        if (empty($startDate) || empty($endDate)) {
            throw new InvalidArgumentException('Start date and end date cannot be empty.');
        }

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid date format provided.');
        }

        $query = $modelClass::whereBetween('created_at', [$start, $end]);

        $total = (clone $query)->count();
        $approved = (clone $query)->approved()->count();
        $pending = (clone $query)->pending()->count();
        $rejected = (clone $query)->rejected()->count();

        return array_merge($this->formatStatisticsPayload($total, $approved, $pending, $rejected), [
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    /**
     * Format the statistics into a consistent array payload.
     */
    private function formatStatisticsPayload(int $total, int $approved, int $pending, int $rejected): array
    {
        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'approved_percentage' => $this->calculatePercentage($approved, $total),
            'pending_percentage' => $this->calculatePercentage($pending, $total),
            'rejected_percentage' => $this->calculatePercentage($rejected, $total),
        ];
    }

    /**
     * Calculate the percentage of a value out of a total.
     */
    private function calculatePercentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($value / $total) * 100, 2);
    }
}

<?php

namespace LaravelApproval\Services;

use LaravelApproval\Contracts\StatisticsServiceInterface;
use LaravelApproval\Contracts\ApprovableInterface;

class ApprovalService
{
    public function __construct(
        protected StatisticsServiceInterface $statisticsService
    ) {
    }

    /**
     * Approve a model.
     */
    public function approve(ApprovableInterface $model, ?int $userId = null, ?string $comment = null): void
    {
        $model->approve($userId, $comment);
    }

    /**
     * Reject a model.
     */
    public function reject(ApprovableInterface $model, ?int $userId = null, ?string $reason = null, ?string $comment = null): void
    {
        $model->reject($userId, $reason, $comment);
    }

    /**
     * Set a model to pending.
     */
    public function setPending(ApprovableInterface $model, ?int $userId = null, ?string $comment = null): void
    {
        $model->setPending($userId, $comment);
    }

    /**
     * Get approval statistics for a model class.
     */
    public function getStatistics(string $modelClass): array
    {
        return $this->statisticsService->getStatistics($modelClass);
    }

    /**
     * Get approval statistics for all models.
     */
    public function getAllStatistics(): array
    {
        return $this->statisticsService->getAllStatistics();
    }

    /**
     * Get approval statistics for a specific model instance.
     */
    public function getModelStatistics(ApprovableInterface $model): array
    {
        return $this->statisticsService->getModelStatistics($model);
    }

    /**
     * Get approval percentage for a model class.
     */
    public function getApprovalPercentage(string $modelClass): float
    {
        return $this->statisticsService->getApprovalPercentage($modelClass);
    }

    /**
     * Get rejection percentage for a model class.
     */
    public function getRejectionPercentage(string $modelClass): float
    {
        return $this->statisticsService->getRejectionPercentage($modelClass);
    }

    /**
     * Get pending percentage for a model class.
     */
    public function getPendingPercentage(string $modelClass): float
    {
        return $this->statisticsService->getPendingPercentage($modelClass);
    }

    /**
     * Get detailed statistics with additional information.
     */
    public function getDetailedStatistics(string $modelClass): array
    {
        return $this->statisticsService->getDetailedStatistics($modelClass);
    }

    /**
     * Get statistics for a date range.
     */
    public function getStatisticsForDateRange(string $modelClass, string $startDate, string $endDate): array
    {
        return $this->statisticsService->getStatisticsForDateRange($modelClass, $startDate, $endDate);
    }
}

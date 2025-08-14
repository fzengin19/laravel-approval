<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Contracts;

use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;

interface StatisticsServiceInterface
{
    /**
     * Get comprehensive statistics for a model class.
     */
    public function getStatistics(string $modelClass): array;

    /**
     * Get statistics for all configured model classes.
     */
    public function getAllStatistics(): array;

    /**
     * Get count of records by status for a model class.
     */
    public function getCountByStatus(string $modelClass, ApprovalStatus $status): int;

    /**
     * Get total count of records for a model class.
     */
    public function getTotalCount(string $modelClass): int;

    /**
     * Get percentage by status for a model class.
     */
    public function getPercentageByStatus(string $modelClass, ApprovalStatus $status): float;

    /**
     * Get approved percentage for a model class.
     */
    public function getApprovedPercentage(string $modelClass): float;

    /**
     * Get pending percentage for a model class.
     */
    public function getPendingPercentage(string $modelClass): float;

    /**
     * Get rejected percentage for a model class.
     */
    public function getRejectedPercentage(string $modelClass): float;
}

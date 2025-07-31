<?php

namespace LaravelApproval\Contracts;

interface StatisticsServiceInterface
{
    /**
     * Get basic approval statistics for a specific model class.
     *
     * @param string $modelClass The fully qualified class name of the model.
     * @return array An array with keys 'approved', 'rejected', 'pending', and 'total'.
     */
    public function getStatistics(string $modelClass): array;

    /**
     * Get aggregated approval statistics for all configured models.
     *
     * @return array An array containing statistics for each model.
     */
    public function getAllStatistics(): array;

    /**
     * Get approval statistics for a specific model instance.
     *
     * @param ApprovableInterface $model The model instance.
     * @return array An array with keys 'approved', 'rejected', 'pending', and 'total'.
     */
    public function getModelStatistics(ApprovableInterface $model): array;

    /**
     * Get the approval percentage for a model class.
     *
     * @param string $modelClass The fully qualified class name of the model.
     * @return float The percentage of approved models.
     */
    public function getApprovalPercentage(string $modelClass): float;

    /**
     * Get the rejection percentage for a model class.
     *
     * @param string $modelClass The fully qualified class name of the model.
     * @return float The percentage of rejected models.
     */
    public function getRejectionPercentage(string $modelClass): float;

    /**
     * Get the pending percentage for a model class.
     *
     * @param string $modelClass The fully qualified class name of the model.
     * @return float The percentage of pending models.
     */
    public function getPendingPercentage(string $modelClass): float;

    /**
     * Get detailed statistics with additional information like percentages.
     *
     * @param string $modelClass The fully qualified class name of the model.
     * @return array Detailed statistics including counts and percentages.
     */
    public function getDetailedStatistics(string $modelClass): array;

    /**
     * Get statistics for a specific model class within a given date range.
     *
     * @param string $modelClass The fully qualified class name of the model.
     * @param string $startDate The start date in 'Y-m-d' format.
     * @param string $endDate The end date in 'Y-m-d' format.
     * @return array Statistics for the given date range.
     */
    public function getStatisticsForDateRange(string $modelClass, string $startDate, string $endDate): array;
} 
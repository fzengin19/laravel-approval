<?php

namespace LaravelApproval\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void approve(\LaravelApproval\Contracts\ApprovableInterface $model, ?int $userId = null, ?string $comment = null)
 * @method static void reject(\LaravelApproval\Contracts\ApprovableInterface $model, ?int $userId = null, ?string $reason = null, ?string $comment = null)
 * @method static void setPending(\LaravelApproval\Contracts\ApprovableInterface $model, ?int $userId = null, ?string $comment = null)
 * @method static array getStatistics(string $modelClass)
 * @method static array getAllStatistics()
 * @method static array getModelStatistics(\LaravelApproval\Contracts\ApprovableInterface $model)
 * @method static float getApprovalPercentage(string $modelClass)
 * @method static float getRejectionPercentage(string $modelClass)
 * @method static float getPendingPercentage(string $modelClass)
 * @method static array getDetailedStatistics(string $modelClass)
 * @method static array getStatisticsForDateRange(string $modelClass, string $startDate, string $endDate)
 *
 * @see \LaravelApproval\Services\ApprovalService
 */
class Approval extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-approval';
    }
}

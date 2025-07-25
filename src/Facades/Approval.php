<?php

namespace LaravelApproval\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelApproval\Models\Approval approve(\Illuminate\Database\Eloquent\Model $model, ?int $causedBy = null)
 * @method static \LaravelApproval\Models\Approval reject(\Illuminate\Database\Eloquent\Model $model, ?int $causedBy = null, ?string $reason = null, ?string $comment = null)
 * @method static \LaravelApproval\Models\Approval setPending(\Illuminate\Database\Eloquent\Model $model, ?int $causedBy = null)
 * @method static array getStatistics(string $modelClass)
 * @method static array getAllStatistics()
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

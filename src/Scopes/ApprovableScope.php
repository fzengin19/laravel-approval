<?php

namespace LaravelApproval\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $modelClass = get_class($model);
        $modelsConfig = config('approvals.models', []);

        // Check if this model has specific configuration
        $showOnlyApproved = false;
        if (isset($modelsConfig[$modelClass]['show_only_approved_by_default'])) {
            $showOnlyApproved = $modelsConfig[$modelClass]['show_only_approved_by_default'];
        } else {
            $showOnlyApproved = config('approvals.default.show_only_approved_by_default', false);
        }

        if ($showOnlyApproved) {
            $builder->whereHas('latestApproval', function ($query) {
                $query->where('status', \LaravelApproval\Models\Approval::STATUS_APPROVED);
            });
        }
    }
}

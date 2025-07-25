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
        if (config('approvals.default.show_only_approved_by_default', false)) {
            $builder->whereHas('latestApproval', function ($query) {
                $query->where('status', 'approved');
            });
        }
    }
}

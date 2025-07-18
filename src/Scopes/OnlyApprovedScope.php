<?php

namespace LaravelApproval\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OnlyApprovedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // This scope will be applied by the ConfigurableScope
        // based on the model's configuration
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     */
    public function remove(Builder $builder, Model $model): void
    {
        // Remove any approval-related constraints
        // This is a placeholder for future implementation
    }
} 
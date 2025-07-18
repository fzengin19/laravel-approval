<?php

namespace LaravelApproval\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Config;

class ConfigurableScope implements Scope
{
    /**
     * The configuration for this scope.
     */
    protected array $config;

    /**
     * Create a new scope instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope if configured to show only approved by default
        if (!($this->config['show_only_approved_by_default'] ?? false)) {
            return;
        }

        // Check if model uses column-based approval
        if (!empty($this->config['column'])) {
            $this->applyColumnScope($builder, $model);
        } else {
            $this->applyPivotScope($builder, $model);
        }
    }

    /**
     * Apply scope for column-based approval.
     */
    protected function applyColumnScope(Builder $builder, Model $model): void
    {
        $column = $this->config['column'];

        if ($column === 'approved_at') {
            $builder->whereNotNull($column);
        } elseif ($column === 'is_approved') {
            $builder->where($column, true);
        } elseif ($column === 'approval_status') {
            $builder->where($column, 'approved');
        }
    }

    /**
     * Apply scope for pivot-based approval.
     */
    protected function applyPivotScope(Builder $builder, Model $model): void
    {
        $builder->whereHas('approval', function ($query) {
            $query->where('status', 'approved');
        });
    }

    /**
     * Get the configuration for this scope.
     */
    public function getConfig(): array
    {
        return $this->config;
    }
} 
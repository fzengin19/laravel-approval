<?php

namespace LaravelApproval\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use LaravelApproval\Enums\ApprovalStatus;

class ApprovableScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['withUnapproved', 'onlyUnapproved', 'onlyApproved'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($this->shouldApplyScope($model)) {
            $unauditedStatus = $model->getApprovalConfig('default_status_for_unaudited');

            $builder->where(function (Builder $q) use ($unauditedStatus) {
                $q->whereHas('latestApproval', function (Builder $subQuery) {
                    $subQuery->where('status', ApprovalStatus::APPROVED);
                });

                if ($unauditedStatus === ApprovalStatus::APPROVED->value) {
                    $q->orWhereDoesntHave('approvals');
                }
            });
        }
    }

    /**
     * Extend the query builder with the given extensions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the with-unapproved extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithUnapproved(Builder $builder)
    {
        $builder->macro('withUnapproved', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the only-unapproved extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyUnapproved(Builder $builder)
    {
        $builder->macro('onlyUnapproved', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)->where(function (Builder $query) {
                $query->whereHas('latestApproval', function (Builder $query) {
                    $query->where('status', '!=', ApprovalStatus::APPROVED->value);
                })->orWhereDoesntHave('latestApproval');
            });
        });
    }

    /**
     * Add the only-approved extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyApproved(Builder $builder)
    {
        $builder->macro('onlyApproved', function (Builder $builder) {
            return $builder->withUnapproved()->whereHas('latestApproval', function (Builder $query) {
                $query->where('status', ApprovalStatus::APPROVED);
            });
        });
    }

    /**
     * Check if the scope should be applied.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    protected function shouldApplyScope(Model $model): bool
    {
        if (method_exists($model, 'getApprovalConfig')) {
            return $model->getApprovalConfig('show_only_approved_by_default', false);
        }
        return false;
    }
}

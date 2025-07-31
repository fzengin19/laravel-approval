<?php

namespace LaravelApproval\Traits;

use LaravelApproval\Scopes\ApprovableScope;

trait Approvable
{
    use HasApprovals, ApprovalScopes, ApprovalActions;

    /**
     * Boot the trait.
     */
    protected static function bootApprovable()
    {
        // Auto scope
        if (config('approvals.default.auto_scope', true)) {
            static::addGlobalScope(new ApprovableScope);
        }

        // Auto pending on create
        static::created(function ($model) {
            if ($model->getApprovalConfig('auto_pending_on_create', false)) {
                $model->setPending();
            }
        });
    }
}

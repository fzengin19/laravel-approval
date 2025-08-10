<?php

namespace LaravelApproval\Traits;

use LaravelApproval\Core\ApprovalManager;

trait ApprovalActions
{
    /**
     * Approve the model.
     *
     *
     * @throws \LaravelApproval\Exceptions\InvalidApprovalStatusException
     * @throws \LaravelApproval\Exceptions\UnauthorizedApprovalException
     */
    public function approve(?int $userId = null, ?string $comment = null): void
    {
        app(ApprovalManager::class)->approve($this, $userId, $comment);
    }

    /**
     * Reject the model.
     *
     *
     * @throws \LaravelApproval\Exceptions\InvalidApprovalStatusException
     * @throws \LaravelApproval\Exceptions\UnauthorizedApprovalException
     */
    public function reject(?int $userId = null, ?string $reason = null, ?string $comment = null): void
    {
        app(ApprovalManager::class)->reject($this, $userId, $reason, $comment);
    }

    /**
     * Set the model to pending status.
     *
     *
     * @throws \LaravelApproval\Exceptions\InvalidApprovalStatusException
     * @throws \LaravelApproval\Exceptions\UnauthorizedApprovalException
     */
    public function setPending(?int $userId = null, ?string $comment = null): void
    {
        app(ApprovalManager::class)->setPending($this, $userId, $comment);
    }

    /**
     * Get a specific approval configuration value for the model.
     *
     * @param  mixed|null  $default
     * @return mixed
     */
    public function getApprovalConfig(string $key, $default = null)
    {
        $modelClass = static::class;

        return config("approvals.models.{$modelClass}.{$key}", config("approvals.default.{$key}", $default));
    }
}

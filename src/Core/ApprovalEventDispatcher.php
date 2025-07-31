<?php

namespace LaravelApproval\Core;

use Illuminate\Support\Facades\Event;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelApproving;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelRejecting;
use LaravelApproval\Events\ModelSettingPending;
use LaravelApproval\Models\Approval;

class ApprovalEventDispatcher
{
    public function dispatchApproved(ApprovableInterface $model, Approval $approval, ?int $userId, ?string $comment): void
    {
        if ($this->eventsEnabled($model)) {
            Event::dispatch(new ModelApproved($model, $approval, $userId, $comment));
        }
    }

    public function dispatchApproving(ApprovableInterface $model, ?int $userId, ?string $comment): void
    {
        if ($this->eventsEnabled($model)) {
            Event::dispatch(new ModelApproving($model, $userId, $comment));
        }
    }

    public function dispatchRejected(ApprovableInterface $model, Approval $approval, ?int $userId, ?string $reason, ?string $comment): void
    {
        if ($this->eventsEnabled($model)) {
            Event::dispatch(new ModelRejected($model, $approval, $userId, $reason, $comment));
        }
    }

    public function dispatchRejecting(ApprovableInterface $model, ?int $userId, ?string $reason, ?string $comment): void
    {
        if ($this->eventsEnabled($model)) {
            Event::dispatch(new ModelRejecting($model, $userId, $reason, $comment));
        }
    }

    public function dispatchPending(ApprovableInterface $model, Approval $approval, ?int $userId, ?string $comment): void
    {
        if ($this->eventsEnabled($model)) {
            Event::dispatch(new ModelPending($model, $approval, $userId, $comment));
        }
    }

    public function dispatchSettingPending(ApprovableInterface $model, Approval $approval, ?int $userId, ?string $comment): void
    {
        if ($this->eventsEnabled($model)) {
            Event::dispatch(new ModelSettingPending($model, $approval, $userId, $comment));
        }
    }

    protected function eventsEnabled(ApprovableInterface $model): bool
    {
        if (method_exists($model, 'getApprovalConfig')) {
            return $model->getApprovalConfig('events_enabled', true);
        }

        return config('approvals.default.events_enabled', true);
    }
} 

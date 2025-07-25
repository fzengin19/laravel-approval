<?php

namespace LaravelApproval\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Models\Approval;
use LaravelApproval\Scopes\ApprovableScope;

trait Approvable
{
    /**
     * Get all approvals for the model.
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /**
     * Get the latest approval for the model.
     */
    public function latestApproval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->latestOfMany();
    }

    /**
     * Check if the model is approved.
     */
    public function isApproved(): bool
    {
        return $this->latestApproval?->status === \LaravelApproval\Models\Approval::STATUS_APPROVED;
    }

    /**
     * Check if the model is pending.
     */
    public function isPending(): bool
    {
        return $this->latestApproval?->status === \LaravelApproval\Models\Approval::STATUS_PENDING;
    }

    /**
     * Check if the model is rejected.
     */
    public function isRejected(): bool
    {
        return $this->latestApproval?->status === \LaravelApproval\Models\Approval::STATUS_REJECTED;
    }

    /**
     * Get the current approval status.
     */
    public function getApprovalStatus(): ?string
    {
        return $this->latestApproval?->status;
    }

    /**
     * Get approval configuration for this model.
     */
    protected function getApprovalConfig(string $key, $default = null)
    {
        $modelClass = get_class($this);
        $modelsConfig = config('approvals.models', []);

        // Check if this model has specific configuration
        if (isset($modelsConfig[$modelClass][$key])) {
            return $modelsConfig[$modelClass][$key];
        }

        // Fall back to default configuration
        return config("approvals.default.{$key}", $default);
    }

    /**
     * Set the model to pending status.
     */
    public function setPending(?int $causedBy = null): Approval
    {
        $mode = $this->getApprovalConfig('mode', 'insert');
        $causedBy = $causedBy ?? Auth::id();

        if ($mode === 'upsert') {
            $approval = $this->approvals()->updateOrCreate(
                [],
                [
                    'status' => \LaravelApproval\Models\Approval::STATUS_PENDING,
                    'caused_by' => $causedBy,
                    'responded_at' => now(),
                ]
            );
        } else {
            $approval = $this->approvals()->create([
                'status' => \LaravelApproval\Models\Approval::STATUS_PENDING,
                'caused_by' => $causedBy,
                'responded_at' => now(),
            ]);
        }

        if ($this->getApprovalConfig('events', true)) {
            Event::dispatch(new ModelPending($this, $approval));
        }

        return $approval;
    }

    /**
     * Approve the model.
     */
    public function approve(?int $causedBy = null): Approval
    {
        $mode = $this->getApprovalConfig('mode', 'insert');
        $causedBy = $causedBy ?? Auth::id();

        if ($mode === 'upsert') {
            $approval = $this->approvals()->updateOrCreate(
                [],
                [
                    'status' => \LaravelApproval\Models\Approval::STATUS_APPROVED,
                    'caused_by' => $causedBy,
                    'responded_at' => now(),
                ]
            );
        } else {
            $approval = $this->approvals()->create([
                'status' => \LaravelApproval\Models\Approval::STATUS_APPROVED,
                'caused_by' => $causedBy,
                'responded_at' => now(),
            ]);
        }

        if ($this->getApprovalConfig('events', true)) {
            Event::dispatch(new ModelApproved($this, $approval));
        }

        return $approval;
    }

    /**
     * Reject the model.
     *
     * Smart rejection handling:
     * - If $reason is a predefined key from config, use it as rejection_reason
     * - If $reason is not a predefined key, use 'other' as rejection_reason and $reason as rejection_comment
     * - If $comment is provided, it will be used as additional rejection_comment
     */
    public function reject(?int $causedBy = null, ?string $reason = null, ?string $comment = null): Approval
    {
        $mode = $this->getApprovalConfig('mode', 'insert');
        $causedBy = $causedBy ?? Auth::id();

        // Smart rejection reason handling
        $rejectionReasons = config('approvals.rejection_reasons', []);
        $finalReason = null;
        $finalComment = $comment;

        if ($reason !== null) {
            // Check if reason is a predefined key
            if (array_key_exists($reason, $rejectionReasons)) {
                // It's a predefined reason, use it as rejection_reason
                $finalReason = $reason;
            } else {
                // It's a custom reason, use 'other' as rejection_reason and the reason as comment
                $finalReason = 'other';
                $finalComment = $reason.($comment ? ' - '.$comment : '');
            }
        }

        if ($mode === 'upsert') {
            $approval = $this->approvals()->updateOrCreate(
                [],
                [
                    'status' => \LaravelApproval\Models\Approval::STATUS_REJECTED,
                    'rejection_reason' => $finalReason,
                    'rejection_comment' => $finalComment,
                    'caused_by' => $causedBy,
                    'responded_at' => now(),
                ]
            );
        } else {
            $approval = $this->approvals()->create([
                'status' => \LaravelApproval\Models\Approval::STATUS_REJECTED,
                'rejection_reason' => $finalReason,
                'rejection_comment' => $finalComment,
                'caused_by' => $causedBy,
                'responded_at' => now(),
            ]);
        }

        if ($this->getApprovalConfig('events', true)) {
            Event::dispatch(new ModelRejected($this, $approval));
        }

        return $approval;
    }

    /**
     * Scope a query to only include approved models.
     */
    public function scopeApproved($query)
    {
        return $query->whereHas('latestApproval', function ($q) {
            $q->where('status', \LaravelApproval\Models\Approval::STATUS_APPROVED);
        });
    }

    /**
     * Scope a query to only include pending models.
     */
    public function scopePending($query)
    {
        return $query->whereHas('latestApproval', function ($q) {
            $q->where('status', \LaravelApproval\Models\Approval::STATUS_PENDING);
        });
    }

    /**
     * Scope a query to only include rejected models.
     */
    public function scopeRejected($query)
    {
        return $query->whereHas('latestApproval', function ($q) {
            $q->where('status', \LaravelApproval\Models\Approval::STATUS_REJECTED);
        });
    }

    /**
     * Scope a query to include models with approval status.
     */
    public function scopeWithApprovalStatus($query)
    {
        return $query->with('latestApproval');
    }

    /**
     * Scope a query to include unapproved models.
     */
    public function scopeWithUnapproved($query)
    {
        return $query->withoutGlobalScope(ApprovableScope::class);
    }

    /**
     * Boot the trait.
     */
    protected static function bootApprovable()
    {
        // Only add global scope if auto_scope is enabled
        if ((new static)->getApprovalConfig('auto_scope', true)) {
            static::addGlobalScope(new ApprovableScope);
        }

        static::created(function ($model) {
            if ($model->getApprovalConfig('auto_pending_on_create', false)) {
                $model->setPending();
            }
        });
    }
}

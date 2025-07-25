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

trait HasApprovals
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
        return $this->latestApproval?->status === 'approved';
    }

    /**
     * Check if the model is pending.
     */
    public function isPending(): bool
    {
        return $this->latestApproval?->status === 'pending';
    }

    /**
     * Check if the model is rejected.
     */
    public function isRejected(): bool
    {
        return $this->latestApproval?->status === 'rejected';
    }

    /**
     * Get the current approval status.
     */
    public function getApprovalStatus(): ?string
    {
        return $this->latestApproval?->status;
    }

    /**
     * Set the model to pending status.
     */
    public function setPending(?int $causedBy = null): Approval
    {
        $mode = config('approvals.default.mode', 'insert');
        $causedBy = $causedBy ?? Auth::id();

        if ($mode === 'upsert') {
            return $this->approvals()->updateOrCreate(
                [],
                [
                    'status' => 'pending',
                    'caused_by' => $causedBy,
                    'responded_at' => now(),
                ]
            );
        }

        $approval = $this->approvals()->create([
            'status' => 'pending',
            'caused_by' => $causedBy,
            'responded_at' => now(),
        ]);

        if (config('approvals.default.events', true)) {
            Event::dispatch(new ModelPending($this, $approval));
        }

        return $approval;
    }

    /**
     * Approve the model.
     */
    public function approve(?int $causedBy = null): Approval
    {
        $mode = config('approvals.default.mode', 'insert');
        $causedBy = $causedBy ?? Auth::id();

        if ($mode === 'upsert') {
            return $this->approvals()->updateOrCreate(
                [],
                [
                    'status' => 'approved',
                    'caused_by' => $causedBy,
                    'responded_at' => now(),
                ]
            );
        }

        $approval = $this->approvals()->create([
            'status' => 'approved',
            'caused_by' => $causedBy,
            'responded_at' => now(),
        ]);

        if (config('approvals.default.events', true)) {
            Event::dispatch(new ModelApproved($this, $approval));
        }

        return $approval;
    }

    /**
     * Reject the model.
     */
    public function reject(?int $causedBy = null, ?string $reason = null, ?string $comment = null): Approval
    {
        $mode = config('approvals.default.mode', 'insert');
        $causedBy = $causedBy ?? Auth::id();

        if ($mode === 'upsert') {
            return $this->approvals()->updateOrCreate(
                [],
                [
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'rejection_comment' => $comment,
                    'caused_by' => $causedBy,
                    'responded_at' => now(),
                ]
            );
        }

        $approval = $this->approvals()->create([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejection_comment' => $comment,
            'caused_by' => $causedBy,
            'responded_at' => now(),
        ]);

        if (config('approvals.default.events', true)) {
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
            $q->where('status', 'approved');
        });
    }

    /**
     * Scope a query to only include pending models.
     */
    public function scopePending($query)
    {
        return $query->whereHas('latestApproval', function ($q) {
            $q->where('status', 'pending');
        });
    }

    /**
     * Scope a query to only include rejected models.
     */
    public function scopeRejected($query)
    {
        return $query->whereHas('latestApproval', function ($q) {
            $q->where('status', 'rejected');
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
    protected static function bootHasApprovals()
    {
        if (config('approvals.default.auto_scope', true)) {
            static::addGlobalScope(new ApprovableScope);
        }

        if (config('approvals.default.auto_pending_on_create', false)) {
            static::created(function ($model) {
                $model->setPending();
            });
        }
    }
}

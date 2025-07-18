<?php

namespace LaravelApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'approvals';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'approved_at' => 'datetime',
        'approved_by' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'approved_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the model that owns the approval.
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who approved/rejected the model.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'approved_by');
    }

    /**
     * Scope to get only approved records.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get only pending records.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only rejected records.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the approval is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the approval is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the approval is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the approval status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get the rejection reason.
     */
    public function getRejectionReason(): ?string
    {
        return $this->rejection_reason;
    }

    /**
     * Get the approver user.
     */
    public function getApprover()
    {
        return $this->approver;
    }

    /**
     * Get the approval date.
     */
    public function getApprovedAt(): ?\Carbon\Carbon
    {
        return $this->approved_at;
    }
} 
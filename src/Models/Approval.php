<?php

namespace LaravelApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'rejection_reason',
        'rejection_comment',
        'caused_by',
        'responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the parent approvable model.
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved approvals.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected approvals.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}

<?php

namespace LaravelApproval\Traits;

use LaravelApproval\Models\Approval;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Scopes\OnlyApprovedScope;
use LaravelApproval\Scopes\ConfigurableScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

trait HasApproval
{
    /**
     * Cached approval configuration
     */
    protected static $cachedConfigs = [];

    /**
     * Boot the trait and add global scopes if configured.
     */
    protected static function bootHasApproval(): void
    {
        $config = static::getCachedApprovalConfig();
        
        if ($config['auto_scope'] ?? true) {
            static::addGlobalScope(new ConfigurableScope($config));
        }
    }

    /**
     * Get cached approval configuration
     */
    protected static function getCachedApprovalConfig(): array
    {
        $modelClass = static::class;
        
        if (!isset(static::$cachedConfigs[$modelClass])) {
            static::$cachedConfigs[$modelClass] = Config::get('approval.models.' . $modelClass, Config::get('approval.models.default'));
        }
        
        return static::$cachedConfigs[$modelClass];
    }

    /**
     * Check rate limiting for approval actions.
     */
    protected function checkRateLimit(string $action): void
    {
        if (!Config::get('approval.rate_limiting.enabled', false)) {
            return;
        }

        $key = 'approval_' . $action . '_' . (Auth::id() ?? 'guest');
        $maxAttempts = Config::get('approval.rate_limiting.max_attempts', 60);
        $decayMinutes = Config::get('approval.rate_limiting.decay_minutes', 1);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new ThrottleRequestsException('Too many approval attempts. Please try again later.');
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Get the approval configuration for this model.
     */
    public function getApprovalConfig(): array
    {
        return static::getCachedApprovalConfig();
    }

    /**
     * Check if this model uses column-based approval.
     */
    public function usesApprovalColumn(): bool
    {
        $config = $this->getApprovalConfig();
        return !empty($config['column']) && $this->hasColumn($config['column']);
    }

    /**
     * Check if this model uses pivot table approval.
     */
    public function usesApprovalPivot(): bool
    {
        $config = $this->getApprovalConfig();
        return !$this->usesApprovalColumn() && ($config['fallback_to_pivot'] ?? true);
    }

    /**
     * Get the approval relationship.
     */
    public function approval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->latestOfMany();
    }

    /**
     * Approve the model.
     */
    public function approve(?int $approvedBy = null): bool
    {
        $this->checkRateLimit('approve');
        
        $approvedBy = $approvedBy ?? Auth::id();
        
        if ($this->usesApprovalColumn()) {
            $result = $this->approveViaColumn($approvedBy ?? 0);
        }
        
        if ($this->usesApprovalPivot()) {
            $result = $this->approveViaPivot($approvedBy ?? 0);
        }
        
        $this->refresh();
        return $result;
    }

    /**
     * Reject the model.
     */
    public function reject(string $reason, ?int $rejectedBy = null): bool
    {
        $this->checkRateLimit('reject');
        
        // Validate rejection reason
        $this->validateRejectionReason($reason);
        
        $rejectedBy = $rejectedBy ?? Auth::id();
        
        if ($this->usesApprovalColumn()) {
            $result = $this->rejectViaColumn($reason, $rejectedBy ?? 0);
        }
        
        if ($this->usesApprovalPivot()) {
            $result = $this->rejectViaPivot($reason, $rejectedBy ?? 0);
        }
        
        $this->refresh();
        return $result;
    }

    /**
     * Set the model to pending status.
     */
    public function setPending(): bool
    {
        $this->checkRateLimit('pending');
        
        if ($this->usesApprovalColumn()) {
            $result = $this->setPendingViaColumn();
        }
        
        if ($this->usesApprovalPivot()) {
            $result = $this->setPendingViaPivot();
        }
        
        $this->refresh();
        return $result;
    }

    /**
     * Get cache key for approval status.
     */
    protected function getApprovalCacheKey(): string
    {
        $prefix = Config::get('approval.cache.prefix', 'approval_status_');
        return $prefix . $this->getTable() . '_' . $this->getKey();
    }

    /**
     * Clear approval status cache.
     */
    protected function clearApprovalCache(): void
    {
        if (!Config::get('approval.cache.enabled', false)) {
            return;
        }

        Cache::forget($this->getApprovalCacheKey());
    }

    /**
     * Get cached approval status.
     */
    protected function getCachedApprovalStatus(): ?string
    {
        if (!Config::get('approval.cache.enabled', false)) {
            return null;
        }

        return Cache::get($this->getApprovalCacheKey());
    }

    /**
     * Set cached approval status.
     */
    protected function setCachedApprovalStatus(string $status): void
    {
        if (!Config::get('approval.cache.enabled', false)) {
            return;
        }

        $ttl = Config::get('approval.cache.ttl', 3600);
        Cache::put($this->getApprovalCacheKey(), $status, $ttl);
    }

    /**
     * Check if the model is approved.
     */
    public function isApproved(): bool
    {
        return $this->getApprovalStatus() === 'approved';
    }

    /**
     * Check if the model is pending.
     */
    public function isPending(): bool
    {
        return $this->getApprovalStatus() === 'pending';
    }

    /**
     * Check if the model is rejected.
     */
    public function isRejected(): bool
    {
        return $this->getApprovalStatus() === 'rejected';
    }

    /**
     * Get the approval status.
     */
    public function getApprovalStatus(): string
    {
        // Check cache first
        $cachedStatus = $this->getCachedApprovalStatus();
        if ($cachedStatus !== null) {
            return $cachedStatus;
        }

        // Calculate status
        $status = $this->calculateApprovalStatus();
        
        // Ensure data consistency for column-based models
        if ($this->usesApprovalColumn()) {
            $wasUpdated = $this->ensureDataConsistency($status);
            if ($wasUpdated) {
                // If consistency was fixed, clear cache and recalculate
                $this->clearApprovalCache();
                $status = $this->calculateApprovalStatus();
            }
        }
        
        // Cache the result
        $this->setCachedApprovalStatus($status);
        
        return $status;
    }

    /**
     * Calculate the approval status without caching.
     */
    protected function calculateApprovalStatus(): string
    {
        $status = 'pending';
        
        if ($this->usesApprovalColumn()) {
            $config = $this->getApprovalConfig();
            $column = $config['column'];
            
            if ($column === 'approved_at') {
                if ($this->getAttribute($column)) {
                    $status = 'approved';
                } elseif ($this->approval?->status === 'rejected') {
                    $status = 'rejected';
                } elseif ($this->approval?->status === 'approved') {
                    $status = 'approved';
                } else {
                    $status = 'pending';
                }
            } elseif ($column === 'is_approved') {
                if ($this->getAttribute($column)) {
                    $status = 'approved';
                } elseif ($this->approval?->status === 'rejected') {
                    $status = 'rejected';
                } elseif ($this->approval?->status === 'approved') {
                    $status = 'approved';
                } else {
                    $status = 'pending';
                }
            } elseif ($column === 'approval_status') {
                $columnStatus = $this->getAttribute($column);
                if ($columnStatus === 'rejected') {
                    $status = 'rejected';
                } elseif ($columnStatus === 'approved') {
                    $status = 'approved';
                } else {
                    // Check if there's a rejection record in pivot table
                    $status = $this->approval?->status === 'rejected' ? 'rejected' : 'pending';
                }
            }
        } elseif ($this->usesApprovalPivot()) {
            $approval = $this->approval;
            $status = $approval ? $approval->status : 'pending';
        }

        return $status;
    }

    /**
     * Ensure data consistency between column and pivot table.
     */
    protected function ensureDataConsistency(string $determinedStatus): bool
    {
        if (!$this->usesApprovalColumn()) {
            return false;
        }

        $config = $this->getApprovalConfig();
        $column = $config['column'];
        $columnValue = $this->getAttribute($column);
        $pivotStatus = $this->approval?->status;

        $needsUpdate = false;

        // If column and pivot are inconsistent, fix the column
        if ($column === 'approval_status') {
            if ($determinedStatus === 'rejected' && $columnValue !== 'rejected') {
                $this->update([$column => 'rejected']);
                $needsUpdate = true;
            } elseif ($determinedStatus === 'approved' && $columnValue !== 'approved') {
                $this->update([$column => 'approved']);
                $needsUpdate = true;
            } elseif ($determinedStatus === 'pending' && $columnValue !== 'pending' && $columnValue !== null) {
                $this->update([$column => 'pending']);
                $needsUpdate = true;
            }
        } elseif ($column === 'approved_at') {
            if ($determinedStatus === 'approved' && !$columnValue) {
                $this->update([$column => now()]);
                $needsUpdate = true;
            } elseif ($determinedStatus !== 'approved' && $columnValue) {
                $this->update([$column => null]);
                $needsUpdate = true;
            }
        } elseif ($column === 'is_approved') {
            if ($determinedStatus === 'approved' && !$columnValue) {
                $this->update([$column => true]);
                $needsUpdate = true;
            } elseif ($determinedStatus !== 'approved' && $columnValue) {
                $this->update([$column => false]);
                $needsUpdate = true;
            }
        }

        // If we made changes, refresh model
        if ($needsUpdate) {
            $this->refresh();
        }

        return $needsUpdate;
    }

    /**
     * Get the rejection reason.
     */
    public function getRejectionReason(): ?string
    {
        if ($this->usesApprovalPivot()) {
            return $this->approval?->rejection_reason;
        }
        
        return null;
    }

    /**
     * Scope to get only approved models.
     */
    public function scopeApproved(Builder $query): Builder
    {
        if ($this->usesApprovalColumn()) {
            return $this->scopeApprovedViaColumn($query);
        }
        
        if ($this->usesApprovalPivot()) {
            return $this->scopeApprovedViaPivot($query);
        }
        
        return $query;
    }

    /**
     * Scope to get only pending models.
     */
    public function scopePending(Builder $query): Builder
    {
        if ($this->usesApprovalColumn()) {
            return $this->scopePendingViaColumn($query);
        }
        
        if ($this->usesApprovalPivot()) {
            return $this->scopePendingViaPivot($query);
        }
        
        return $query;
    }

    /**
     * Scope to get only rejected models.
     */
    public function scopeRejected(Builder $query): Builder
    {
        if ($this->usesApprovalColumn()) {
            return $this->scopeRejectedViaColumn($query);
        }
        
        if ($this->usesApprovalPivot()) {
            return $this->scopeRejectedViaPivot($query);
        }
        
        return $query;
    }

    /**
     * Scope to include approval status in the query.
     */
    public function scopeWithApprovalStatus(Builder $query): Builder
    {
        if ($this->usesApprovalPivot()) {
            return $query->with('approval');
        }
        
        return $query;
    }

    // Column-based approval methods
    protected function approveViaColumn(int $approvedBy): bool
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approved_at') {
            $this->update([$column => now()]);
        } elseif ($column === 'is_approved') {
            $this->update([$column => true]);
        } elseif ($column === 'approval_status') {
            $this->update([$column => 'approved']);
        }
        
        // Clear cache after update
        $this->clearApprovalCache();
        // (Cache tekrar doldurulmayacak)
        
        if (Config::get('approval.models.' . static::class . '.events', true)) {
            event(new ModelApproved($this, $approvedBy, now(), 'column'));
        }
        
        $this->refresh();
        return true;
    }

    protected function rejectViaColumn(string $reason, int $rejectedBy): bool
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approval_status') {
            $this->update([$column => 'rejected']);
        } elseif ($column === 'approved_at') {
            $this->update([$column => null]);
            $this->approval()->updateOrCreate(
                [],
                [
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'approved_by' => $rejectedBy,
                    'approved_at' => now()
                ]
            );
        } elseif ($column === 'is_approved') {
            $this->update([$column => false]);
            $this->approval()->updateOrCreate(
                [],
                [
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'approved_by' => $rejectedBy,
                    'approved_at' => now()
                ]
            );
        }
        
        // Clear cache after update
        $this->clearApprovalCache();
        // (Cache tekrar doldurulmayacak)
        
        if (Config::get('approval.models.' . static::class . '.events', true)) {
            event(new ModelRejected($this, $rejectedBy, $reason, 'column'));
        }
        
        $this->refresh();
        return true;
    }

    protected function setPendingViaColumn(): bool
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approval_status') {
            $this->update([$column => 'pending']);
            // Also clear any rejection record in pivot table
            $this->approval()->updateOrCreate(
                [],
                [
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'approved_by' => null,
                    'approved_at' => null
                ]
            );
        } elseif ($column === 'approved_at') {
            $this->update([$column => null]);
            $this->approval()->updateOrCreate(
                [],
                [
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'approved_by' => null,
                    'approved_at' => null
                ]
            );
        } elseif ($column === 'is_approved') {
            $this->update([$column => null]);
            $this->approval()->updateOrCreate(
                [],
                [
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'approved_by' => null,
                    'approved_at' => null
                ]
            );
        }
        
        // Clear cache after update
        $this->clearApprovalCache();
        // (Cache tekrar doldurulmayacak)
        
        if (Config::get('approval.models.' . static::class . '.events', true)) {
            event(new ModelPending($this, 'column'));
        }
        
        $this->refresh();
        return true;
    }

    protected function isApprovedViaColumn(): bool
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approved_at') {
            return !empty($this->{$column});
        } elseif ($column === 'is_approved') {
            return (bool) $this->{$column};
        } elseif ($column === 'approval_status') {
            return $this->{$column} === 'approved';
        }
        
        return false;
    }

    protected function isPendingViaColumn(): bool
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approved_at') {
            // If approved_at is empty and no rejection record exists, it's pending
            return empty($this->{$column}) && $this->approval?->status !== 'rejected';
        } elseif ($column === 'is_approved') {
            // If is_approved is null and no rejection record exists, it's pending
            return $this->{$column} === null && $this->approval?->status !== 'rejected';
        } elseif ($column === 'approval_status') {
            return $this->{$column} === 'pending' || empty($this->{$column});
        }
        
        return true;
    }

    protected function isRejectedViaColumn(): bool
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approval_status') {
            return $this->{$column} === 'rejected';
        } elseif ($column === 'approved_at') {
            // For approved_at, we can't directly determine rejection
            // We need to check if there's a rejection record in pivot table
            return $this->approval?->status === 'rejected';
        } elseif ($column === 'is_approved') {
            // For is_approved, we can't directly determine rejection
            // We need to check if there's a rejection record in pivot table
            return $this->approval?->status === 'rejected';
        }
        
        return false;
    }

    protected function scopeApprovedViaColumn(Builder $query): Builder
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approved_at') {
            return $query->whereNotNull($column);
        } elseif ($column === 'is_approved') {
            return $query->where($column, true);
        } elseif ($column === 'approval_status') {
            return $query->where($column, 'approved');
        }
        
        return $query;
    }

    protected function scopePendingViaColumn(Builder $query): Builder
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approved_at') {
            return $query->whereNull($column)->whereDoesntHave('approval', function ($q) {
                $q->where('status', 'rejected');
            });
        } elseif ($column === 'is_approved') {
            return $query->whereNull($column)->whereDoesntHave('approval', function ($q) {
                $q->where('status', 'rejected');
            });
        } elseif ($column === 'approval_status') {
            return $query->where(function ($q) use ($column) {
                $q->where($column, 'pending')->orWhereNull($column);
            })->whereDoesntHave('approval', function ($q) {
                $q->where('status', 'rejected');
            });
        }
        
        return $query;
    }

    protected function scopeRejectedViaColumn(Builder $query): Builder
    {
        $config = $this->getApprovalConfig();
        $column = $config['column'];
        
        if ($column === 'approval_status') {
            return $query->where($column, 'rejected');
        } elseif ($column === 'approved_at' || $column === 'is_approved') {
            // For these columns, we need to check pivot table for rejection status
            return $query->whereHas('approval', function ($q) {
                $q->where('status', 'rejected');
            });
        }
        
        return $query;
    }

    // Pivot-based approval methods
    protected function approveViaPivot(int $approvedBy): bool
    {
        $approval = $this->approval()->firstOrCreate([
            'status' => 'pending'
        ]);
        
        $approval->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => null
        ]);
        
        // Clear cache after update
        $this->clearApprovalCache();
        
        if (Config::get('approval.models.' . static::class . '.events', true)) {
            event(new ModelApproved($this, $approvedBy, now(), 'pivot'));
        }
        
        $this->refresh();
        // Force reload the approval relationship after refresh
        $this->load('approval');
        return true;
    }

    protected function rejectViaPivot(string $reason, int $rejectedBy): bool
    {
        $approval = $this->approval()->firstOrCreate([
            'status' => 'pending'
        ]);
        
        $approval->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $rejectedBy,
            'approved_at' => now()
        ]);
        
        // Clear cache after update
        $this->clearApprovalCache();
        
        if (Config::get('approval.models.' . static::class . '.events', true)) {
            event(new ModelRejected($this, $rejectedBy, $reason, 'pivot'));
        }
        
        $this->refresh();
        // Force reload the approval relationship after refresh
        $this->load('approval');
        return true;
    }

    protected function setPendingViaPivot(): bool
    {
        $approval = $this->approval()->firstOrCreate([
            'status' => 'pending'
        ]);
        
        $approval->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'approved_by' => null,
            'approved_at' => null
        ]);
        
        // Clear cache after update
        $this->clearApprovalCache();
        
        if (Config::get('approval.models.' . static::class . '.events', true)) {
            event(new ModelPending($this, 'pivot'));
        }
        
        $this->refresh();
        // Force reload the approval relationship after refresh
        $this->load('approval');
        return true;
    }

    protected function isApprovedViaPivot(): bool
    {
        return $this->approval?->status === 'approved';
    }

    protected function isPendingViaPivot(): bool
    {
        return !$this->approval || $this->approval->status === 'pending';
    }

    protected function isRejectedViaPivot(): bool
    {
        return $this->approval?->status === 'rejected';
    }

    protected function scopeApprovedViaPivot(Builder $query): Builder
    {
        return $query->whereHas('approval', function ($q) {
            $q->where('status', 'approved');
        });
    }

    protected function scopePendingViaPivot(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('approval')
              ->orWhereHas('approval', function ($subQ) {
                  $subQ->where('status', 'pending');
              });
        });
    }

    protected function scopeRejectedViaPivot(Builder $query): Builder
    {
        return $query->whereHas('approval', function ($q) {
            $q->where('status', 'rejected');
        });
    }

    /**
     * Validate rejection reason.
     */
    protected function validateRejectionReason(string $reason): void
    {
        $allowedReasons = Config::get('approval.rejection_reasons', []);
        
        // If no specific reasons are configured, allow any reason
        if (empty($allowedReasons)) {
            return;
        }
        
        if (!in_array($reason, $allowedReasons)) {
            throw new \InvalidArgumentException("Invalid rejection reason. Allowed reasons: " . implode(', ', $allowedReasons));
        }
    }

    /**
     * Check if the model has a specific column.
     */
    public function hasColumn(string $column): bool
    {
        return in_array($column, $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable()));
    }
}
<?php

namespace LaravelApproval\Services;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Models\Approval;

class ApprovalService
{
    /**
     * Approve a model.
     */
    public function approve(Model $model, ?int $causedBy = null): Approval
    {
        return $model->approve($causedBy);
    }

    /**
     * Reject a model.
     */
    public function reject(Model $model, ?int $causedBy = null, ?string $reason = null, ?string $comment = null): Approval
    {
        return $model->reject($causedBy, $reason, $comment);
    }

    /**
     * Set a model to pending.
     */
    public function setPending(Model $model, ?int $causedBy = null): Approval
    {
        return $model->setPending($causedBy);
    }

    /**
     * Get approval statistics for a model class.
     */
    public function getStatistics(string $modelClass): array
    {
        $total = $modelClass::count();
        $approved = $modelClass::approved()->count();
        $pending = $modelClass::pending()->count();
        $rejected = $modelClass::rejected()->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'approved_percentage' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'pending_percentage' => $total > 0 ? round(($pending / $total) * 100, 2) : 0,
            'rejected_percentage' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get approval statistics for all models.
     */
    public function getAllStatistics(): array
    {
        $statistics = [];
        $models = config('approvals.models', []);

        foreach ($models as $modelClass => $config) {
            $statistics[$modelClass] = $this->getStatistics($modelClass);
        }

        return $statistics;
    }
}

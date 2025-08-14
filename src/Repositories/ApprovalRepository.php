<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaravelApproval\LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;

class ApprovalRepository implements ApprovalRepositoryInterface
{
    /**
     * Create a new approval record.
     */
    public function create(array $data): Approval
    {
        // Convert enum to string if needed
        if (isset($data['status']) && $data['status'] instanceof ApprovalStatus) {
            $data['status'] = $data['status']->value;
        }

        return Approval::create($data);
    }

    /**
     * Update an existing approval record.
     */
    public function update(int $id, array $data): Approval
    {
        // Convert enum to string if needed
        if (isset($data['status']) && $data['status'] instanceof ApprovalStatus) {
            $data['status'] = $data['status']->value;
        }

        $approval = Approval::findOrFail($id);
        $approval->update($data);

        return $approval->fresh();
    }

    /**
     * Find all approval records for a model.
     */
    public function findByModel(Model $model): Collection
    {
        return Approval::query()
            ->where('approvable_type', $model->getMorphClass())
            ->where('approvable_id', $model->getKey())
            ->get();
    }

    /**
     * Find the latest approval record for a model.
     */
    public function findLatestByModel(Model $model): ?Approval
    {
        return Approval::query()
            ->where('approvable_type', $model->getMorphClass())
            ->where('approvable_id', $model->getKey())
            ->latest()
            ->first();
    }

    /**
     * Delete all approval records for a model.
     */
    public function deleteByModel(Model $model): bool
    {
        $deletedCount = Approval::query()
            ->where('approvable_type', $model->getMorphClass())
            ->where('approvable_id', $model->getKey())
            ->delete();

        return $deletedCount > 0;
    }

    /**
     * Get approval records by status.
     */
    public function getByStatus(ApprovalStatus $status, ?string $modelClass = null): Collection
    {
        $query = Approval::query()->where('status', $status->value);

        if ($modelClass !== null) {
            $query->where('approvable_type', $modelClass);
        }

        return $query->get();
    }

    /**
     * Count approval records by status.
     */
    public function countByStatus(ApprovalStatus $status, ?string $modelClass = null): int
    {
        $query = Approval::query()->where('status', $status->value);

        if ($modelClass !== null) {
            $query->where('approvable_type', $modelClass);
        }

        return $query->count();
    }

    /**
     * Check if an approval record exists for a model.
     */
    public function exists(Model $model): bool
    {
        return Approval::query()
            ->where('approvable_type', $model->getMorphClass())
            ->where('approvable_id', $model->getKey())
            ->exists();
    }
}

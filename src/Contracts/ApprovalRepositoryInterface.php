<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;

interface ApprovalRepositoryInterface
{
    /**
     * Create a new approval record.
     */
    public function create(array $data): Approval;

    /**
     * Update an existing approval record.
     */
    public function update(int $id, array $data): Approval;

    /**
     * Find all approval records for a model.
     */
    public function findByModel(Model $model): Collection;

    /**
     * Find the latest approval record for a model.
     */
    public function findLatestByModel(Model $model): ?Approval;

    /**
     * Delete all approval records for a model.
     */
    public function deleteByModel(Model $model): bool;

    /**
     * Get approval records by status.
     */
    public function getByStatus(ApprovalStatus $status, ?string $modelClass = null): Collection;

    /**
     * Count approval records by status.
     */
    public function countByStatus(ApprovalStatus $status, ?string $modelClass = null): int;

    /**
     * Check if an approval record exists for a model.
     */
    public function exists(Model $model): bool;
}

<?php

namespace LaravelApproval\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LaravelApproval\Models\Approval;

interface ApprovalRepositoryInterface
{
    /**
     * Create a new approval record for a model.
     *
     * @param  Model&ApprovableInterface  $model  The model to approve.
     * @param  array  $data  The approval data.
     * @return Approval The created approval record.
     */
    public function create(ApprovableInterface $model, array $data): Approval;

    /**
     * Update an existing approval record or create a new one.
     *
     * @param  Model&ApprovableInterface  $model  The model to approve.
     * @param  array  $data  The approval data.
     * @return Approval The created or updated approval record.
     */
    public function updateOrCreate(ApprovableInterface $model, array $data): Approval;

    /**
     * Get all approval records for a model.
     *
     * @param  Model&ApprovableInterface  $model  The model to get approvals for.
     * @return MorphMany The relation instance.
     */
    public function getAllForModel(ApprovableInterface $model): MorphMany;

    /**
     * Get the latest approval record for a model.
     *
     * @param  Model&ApprovableInterface  $model  The model to get the latest approval for.
     * @return Approval|null The latest approval record or null if none exists.
     */
    public function getLatestForModel(ApprovableInterface $model): ?Approval;

    /**
     * Delete all approval records for a model.
     *
     * @param  Model&ApprovableInterface  $model  The model to delete approvals for.
     */
    public function deleteForModel(ApprovableInterface $model): void;
}

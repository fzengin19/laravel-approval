<?php

namespace LaravelApproval\Core;

use Illuminate\Support\Arr;
use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Models\Approval;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ApprovalRepository implements ApprovalRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ApprovableInterface $model, array $data): Approval
    {
        $approval = new Approval();
        $approval->fill(Arr::except($data, ['caused_by_type', 'caused_by_id']));

        $approval->approvable()->associate($model);

        if (isset($data['caused_by_type']) && isset($data['caused_by_id'])) {
            $approval->caused_by_type = $data['caused_by_type'];
            $approval->caused_by_id = $data['caused_by_id'];
        }

        $approval->save();

        return $approval;
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrCreate(ApprovableInterface $model, array $data): Approval
    {
        $attributes = [
            'approvable_type' => $model->getMorphClass(),
            'approvable_id' => $model->getKey(),
        ];
        
        $approval = Approval::firstOrNew($attributes);
        $approval->fill(Arr::except($data, ['caused_by_type', 'caused_by_id']));
        
        if (isset($data['caused_by_type']) && isset($data['caused_by_id'])) {
            $approval->caused_by_type = $data['caused_by_type'];
            $approval->caused_by_id = $data['caused_by_id'];
        }

        $approval->save();

        return $approval;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForModel(ApprovableInterface $model): MorphMany
    {
        return $model->approvals();
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestForModel(ApprovableInterface $model): ?Approval
    {
        return $model->latestApproval()->first();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForModel(ApprovableInterface $model): void
    {
        $model->approvals()->delete();
    }
} 
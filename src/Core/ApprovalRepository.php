<?php

namespace LaravelApproval\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Models\Approval;

class ApprovalRepository implements ApprovalRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ApprovableInterface $model, array $data): Approval
    {
        $approval = new Approval;
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
        $eloquentModel = $this->asModel($model);

        return DB::transaction(function () use ($eloquentModel, $data): Approval {
            $eloquentModel->newQuery()
                ->whereKey($eloquentModel->getKey())
                ->lockForUpdate()
                ->first();

            /** @var Approval|null $approval */
            $approval = $eloquentModel->approvals()->latest('id')->first();

            if ($approval === null) {
                return $this->create($eloquentModel, $data);
            }

            $approval->fill(Arr::except($data, ['caused_by_type', 'caused_by_id']));

            if (isset($data['caused_by_type']) && isset($data['caused_by_id'])) {
                $approval->caused_by_type = $data['caused_by_type'];
                $approval->caused_by_id = $data['caused_by_id'];
            }

            $approval->save();

            return $approval;
        });
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
        /** @var Approval|null $approval */
        $approval = $model->latestApproval()->first();

        return $approval;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForModel(ApprovableInterface $model): void
    {
        $model->approvals()->delete();
    }

    private function asModel(ApprovableInterface $model): Model&ApprovableInterface
    {
        if (! $model instanceof Model) {
            throw new \InvalidArgumentException('Approvable models must extend Eloquent Model.');
        }

        return $model;
    }
}

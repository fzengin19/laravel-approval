<?php

namespace LaravelApproval\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Exceptions\ApprovalException;
use LaravelApproval\Exceptions\UnauthorizedApprovalException;
use LaravelApproval\Models\Approval;

class ApprovalManager
{
    protected ApprovalRepositoryInterface $repository;

    protected ApprovalValidatorInterface $validator;

    protected ApprovalEventDispatcher $eventDispatcher;

    public function __construct(
        ApprovalRepositoryInterface $repository,
        ApprovalValidatorInterface $validator,
        ApprovalEventDispatcher $eventDispatcher
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function approve(ApprovableInterface $model, ?int $userId = null, ?string $comment = null): void
    {
        $causer = $this->getCauser($userId);
        $causerId = $this->getCauserId($causer);

        if ($userId !== null && $causer === null) {
            throw UnauthorizedApprovalException::cannotApprove($userId);
        }

        if (! $this->validator->canApprove($model, $causerId)) {
            throw UnauthorizedApprovalException::cannotApprove($causerId);
        }

        if (! $this->validator->validateApproval($model, $causerId)) {
            throw UnauthorizedApprovalException::cannotApprove($causerId);
        }

        $this->eventDispatcher->dispatchApproving($model, $causerId, $comment);

        $data = [
            'status' => ApprovalStatus::APPROVED,
            'responded_at' => now(),
            'rejection_reason' => null,
            'rejection_comment' => $comment,
        ];

        $approval = $this->saveApproval($model, $data, $causer);
        $this->syncModelApprovalRelations($model, $approval);

        $this->eventDispatcher->dispatchApproved($model, $approval, $causerId, $comment);
    }

    public function reject(ApprovableInterface $model, ?int $userId = null, ?string $reason = null, ?string $comment = null): void
    {
        $causer = $this->getCauser($userId);
        $causerId = $this->getCauserId($causer);

        if ($userId !== null && $causer === null) {
            throw UnauthorizedApprovalException::cannotReject($userId);
        }

        if (! $this->validator->canReject($model, $causerId)) {
            throw UnauthorizedApprovalException::cannotReject($causerId);
        }

        if (! $this->validator->validateRejection($model, $causerId, $reason)) {
            throw UnauthorizedApprovalException::cannotReject($causerId);
        }

        $rejectionData = $this->prepareRejectionData($model, $reason, $comment);

        $this->eventDispatcher->dispatchRejecting($model, $causerId, $rejectionData['rejection_reason'], $rejectionData['rejection_comment']);

        $data = [
            'status' => ApprovalStatus::REJECTED,
            'responded_at' => now(),
            ...$rejectionData,
        ];

        $approval = $this->saveApproval($model, $data, $causer);
        $this->syncModelApprovalRelations($model, $approval);

        $this->eventDispatcher->dispatchRejected($model, $approval, $causerId, $rejectionData['rejection_reason'], $rejectionData['rejection_comment']);
    }

    public function setPending(ApprovableInterface $model, ?int $userId = null, ?string $comment = null): void
    {
        $causer = $this->getCauser($userId);
        $causerId = $this->getCauserId($causer);

        if ($userId !== null && $causer === null) {
            throw UnauthorizedApprovalException::cannotSetPending($userId);
        }

        if (! $this->validator->canSetPending($model, $causerId)) {
            throw UnauthorizedApprovalException::cannotSetPending($causerId);
        }

        if (! $this->validator->validatePending($model, $causerId)) {
            throw UnauthorizedApprovalException::cannotSetPending($causerId);
        }
        $data = [
            'status' => ApprovalStatus::PENDING,
            'responded_at' => now(),
            'rejection_reason' => null,
            'rejection_comment' => $comment,
        ];

        $approval = $this->saveApproval($model, $data, $causer);
        $this->syncModelApprovalRelations($model, $approval);

        $this->eventDispatcher->dispatchSettingPending($model, $approval, $causerId, $comment);

        $this->eventDispatcher->dispatchPending($model, $approval, $causerId, $comment);
    }

    private function saveApproval(ApprovableInterface $model, array $data, ?Model $causer): Approval
    {
        $eloquentModel = $this->asModel($model);
        $mode = $this->getModelConfig($model, 'mode', 'insert');

        if ($causer) {
            $data['caused_by_type'] = $causer->getMorphClass();
            $data['caused_by_id'] = (int) $causer->getKey();
        }

        if ($mode === 'upsert') {
            return $this->repository->updateOrCreate($eloquentModel, $data);
        }

        return $this->repository->create($eloquentModel, $data);
    }

    private function getCauser(?int $userId): ?Model
    {
        $userId = $userId ?? Auth::id();

        if ($userId === null) {
            return null;
        }

        $userModelClass = config('approvals.user_model');

        if ($userModelClass === null || ! class_exists($userModelClass)) {
            return null;
        }

        return $userModelClass::find($userId);
    }

    private function prepareRejectionData(ApprovableInterface $model, ?string $reason, ?string $comment): array
    {
        if ($reason === null) {
            return [
                'rejection_reason' => null,
                'rejection_comment' => $comment,
            ];
        }

        $rejectionReasons = $this->getModelConfig($model, 'rejection_reasons', []);

        if (array_key_exists($reason, $rejectionReasons)) {
            if (! $this->validator->validateRejectionReason($reason, $rejectionReasons)) {
                throw ApprovalException::invalidRejectionReason($reason);
            }

            return [
                'rejection_reason' => $reason,
                'rejection_comment' => $comment,
            ];
        }

        $finalComment = $reason;
        if ($comment) {
            $finalComment = ! empty($reason) ? "{$reason} - {$comment}" : $comment;
        }

        if (! $this->validator->validateRejectionReason('other', $rejectionReasons)) {
            throw ApprovalException::invalidRejectionReason($reason);
        }

        return [
            'rejection_reason' => 'other',
            'rejection_comment' => $finalComment,
        ];
    }

    private function getCauserId(?Model $causer): ?int
    {
        return $causer ? (int) $causer->getKey() : null;
    }

    private function syncModelApprovalRelations(ApprovableInterface $model, Approval $approval): void
    {
        if (! $model instanceof Model) {
            return;
        }

        $model->setRelation('latestApproval', $approval);
        $model->unsetRelation('approvals');
    }

    protected function getModelConfig(ApprovableInterface $model, string $key, $default = null)
    {
        return $model->getApprovalConfig($key, $default);
    }

    private function asModel(ApprovableInterface $model): Model&ApprovableInterface
    {
        if (! $model instanceof Model) {
            throw new \InvalidArgumentException('Approvable models must extend Eloquent Model.');
        }

        return $model;
    }
}

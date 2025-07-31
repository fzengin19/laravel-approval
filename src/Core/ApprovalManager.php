<?php

namespace LaravelApproval\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Enums\ApprovalStatus;
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

        if (!$this->validator->canApprove($model, $causer?->id)) {
            throw UnauthorizedApprovalException::cannotApprove($causer?->id);
        }

        if (!$this->validator->validateApproval($model, $causer?->id)) {
            throw UnauthorizedApprovalException::cannotApprove($causer?->id);
        }

        $this->eventDispatcher->dispatchApproving($model, $causer?->id, $comment);

        $data = [
            'status' => ApprovalStatus::APPROVED,
            'responded_at' => now(),
            'rejection_reason' => null,
            'rejection_comment' => $comment,
        ];

        $approval = $this->saveApproval($model, $data, $causer);

        $this->eventDispatcher->dispatchApproved($model, $approval, $causer?->id, $comment);
    }

    public function reject(ApprovableInterface $model, ?int $userId = null, ?string $reason = null, ?string $comment = null): void
    {
        $causer = $this->getCauser($userId);

        if (!$this->validator->canReject($model, $causer?->id)) {
            throw UnauthorizedApprovalException::cannotReject($causer?->id);
        }

        if (!$this->validator->validateRejection($model, $causer?->id, $reason)) {
            throw UnauthorizedApprovalException::cannotReject($causer?->id);
        }

        $rejectionData = $this->prepareRejectionData($model, $reason, $comment);

        $this->eventDispatcher->dispatchRejecting($model, $causer?->id, $rejectionData['rejection_reason'], $rejectionData['rejection_comment']);

        $data = [
            'status' => ApprovalStatus::REJECTED,
            'responded_at' => now(),
            ...$rejectionData,
        ];

        $approval = $this->saveApproval($model, $data, $causer);

        $this->eventDispatcher->dispatchRejected($model, $approval, $causer?->id, $rejectionData['rejection_reason'], $rejectionData['rejection_comment']);
    }

    public function setPending(ApprovableInterface $model, ?int $userId = null, ?string $comment = null): void
    {
        $causer = $this->getCauser($userId);

        if (!$this->validator->canSetPending($model, $causer?->id)) {
            throw UnauthorizedApprovalException::cannotSetPending($causer?->id);
        }

        if (!$this->validator->validatePending($model, $causer?->id)) {
            throw UnauthorizedApprovalException::cannotSetPending($causer?->id);
        }
        $data = [
            'status' => ApprovalStatus::PENDING,
            'responded_at' => now(),
            'rejection_reason' => null,
            'rejection_comment' => $comment,
        ];

        $approval = $this->saveApproval($model, $data, $causer);

        $this->eventDispatcher->dispatchSettingPending($model, $approval, $causer?->id, $comment);

        $this->eventDispatcher->dispatchPending($model, $approval, $causer?->id, $comment);
    }

    private function saveApproval(ApprovableInterface $model, array $data, ?Model $causer): Approval
    {
        $mode = $this->getModelConfig($model, 'mode', 'insert');

        if ($causer) {
            $data['caused_by_type'] = $causer->getMorphClass();
            $data['caused_by_id'] = $causer->id;
        }

        if ($mode === 'upsert') {
            return $this->repository->updateOrCreate($model, $data);
        }

        return $this->repository->create($model, $data);
    }

    private function getCauser(?int $userId): ?Model
    {
        $userId = $userId ?? Auth::id();

        if ($userId === null) {
            return null;
        }

        $userModelClass = config('approvals.user_model');

        if ($userModelClass === null || !class_exists($userModelClass)) {
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
        $allowCustom = $this->getModelConfig($model, 'allow_custom_reasons', false);

        if (array_key_exists($reason, $rejectionReasons)) {
            return [
                'rejection_reason' => $reason,
                'rejection_comment' => $comment,
            ];
        }

        if ($allowCustom) {
            // In the original logic, a custom reason might be saved directly.
            // Let's stick to the safer 'other' categorization for now.
        }

        $finalComment = $reason;
        if ($comment) {
            $finalComment = !empty($reason) ? "{$reason} - {$comment}" : $comment;
        }

        return [
            'rejection_reason' => 'other',
            'rejection_comment' => $finalComment,
        ];
    }

    protected function getModelConfig(ApprovableInterface $model, string $key, $default = null)
    {
        if (method_exists($model, 'getApprovalConfig')) {
            return $model->getApprovalConfig($key, $default);
        }

        $modelClass = get_class($model);
        $modelsConfig = config('approvals.models', []);

        if (isset($modelsConfig[$modelClass][$key])) {
            return $modelsConfig[$modelClass][$key];
        }

        return config("approvals.default.{$key}", $default);
    }
}

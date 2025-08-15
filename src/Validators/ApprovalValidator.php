<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Validators;

use LaravelApproval\LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Exceptions\InvalidApprovalStatusException;
use LaravelApproval\LaravelApproval\Exceptions\UnauthorizedApprovalException;

class ApprovalValidator implements ApprovalValidatorInterface
{
    public function validateApproval(ApprovableInterface $model, ?int $causerId = null): void
    {
        $this->validateCauser($causerId);
        $this->validateModelState($model);
    }

    public function validateRejection(ApprovableInterface $model, ?int $causerId = null, ?string $reason = null, ?string $comment = null): void
    {
        $this->validateCauser($causerId);
        $this->validateModelState($model);
        $this->validateRejectionComment($comment);
        $this->validateRejectionReason($model, $reason);
    }

    public function validatePending(ApprovableInterface $model, ?int $causerId = null): void
    {
        $this->validateCauser($causerId);
        $this->validateModelState($model);
    }

    public function validateStatusTransition(ApprovableInterface $model, ApprovalStatus $newStatus): void
    {
        $currentStatus = $model->getApprovalStatus();

        // If model has no current status, any status is allowed
        if ($currentStatus === null) {
            return;
        }

        // Validate business rules for status transitions
        $this->validateTransitionRules($currentStatus, $newStatus);
    }

    public function validateModelConfiguration(ApprovableInterface $model): void
    {
        $config = $model->getApprovalConfig();

        if (! is_array($config)) {
            throw new InvalidApprovalStatusException('Model approval configuration must be an array');
        }

        $this->validateModeConfiguration($config);
        $this->validateRejectionReasonsConfiguration($config);
        $this->validateEventConfiguration($config);
    }

    protected function validateCauser(?int $causerId): void
    {
        if ($causerId !== null && $causerId <= 0) {
            throw new UnauthorizedApprovalException('Causer ID must be a positive integer');
        }
    }

    protected function validateModelState(ApprovableInterface $model): void
    {
        // Cast to Model to access the exists property
        if ($model instanceof \Illuminate\Database\Eloquent\Model && ! $model->exists) {
            throw new InvalidApprovalStatusException('Model must be persisted before approval operations');
        }

        if (! $model->getKey()) {
            throw new InvalidApprovalStatusException('Model must have a valid primary key');
        }
    }

    protected function validateRejectionReason(ApprovableInterface $model, ?string $reason): void
    {
        if ($reason === null) {
            return;
        }

        if (strlen($reason) > 255) {
            throw new InvalidApprovalStatusException('Rejection reason cannot exceed 255 characters');
        }

        $config = $model->getApprovalConfig();
        $allowCustomReasons = $config['allow_custom_reasons'] ?? false;
        $predefinedReasons = array_keys($config['rejection_reasons'] ?? []);

        if (! $allowCustomReasons && ! in_array($reason, $predefinedReasons, true)) {
            throw new InvalidApprovalStatusException(
                sprintf('Rejection reason "%s" is not allowed. Allowed reasons: %s',
                    $reason,
                    implode(', ', $predefinedReasons)
                )
            );
        }
    }

    protected function validateRejectionComment(?string $comment): void
    {
        if ($comment === null) {
            return;
        }

        // TEXT field can handle large content, but we set a reasonable limit
        if (strlen($comment) > 65535) {
            throw new InvalidApprovalStatusException('Rejection comment cannot exceed 65535 characters');
        }
    }

    protected function validateTransitionRules(ApprovalStatus $currentStatus, ApprovalStatus $newStatus): void
    {
        // Business rule: Any status can transition to any other status
        // This is flexible by design, but can be customized here if needed

        // Example of stricter rules (commented out):
        // if ($currentStatus === ApprovalStatus::APPROVED && $newStatus === ApprovalStatus::PENDING) {
        //     throw new InvalidApprovalStatusException('Cannot change from approved to pending status');
        // }
    }

    protected function validateModeConfiguration(array $config): void
    {
        $mode = $config['mode'] ?? 'insert';

        if (! in_array($mode, ['insert', 'upsert'], true)) {
            throw new InvalidApprovalStatusException('Approval mode must be either "insert" or "upsert"');
        }
    }

    protected function validateRejectionReasonsConfiguration(array $config): void
    {
        $rejectionReasons = $config['rejection_reasons'] ?? [];

        if (! is_array($rejectionReasons)) {
            throw new InvalidApprovalStatusException('Rejection reasons configuration must be an array');
        }

        foreach ($rejectionReasons as $key => $value) {
            if (! is_string($key) || ! is_string($value)) {
                throw new InvalidApprovalStatusException('Rejection reasons must be key-value pairs of strings');
            }
        }
    }

    protected function validateEventConfiguration(array $config): void
    {
        $eventsEnabled = $config['events_enabled'] ?? true;

        if (! is_bool($eventsEnabled)) {
            throw new InvalidApprovalStatusException('Events enabled configuration must be a boolean');
        }

        if (isset($config['events_webhooks_enabled']) && ! is_bool($config['events_webhooks_enabled'])) {
            throw new InvalidApprovalStatusException('Webhook events configuration must be a boolean');
        }

        if (isset($config['events_webhooks_endpoints']) && ! is_array($config['events_webhooks_endpoints'])) {
            throw new InvalidApprovalStatusException('Webhook endpoints configuration must be an array');
        }
    }
}

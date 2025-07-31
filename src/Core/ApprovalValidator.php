<?php

namespace LaravelApproval\Core;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;

class ApprovalValidator implements ApprovalValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validateApproval(ApprovableInterface $model, ?int $userId = null): bool
    {
        // Default validation allows the action. Can be extended with custom logic.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateRejection(ApprovableInterface $model, ?int $userId = null, ?string $reason = null): bool
    {
        // Default validation allows the action. Can be extended with custom logic.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validatePending(ApprovableInterface $model, ?int $userId = null): bool
    {
        // Default validation allows the action. Can be extended with custom logic.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateRejectionReason(string $reason, array $allowedReasons = []): bool
    {
        if (empty($allowedReasons)) {
            return true; // No restrictions
        }

        return array_key_exists($reason, $allowedReasons);
    }

    /**
     * {@inheritdoc}
     */
    public function canApprove(ApprovableInterface $model, ?int $userId = null): bool
    {
        // Default authorization allows the action. Can be extended with custom logic.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canReject(ApprovableInterface $model, ?int $userId = null): bool
    {
        // Default authorization allows the action. Can be extended with custom logic.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canSetPending(ApprovableInterface $model, ?int $userId = null): bool
    {
        // Default authorization allows the action. Can be extended with custom logic.
        return true;
    }

    /**
     * Validate model configuration.
     */
    public function validateModelConfiguration(Model $model): bool
    {
        $modelClass = get_class($model);
        $modelsConfig = config('approvals.models', []);

        // Check if model is configured
        if (! isset($modelsConfig[$modelClass])) {
            return true; // Use defaults
        }

        $config = $modelsConfig[$modelClass];

        // Validate mode
        if (isset($config['mode']) && ! in_array($config['mode'], ['insert', 'upsert'])) {
            return false;
        }

        // Validate rejection reasons
        if (isset($config['rejection_reasons'])) {
            if (! is_array($config['rejection_reasons'])) {
                return false;
            }
            // Ensure it's an associative array [key => value]
            if (array_is_list($config['rejection_reasons'])) {
                return false;
            }
            foreach ($config['rejection_reasons'] as $key => $value) {
                if (! is_string($key) || ! is_string($value)) {
                    return false;
                }
            }
        }

        // Validate allow_custom_reasons
        if (isset($config['allow_custom_reasons']) && ! is_bool($config['allow_custom_reasons'])) {
            return false;
        }

        return true;
    }
}

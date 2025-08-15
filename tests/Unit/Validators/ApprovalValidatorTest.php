<?php

declare(strict_types=1);

use LaravelApproval\LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Exceptions\InvalidApprovalStatusException;
use LaravelApproval\LaravelApproval\Exceptions\UnauthorizedApprovalException;
use LaravelApproval\LaravelApproval\Tests\Models\Post;
use LaravelApproval\LaravelApproval\Validators\ApprovalValidator;

beforeEach(function () {
    $this->validator = new ApprovalValidator;
    $this->model = new Post(['title' => 'Test Post']);
    $this->model->save(); // Persist the model
});

describe('ApprovalValidator', function () {
    describe('validateApproval', function () {
        it('passes with valid causer and persisted model', function () {
            expect(fn () => $this->validator->validateApproval($this->model, 1))
                ->not->toThrow(Exception::class);
        });

        it('passes with null causer', function () {
            expect(fn () => $this->validator->validateApproval($this->model, null))
                ->not->toThrow(Exception::class);
        });

        it('throws exception for invalid causer ID', function () {
            expect(fn () => $this->validator->validateApproval($this->model, 0))
                ->toThrow(UnauthorizedApprovalException::class, 'Causer ID must be a positive integer');
        });

        it('throws exception for negative causer ID', function () {
            expect(fn () => $this->validator->validateApproval($this->model, -1))
                ->toThrow(UnauthorizedApprovalException::class, 'Causer ID must be a positive integer');
        });

        it('throws exception for non-persisted model', function () {
            $newModel = new Post(['title' => 'New Post']);

            expect(fn () => $this->validator->validateApproval($newModel, 1))
                ->toThrow(InvalidApprovalStatusException::class, 'Model must be persisted before approval operations');
        });
    });

    describe('validateRejection', function () {
        it('passes with valid parameters', function () {
            expect(fn () => $this->validator->validateRejection($this->model, 1, 'spam', 'This is spam'))
                ->not->toThrow(Exception::class);
        });

        it('passes with null reason and comment', function () {
            expect(fn () => $this->validator->validateRejection($this->model, 1, null, null))
                ->not->toThrow(Exception::class);
        });

        it('throws exception for reason exceeding 255 characters', function () {
            $longReason = str_repeat('a', 256);

            expect(fn () => $this->validator->validateRejection($this->model, 1, $longReason, 'comment'))
                ->toThrow(InvalidApprovalStatusException::class, 'Rejection reason cannot exceed 255 characters');
        });

        it('throws exception for comment exceeding 65535 characters', function () {
            $longComment = str_repeat('a', 65536);

            expect(fn () => $this->validator->validateRejection($this->model, 1, 'reason', $longComment))
                ->toThrow(InvalidApprovalStatusException::class, 'Rejection comment cannot exceed 65535 characters');
        });

        it('validates rejection reason against model configuration', function () {
            // Mock the model's getApprovalConfig method
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('exists')->andReturn(true);
            $model->shouldReceive('getKey')->andReturn(1);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'allow_custom_reasons' => false,
                'rejection_reasons' => [
                    'spam' => 'Spam',
                    'inappropriate' => 'Inappropriate Content',
                ],
            ]);

            // Valid predefined reason should pass
            expect(fn () => $this->validator->validateRejection($model, 1, 'spam', 'comment'))
                ->not->toThrow(Exception::class);

            // Invalid reason should fail
            expect(fn () => $this->validator->validateRejection($model, 1, 'custom_reason', 'comment'))
                ->toThrow(InvalidApprovalStatusException::class, 'Rejection reason "custom_reason" is not allowed');
        });

        it('allows custom reasons when configured', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('exists')->andReturn(true);
            $model->shouldReceive('getKey')->andReturn(1);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'allow_custom_reasons' => true,
                'rejection_reasons' => [
                    'spam' => 'Spam',
                ],
            ]);

            expect(fn () => $this->validator->validateRejection($model, 1, 'custom_reason', 'comment'))
                ->not->toThrow(Exception::class);
        });
    });

    describe('validatePending', function () {
        it('passes with valid parameters', function () {
            expect(fn () => $this->validator->validatePending($this->model, 1))
                ->not->toThrow(Exception::class);
        });

        it('throws exception for invalid causer', function () {
            expect(fn () => $this->validator->validatePending($this->model, 0))
                ->toThrow(UnauthorizedApprovalException::class, 'Causer ID must be a positive integer');
        });

        it('throws exception for non-persisted model', function () {
            $newModel = new Post(['title' => 'New Post']);

            expect(fn () => $this->validator->validatePending($newModel, 1))
                ->toThrow(InvalidApprovalStatusException::class, 'Model must be persisted before approval operations');
        });
    });

    describe('validateStatusTransition', function () {
        it('allows any transition when model has no current status', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalStatus')->andReturn(null);

            expect(fn () => $this->validator->validateStatusTransition($model, ApprovalStatus::APPROVED))
                ->not->toThrow(Exception::class);

            expect(fn () => $this->validator->validateStatusTransition($model, ApprovalStatus::PENDING))
                ->not->toThrow(Exception::class);

            expect(fn () => $this->validator->validateStatusTransition($model, ApprovalStatus::REJECTED))
                ->not->toThrow(Exception::class);
        });

        it('allows transitions between all statuses', function () {
            $model = Mockery::mock(ApprovableInterface::class);

            // Test all possible transitions
            $statuses = [ApprovalStatus::PENDING, ApprovalStatus::APPROVED, ApprovalStatus::REJECTED];

            foreach ($statuses as $currentStatus) {
                foreach ($statuses as $newStatus) {
                    $model->shouldReceive('getApprovalStatus')->andReturn($currentStatus);

                    expect(fn () => $this->validator->validateStatusTransition($model, $newStatus))
                        ->not->toThrow(Exception::class);
                }
            }
        });
    });

    describe('validateModelConfiguration', function () {
        it('passes with valid configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'mode' => 'insert',
                'events_enabled' => true,
                'rejection_reasons' => [
                    'spam' => 'Spam',
                    'inappropriate' => 'Inappropriate Content',
                ],
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->not->toThrow(Exception::class);
        });

        it('throws exception for non-array configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn('invalid');

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Model approval configuration must be an array');
        });

        it('validates mode configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'mode' => 'invalid_mode',
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Approval mode must be either "insert" or "upsert"');
        });

        it('validates rejection reasons configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'rejection_reasons' => 'invalid',
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Rejection reasons configuration must be an array');
        });

        it('validates rejection reasons format', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'rejection_reasons' => [
                    'spam' => 123, // Invalid: should be string
                ],
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Rejection reasons must be key-value pairs of strings');
        });

        it('validates event configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'events_enabled' => 'invalid', // Should be boolean
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Events enabled configuration must be a boolean');
        });

        it('validates webhook configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'events_webhooks_enabled' => 'invalid', // Should be boolean
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Webhook events configuration must be a boolean');
        });

        it('validates webhook endpoints configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'events_webhooks_endpoints' => 'invalid', // Should be array
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->toThrow(InvalidApprovalStatusException::class, 'Webhook endpoints configuration must be an array');
        });
    });

    describe('edge cases', function () {
        it('handles model with no primary key', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('exists')->andReturn(true);
            $model->shouldReceive('getKey')->andReturn(null);

            expect(fn () => $this->validator->validateApproval($model, 1))
                ->toThrow(InvalidApprovalStatusException::class, 'Model must have a valid primary key');
        });

        it('handles exactly 255 character reason', function () {
            $reason = str_repeat('a', 255);

            expect(fn () => $this->validator->validateRejection($this->model, 1, $reason, 'comment'))
                ->not->toThrow(Exception::class);
        });

        it('handles exactly 65535 character comment', function () {
            $comment = str_repeat('a', 65535);

            expect(fn () => $this->validator->validateRejection($this->model, 1, 'reason', $comment))
                ->not->toThrow(Exception::class);
        });

        it('handles empty rejection reasons configuration', function () {
            $model = Mockery::mock(ApprovableInterface::class);
            $model->shouldReceive('getApprovalConfig')->andReturn([
                'rejection_reasons' => [],
            ]);

            expect(fn () => $this->validator->validateModelConfiguration($model))
                ->not->toThrow(Exception::class);
        });
    });
});

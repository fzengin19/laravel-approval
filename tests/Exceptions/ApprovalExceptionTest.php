<?php

use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Exceptions\ApprovalException;
use LaravelApproval\Exceptions\InvalidApprovalStatusException;
use LaravelApproval\Exceptions\UnauthorizedApprovalException;

test('ApprovalException static factories create correct instances', function (string $method, array $args, string $expectedMessage) {
    /** @var ApprovalException $exception */
    $exception = ApprovalException::$method(...$args);

    expect($exception)->toBeInstanceOf(ApprovalException::class);
    expect($exception->getMessage())->toBe($expectedMessage);
})->with([
    ['invalidStatus', ['invalid'], 'Invalid approval status: invalid'],
    ['modelNotFound', ['App\Models\Post'], 'Model not found: App\Models\Post'],
    ['approvalNotFound', [123], 'Approval not found: 123'],
    ['invalidRejectionReason', ['invalid_reason'], 'Invalid rejection reason: invalid_reason'],
    ['configurationError', ['mode'], 'Configuration error for key: mode'],
]);

test('InvalidApprovalStatusException invalidTransition static method creates correct instance', function () {
    $exception = InvalidApprovalStatusException::invalidTransition('pending', 'approved');

    expect($exception)->toBeInstanceOf(InvalidApprovalStatusException::class);
    expect($exception->getMessage())->toBe('Cannot transition from pending to approved');
});

test('InvalidApprovalStatusException invalidStatus static method creates correct message', function () {
    $exception = InvalidApprovalStatusException::invalidStatus('unknown');

    $allowed = implode(', ', array_map(fn($case) => $case->value, ApprovalStatus::cases()));
    $expectedMessage = "Unknown approval status: `unknown`. Allowed statuses are: {$allowed}.";

    expect($exception)->toBeInstanceOf(InvalidApprovalStatusException::class);
    expect($exception->getMessage())->toBe($expectedMessage);
});


test('UnauthorizedApprovalException static methods create correct instances', function () {
    $cannotApprove = UnauthorizedApprovalException::cannotApprove(1);
    expect($cannotApprove)->toBeInstanceOf(UnauthorizedApprovalException::class)
        ->and($cannotApprove->getMessage())->toContain('approve')
        ->and($cannotApprove->getMessage())->toContain('(User ID: 1)');

    $cannotReject = UnauthorizedApprovalException::cannotReject(2);
    expect($cannotReject)->toBeInstanceOf(UnauthorizedApprovalException::class)
        ->and($cannotReject->getMessage())->toContain('reject')
        ->and($cannotReject->getMessage())->toContain('(User ID: 2)');

    $cannotSetPending = UnauthorizedApprovalException::cannotSetPending();
    expect($cannotSetPending)->toBeInstanceOf(UnauthorizedApprovalException::class)
        ->and($cannotSetPending->getMessage())->toContain('set pending')
        ->and($cannotSetPending->getMessage())->not->toContain('User ID');
}); 
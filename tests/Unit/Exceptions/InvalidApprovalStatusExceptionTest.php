<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Exceptions;

use LaravelApproval\LaravelApproval\Exceptions\ApprovalException;
use LaravelApproval\LaravelApproval\Exceptions\InvalidApprovalStatusException;
use PHPUnit\Framework\TestCase;

final class InvalidApprovalStatusExceptionTest extends TestCase
{
    public function test_invalid_approval_status_exception_extends_approval_exception(): void
    {
        $exception = new InvalidApprovalStatusException('Test message');

        $this->assertInstanceOf(ApprovalException::class, $exception);
    }

    public function test_invalid_approval_status_exception_can_be_created_with_message(): void
    {
        $message = 'Invalid status provided';
        $exception = new InvalidApprovalStatusException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_invalid_approval_status_exception_has_specific_code(): void
    {
        $exception = new InvalidApprovalStatusException('Invalid status');

        $this->assertEquals(1001, $exception->getCode());
    }

    public function test_invalid_approval_status_exception_can_override_code(): void
    {
        $customCode = 2001;
        $exception = new InvalidApprovalStatusException('Invalid status', $customCode);

        $this->assertEquals($customCode, $exception->getCode());
    }

    public function test_invalid_approval_status_exception_with_invalid_status_value(): void
    {
        $invalidStatus = 'invalid_status';
        $exception = InvalidApprovalStatusException::forInvalidStatus($invalidStatus);

        $expectedMessage = "Invalid approval status '{$invalidStatus}'. Valid statuses are: pending, approved, rejected.";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(1001, $exception->getCode());
    }

    public function test_invalid_approval_status_exception_for_empty_status(): void
    {
        $exception = InvalidApprovalStatusException::forEmptyStatus();

        $this->assertEquals('Approval status cannot be empty.', $exception->getMessage());
        $this->assertEquals(1002, $exception->getCode());
    }

    public function test_invalid_approval_status_exception_for_null_status(): void
    {
        $exception = InvalidApprovalStatusException::forNullStatus();

        $this->assertEquals('Approval status cannot be null.', $exception->getMessage());
        $this->assertEquals(1003, $exception->getCode());
    }

    public function test_invalid_approval_status_exception_can_be_thrown(): void
    {
        $this->expectException(InvalidApprovalStatusException::class);
        $this->expectExceptionMessage('Invalid status test');
        $this->expectExceptionCode(1001);

        throw new InvalidApprovalStatusException('Invalid status test');
    }

    public function test_invalid_approval_status_exception_can_be_caught_as_approval_exception(): void
    {
        try {
            throw new InvalidApprovalStatusException('Caught exception');
        } catch (ApprovalException $e) {
            $this->assertEquals('Caught exception', $e->getMessage());
            $this->assertInstanceOf(InvalidApprovalStatusException::class, $e);
        }
    }

    public function test_invalid_approval_status_exception_with_previous_exception(): void
    {
        $previous = new \InvalidArgumentException('Previous error');
        $exception = new InvalidApprovalStatusException('Current error', 1001, $previous);

        $this->assertEquals('Current error', $exception->getMessage());
        $this->assertEquals(1001, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}

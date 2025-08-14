<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Exceptions;

use LaravelApproval\LaravelApproval\Exceptions\ApprovalException;
use PHPUnit\Framework\TestCase;

final class ApprovalExceptionTest extends TestCase
{
    public function test_approval_exception_extends_exception(): void
    {
        $exception = new ApprovalException('Test message');

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_approval_exception_can_be_created_with_message(): void
    {
        $message = 'Something went wrong with approval';
        $exception = new ApprovalException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_approval_exception_can_be_created_with_message_and_code(): void
    {
        $message = 'Approval error';
        $code = 1001;
        $exception = new ApprovalException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function test_approval_exception_can_be_created_with_previous_exception(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new ApprovalException('Current error', 0, $previous);

        $this->assertEquals('Current error', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_approval_exception_has_default_values(): void
    {
        $exception = new ApprovalException;

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function test_approval_exception_can_be_thrown(): void
    {
        $this->expectException(ApprovalException::class);
        $this->expectExceptionMessage('Test exception');
        $this->expectExceptionCode(500);

        throw new ApprovalException('Test exception', 500);
    }

    public function test_approval_exception_can_be_caught(): void
    {
        try {
            throw new ApprovalException('Caught exception');
        } catch (ApprovalException $e) {
            $this->assertEquals('Caught exception', $e->getMessage());
            $this->assertInstanceOf(ApprovalException::class, $e);
        }
    }
}

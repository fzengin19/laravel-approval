<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Exceptions;

use LaravelApproval\LaravelApproval\Exceptions\ApprovalException;
use LaravelApproval\LaravelApproval\Exceptions\UnauthorizedApprovalException;
use PHPUnit\Framework\TestCase;

final class UnauthorizedApprovalExceptionTest extends TestCase
{
    public function test_unauthorized_approval_exception_extends_approval_exception(): void
    {
        $exception = new UnauthorizedApprovalException('Test message');

        $this->assertInstanceOf(ApprovalException::class, $exception);
    }

    public function test_unauthorized_approval_exception_can_be_created_with_message(): void
    {
        $message = 'Unauthorized approval action';
        $exception = new UnauthorizedApprovalException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_unauthorized_approval_exception_has_specific_code(): void
    {
        $exception = new UnauthorizedApprovalException('Unauthorized');

        $this->assertEquals(2001, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_can_override_code(): void
    {
        $customCode = 3001;
        $exception = new UnauthorizedApprovalException('Unauthorized', $customCode);

        $this->assertEquals($customCode, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_for_user(): void
    {
        $userId = 123;
        $action = 'approve';
        $exception = UnauthorizedApprovalException::forUser($userId, $action);

        $expectedMessage = "User with ID {$userId} is not authorized to perform '{$action}' action.";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(2001, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_for_missing_causer(): void
    {
        $exception = UnauthorizedApprovalException::forMissingCauser();

        $this->assertEquals('Approval action requires a causer (user who performs the action).', $exception->getMessage());
        $this->assertEquals(2002, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_for_invalid_causer(): void
    {
        $causerId = 'invalid_id';
        $exception = UnauthorizedApprovalException::forInvalidCauser($causerId);

        $expectedMessage = "Invalid causer ID '{$causerId}'. Causer must be a valid user ID.";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(2003, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_for_self_approval(): void
    {
        $userId = 456;
        $exception = UnauthorizedApprovalException::forSelfApproval($userId);

        $expectedMessage = "User with ID {$userId} cannot approve their own content.";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(2004, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_for_role(): void
    {
        $role = 'user';
        $requiredRole = 'admin';
        $exception = UnauthorizedApprovalException::forRole($role, $requiredRole);

        $expectedMessage = "Role '{$role}' is not authorized. Required role: '{$requiredRole}'.";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(2005, $exception->getCode());
    }

    public function test_unauthorized_approval_exception_can_be_thrown(): void
    {
        $this->expectException(UnauthorizedApprovalException::class);
        $this->expectExceptionMessage('Unauthorized test');
        $this->expectExceptionCode(2001);

        throw new UnauthorizedApprovalException('Unauthorized test');
    }

    public function test_unauthorized_approval_exception_can_be_caught_as_approval_exception(): void
    {
        try {
            throw new UnauthorizedApprovalException('Caught exception');
        } catch (ApprovalException $e) {
            $this->assertEquals('Caught exception', $e->getMessage());
            $this->assertInstanceOf(UnauthorizedApprovalException::class, $e);
        }
    }

    public function test_unauthorized_approval_exception_with_previous_exception(): void
    {
        $previous = new \UnexpectedValueException('Previous error');
        $exception = new UnauthorizedApprovalException('Current error', 2001, $previous);

        $this->assertEquals('Current error', $exception->getMessage());
        $this->assertEquals(2001, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_unauthorized_approval_exception_for_user_with_string_id(): void
    {
        $userId = 'user_uuid_123';
        $action = 'reject';
        $exception = UnauthorizedApprovalException::forUser($userId, $action);

        $expectedMessage = "User with ID {$userId} is not authorized to perform '{$action}' action.";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(2001, $exception->getCode());
    }
}

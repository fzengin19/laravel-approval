<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Contracts;

use LaravelApproval\LaravelApproval\Contracts\ApprovalValidatorInterface;
use PHPUnit\Framework\TestCase;

final class ApprovalValidatorInterfaceTest extends TestCase
{
    public function test_approval_validator_interface_exists(): void
    {
        $this->assertTrue(interface_exists(ApprovalValidatorInterface::class));
    }

    public function test_approval_validator_interface_has_required_methods(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);

        $expectedMethods = [
            'validateApprovalData',
            'validateStatus',
            'validateCauser',
            'validateModel',
            'validateRejectionReason',
            'canApprove',
            'canReject',
            'canSetPending',
        ];

        $actualMethods = array_map(
            fn (\ReflectionMethod $method) => $method->getName(),
            $reflection->getMethods()
        );

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains(
                $expectedMethod,
                $actualMethods,
                "ApprovalValidatorInterface should have {$expectedMethod} method"
            );
        }
    }

    public function test_approval_validator_interface_validate_approval_data_method(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateApprovalData');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('data', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType()?->getName());
    }

    public function test_approval_validator_interface_validate_status_method(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateStatus');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('status', $parameters[0]->getName());
    }

    public function test_approval_validator_interface_validate_causer_method(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateCauser');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('causerId', $parameters[0]->getName());
    }

    public function test_approval_validator_interface_validate_model_method(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateModel');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
    }

    public function test_approval_validator_interface_validate_rejection_reason_method(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateRejectionReason');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('reason', $parameters[0]->getName());
        $this->assertEquals('modelClass', $parameters[1]->getName());
    }

    public function test_approval_validator_interface_permission_methods(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);

        $permissionMethods = ['canApprove', 'canReject', 'canSetPending'];

        foreach ($permissionMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic());
            $this->assertEquals('bool', $method->getReturnType()?->getName());

            $parameters = $method->getParameters();
            $this->assertCount(2, $parameters);
            $this->assertEquals('model', $parameters[0]->getName());
            $this->assertEquals('causerId', $parameters[1]->getName());
        }
    }

    public function test_approval_validator_interface_can_approve_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('canApprove');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
        $this->assertEquals('causerId', $parameters[1]->getName());
    }

    public function test_approval_validator_interface_can_reject_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('canReject');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
        $this->assertEquals('causerId', $parameters[1]->getName());
    }

    public function test_approval_validator_interface_can_set_pending_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('canSetPending');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
        $this->assertEquals('causerId', $parameters[1]->getName());
    }
}

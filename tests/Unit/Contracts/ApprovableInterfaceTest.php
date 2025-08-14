<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Contracts;

use LaravelApproval\LaravelApproval\Contracts\ApprovableInterface;
use PHPUnit\Framework\TestCase;

final class ApprovableInterfaceTest extends TestCase
{
    public function test_approvable_interface_exists(): void
    {
        $this->assertTrue(interface_exists(ApprovableInterface::class));
    }

    public function test_approvable_interface_has_required_methods(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);

        $expectedMethods = [
            'approve',
            'reject',
            'setPending',
            'isApproved',
            'isPending',
            'isRejected',
            'getApprovalStatus',
            'getApprovalConfig',
            'approvals',
            'latestApproval',
        ];

        $actualMethods = array_map(
            fn (\ReflectionMethod $method) => $method->getName(),
            $reflection->getMethods()
        );

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains(
                $expectedMethod,
                $actualMethods,
                "ApprovableInterface should have {$expectedMethod} method"
            );
        }
    }

    public function test_approvable_interface_approve_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);
        $method = $reflection->getMethod('approve');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('self', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('causerId', $parameters[0]->getName());
        $this->assertEquals('int', $parameters[0]->getType()?->getName());
    }

    public function test_approvable_interface_reject_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);
        $method = $reflection->getMethod('reject');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('self', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(3, $parameters);
        $this->assertEquals('causerId', $parameters[0]->getName());
        $this->assertEquals('reason', $parameters[1]->getName());
        $this->assertEquals('comment', $parameters[2]->getName());
    }

    public function test_approvable_interface_set_pending_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);
        $method = $reflection->getMethod('setPending');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('self', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('causerId', $parameters[0]->getName());
        $this->assertEquals('int', $parameters[0]->getType()?->getName());
    }

    public function test_approvable_interface_status_check_methods(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);

        $statusMethods = ['isApproved', 'isPending', 'isRejected'];

        foreach ($statusMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic());
            $this->assertEquals('bool', $method->getReturnType()?->getName());
            $this->assertCount(0, $method->getParameters());
        }
    }

    public function test_approvable_interface_get_approval_status_method(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);
        $method = $reflection->getMethod('getApprovalStatus');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_approvable_interface_get_approval_config_method(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);
        $method = $reflection->getMethod('getApprovalConfig');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('array', $method->getReturnType()?->getName());
        $this->assertCount(0, $method->getParameters());
    }

    public function test_approvable_interface_relationship_methods(): void
    {
        $reflection = new \ReflectionClass(ApprovableInterface::class);

        $relationshipMethods = ['approvals', 'latestApproval'];

        foreach ($relationshipMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic());
            $this->assertCount(0, $method->getParameters());
        }
    }
}

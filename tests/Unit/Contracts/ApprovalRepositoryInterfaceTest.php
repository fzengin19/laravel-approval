<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Contracts;

use LaravelApproval\LaravelApproval\Contracts\ApprovalRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ApprovalRepositoryInterfaceTest extends TestCase
{
    public function test_approval_repository_interface_exists(): void
    {
        $this->assertTrue(interface_exists(ApprovalRepositoryInterface::class));
    }

    public function test_approval_repository_interface_has_required_methods(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);

        $expectedMethods = [
            'create',
            'update',
            'findByModel',
            'findLatestByModel',
            'deleteByModel',
            'getByStatus',
            'countByStatus',
            'exists',
        ];

        $actualMethods = array_map(
            fn (\ReflectionMethod $method) => $method->getName(),
            $reflection->getMethods()
        );

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains(
                $expectedMethod,
                $actualMethods,
                "ApprovalRepositoryInterface should have {$expectedMethod} method"
            );
        }
    }

    public function test_approval_repository_interface_create_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('create');

        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('data', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType()?->getName());
    }

    public function test_approval_repository_interface_update_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('update');

        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('id', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());
        $this->assertEquals('array', $parameters[1]->getType()?->getName());
    }

    public function test_approval_repository_interface_find_by_model_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('findByModel');

        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
    }

    public function test_approval_repository_interface_find_latest_by_model_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('findLatestByModel');

        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
    }

    public function test_approval_repository_interface_delete_by_model_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('deleteByModel');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
    }

    public function test_approval_repository_interface_get_by_status_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('getByStatus');

        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('status', $parameters[0]->getName());
        $this->assertEquals('modelClass', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
        $this->assertNull($parameters[1]->getDefaultValue());
    }

    public function test_approval_repository_interface_count_by_status_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('countByStatus');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('int', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('status', $parameters[0]->getName());
        $this->assertEquals('modelClass', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
        $this->assertNull($parameters[1]->getDefaultValue());
    }

    public function test_approval_repository_interface_exists_method_signature(): void
    {
        $reflection = new \ReflectionClass(ApprovalRepositoryInterface::class);
        $method = $reflection->getMethod('exists');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('bool', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('model', $parameters[0]->getName());
    }
}

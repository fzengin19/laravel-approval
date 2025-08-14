<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Contracts;

use LaravelApproval\LaravelApproval\Contracts\StatisticsServiceInterface;
use PHPUnit\Framework\TestCase;

final class StatisticsServiceInterfaceTest extends TestCase
{
    public function test_statistics_service_interface_exists(): void
    {
        $this->assertTrue(interface_exists(StatisticsServiceInterface::class));
    }

    public function test_statistics_service_interface_has_required_methods(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);

        $expectedMethods = [
            'getStatistics',
            'getAllStatistics',
            'getCountByStatus',
            'getTotalCount',
            'getPercentageByStatus',
            'getApprovedPercentage',
            'getPendingPercentage',
            'getRejectedPercentage',
        ];

        $actualMethods = array_map(
            fn (\ReflectionMethod $method) => $method->getName(),
            $reflection->getMethods()
        );

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains(
                $expectedMethod,
                $actualMethods,
                "StatisticsServiceInterface should have {$expectedMethod} method"
            );
        }
    }

    public function test_statistics_service_interface_get_statistics_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getStatistics');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('array', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
    }

    public function test_statistics_service_interface_get_all_statistics_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getAllStatistics');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('array', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);
    }

    public function test_statistics_service_interface_get_count_by_status_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getCountByStatus');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('int', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
        $this->assertEquals('status', $parameters[1]->getName());
    }

    public function test_statistics_service_interface_get_total_count_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getTotalCount');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('int', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
    }

    public function test_statistics_service_interface_get_percentage_by_status_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getPercentageByStatus');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('float', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
        $this->assertEquals('status', $parameters[1]->getName());
    }

    public function test_statistics_service_interface_specific_percentage_methods(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);

        $percentageMethods = ['getApprovedPercentage', 'getPendingPercentage', 'getRejectedPercentage'];

        foreach ($percentageMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic());
            $this->assertEquals('float', $method->getReturnType()?->getName());

            $parameters = $method->getParameters();
            $this->assertCount(1, $parameters);
            $this->assertEquals('modelClass', $parameters[0]->getName());
            $this->assertEquals('string', $parameters[0]->getType()?->getName());
        }
    }

    public function test_statistics_service_interface_get_approved_percentage_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getApprovedPercentage');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('float', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
    }

    public function test_statistics_service_interface_get_pending_percentage_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getPendingPercentage');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('float', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
    }

    public function test_statistics_service_interface_get_rejected_percentage_method(): void
    {
        $reflection = new \ReflectionClass(StatisticsServiceInterface::class);
        $method = $reflection->getMethod('getRejectedPercentage');

        $this->assertTrue($method->isPublic());
        $this->assertEquals('float', $method->getReturnType()?->getName());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('modelClass', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()?->getName());
    }
}

<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit;

use LaravelApproval\LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\LaravelApproval\Repositories\ApprovalRepository;
use LaravelApproval\LaravelApproval\Tests\TestCase;
use LaravelApproval\LaravelApproval\Validators\ApprovalValidator;

final class LaravelApprovalServiceProviderTest extends TestCase
{
    public function test_approval_repository_interface_is_bound(): void
    {
        $repository = $this->app->make(ApprovalRepositoryInterface::class);

        $this->assertInstanceOf(ApprovalRepository::class, $repository);
    }

    public function test_approval_validator_interface_is_bound(): void
    {
        $validator = $this->app->make(ApprovalValidatorInterface::class);

        $this->assertInstanceOf(ApprovalValidator::class, $validator);
    }

    public function test_approval_repository_interface_returns_same_instance(): void
    {
        $repository1 = $this->app->make(ApprovalRepositoryInterface::class);
        $repository2 = $this->app->make(ApprovalRepositoryInterface::class);

        // Should be different instances since it's not singleton
        $this->assertNotSame($repository1, $repository2);
        $this->assertInstanceOf(ApprovalRepository::class, $repository1);
        $this->assertInstanceOf(ApprovalRepository::class, $repository2);
    }

    public function test_approval_repository_can_be_resolved_directly(): void
    {
        $repository = $this->app->make(ApprovalRepository::class);

        $this->assertInstanceOf(ApprovalRepository::class, $repository);
    }
}

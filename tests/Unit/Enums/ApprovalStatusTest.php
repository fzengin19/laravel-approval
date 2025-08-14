<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Enums;

use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use PHPUnit\Framework\TestCase;

final class ApprovalStatusTest extends TestCase
{
    public function test_enum_has_correct_cases(): void
    {
        $cases = ApprovalStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ApprovalStatus::PENDING, $cases);
        $this->assertContains(ApprovalStatus::APPROVED, $cases);
        $this->assertContains(ApprovalStatus::REJECTED, $cases);
    }

    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('pending', ApprovalStatus::PENDING->value);
        $this->assertEquals('approved', ApprovalStatus::APPROVED->value);
        $this->assertEquals('rejected', ApprovalStatus::REJECTED->value);
    }

    public function test_can_create_from_string(): void
    {
        $this->assertEquals(ApprovalStatus::PENDING, ApprovalStatus::from('pending'));
        $this->assertEquals(ApprovalStatus::APPROVED, ApprovalStatus::from('approved'));
        $this->assertEquals(ApprovalStatus::REJECTED, ApprovalStatus::from('rejected'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(ApprovalStatus::tryFrom('invalid'));
        $this->assertNull(ApprovalStatus::tryFrom(''));
        $this->assertNull(ApprovalStatus::tryFrom('PENDING'));
    }

    public function test_enum_value_can_be_accessed(): void
    {
        $this->assertEquals('pending', ApprovalStatus::PENDING->value);
        $this->assertEquals('approved', ApprovalStatus::APPROVED->value);
        $this->assertEquals('rejected', ApprovalStatus::REJECTED->value);
    }
}

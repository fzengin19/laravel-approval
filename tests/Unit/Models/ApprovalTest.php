<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Models;

use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;
use LaravelApproval\LaravelApproval\Tests\TestCase;

final class ApprovalTest extends TestCase
{
    public function test_approval_has_correct_fillable_attributes(): void
    {
        $approval = new Approval;

        $expected = [
            'approvable_type',
            'approvable_id',
            'causer_type',
            'causer_id',
            'status',
            'rejection_reason',
            'rejection_comment',
        ];

        $this->assertEquals($expected, $approval->getFillable());
    }

    public function test_approval_has_correct_casts(): void
    {
        $approval = new Approval;

        $casts = $approval->getCasts();

        $this->assertEquals(ApprovalStatus::class, $casts['status']);
        // Timestamps are automatically handled by Laravel
        $this->assertArrayHasKey('status', $casts);
    }

    public function test_approval_belongs_to_approvable_model(): void
    {
        $approval = Approval::factory()->make();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $approval->approvable());
    }

    public function test_approval_belongs_to_causer(): void
    {
        $approval = Approval::factory()->make();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $approval->causer());
    }

    public function test_approval_can_be_created_with_enum_status(): void
    {
        $approval = Approval::factory()->create([
            'status' => ApprovalStatus::APPROVED,
        ]);

        $this->assertEquals(ApprovalStatus::APPROVED, $approval->status);
    }

    public function test_approval_can_be_created_with_string_status(): void
    {
        $approval = Approval::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertEquals(ApprovalStatus::PENDING, $approval->status);
    }

    public function test_approval_table_name_is_correct(): void
    {
        $approval = new Approval;

        $this->assertEquals('approvals', $approval->getTable());
    }

    public function test_approval_has_timestamps(): void
    {
        $approval = new Approval;

        $this->assertTrue($approval->usesTimestamps());
    }

    public function test_approval_factory_creates_valid_instance(): void
    {
        $approval = Approval::factory()->create();

        $this->assertInstanceOf(Approval::class, $approval);
        $this->assertNotNull($approval->id);
        $this->assertNotNull($approval->approvable_type);
        $this->assertNotNull($approval->approvable_id);
        $this->assertNotNull($approval->status);
        $this->assertInstanceOf(ApprovalStatus::class, $approval->status);
    }

    public function test_approval_can_have_rejection_reason_and_comment(): void
    {
        $approval = Approval::factory()->create([
            'status' => ApprovalStatus::REJECTED,
            'rejection_reason' => 'spam',
            'rejection_comment' => 'This content is spam',
        ]);

        $this->assertEquals('spam', $approval->rejection_reason);
        $this->assertEquals('This content is spam', $approval->rejection_comment);
    }

    public function test_approval_can_have_null_rejection_fields(): void
    {
        $approval = Approval::factory()->create([
            'status' => ApprovalStatus::APPROVED,
            'rejection_reason' => null,
            'rejection_comment' => null,
        ]);

        $this->assertNull($approval->rejection_reason);
        $this->assertNull($approval->rejection_comment);
    }
}

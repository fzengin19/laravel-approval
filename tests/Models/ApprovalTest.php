<?php

namespace LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Carbon;
use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class ApprovalTest extends TestCase
{
    private Post $post;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_be_created_with_valid_data()
    {
        $approval = new Approval();
        $approval->fill([
            'status' => ApprovalStatus::PENDING,
        ]);
        $approval->approvable()->associate($this->post);
        $approval->causer()->associate($this->user);
        $approval->save();

        $this->assertDatabaseHas('approvals', [
            'approvable_id' => $this->post->id,
            'approvable_type' => $this->post->getMorphClass(),
            'status' => ApprovalStatus::PENDING->value,
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
        ]);

        $this->assertInstanceOf(Approval::class, $approval);
        $this->assertEquals(ApprovalStatus::PENDING, $approval->status);
    }

    /** @test */
    public function it_correctly_casts_attributes()
    {
        $approval = Approval::create([
            'approvable_type' => $this->post->getMorphClass(),
            'approvable_id' => $this->post->id,
            'status' => ApprovalStatus::APPROVED,
            'responded_at' => now(),
        ]);

        $this->assertInstanceOf(ApprovalStatus::class, $approval->status);
        $this->assertEquals(ApprovalStatus::APPROVED, $approval->status);
        $this->assertInstanceOf(Carbon::class, $approval->responded_at);
    }

    /** @test */
    public function it_protects_against_mass_assignment()
    {
        $approval = new Approval();

        $this->assertTrue($approval->isFillable('status'));
        $this->assertFalse($approval->isFillable('caused_by_id'));
        $this->assertFalse($approval->isFillable('caused_by_type'));
        $this->assertFalse($approval->isFillable('id'));
    }

    /** @test */
    public function it_correctly_resolves_the_approvable_relationship()
    {
        $approval = Approval::create([
            'approvable_id' => $this->post->id,
            'approvable_type' => $this->post->getMorphClass(),
            'status' => ApprovalStatus::PENDING,
        ]);

        $this->assertInstanceOf(Post::class, $approval->approvable);
        $this->assertTrue($this->post->is($approval->approvable));
    }

    /** @test */
    public function it_correctly_resolves_the_causer_relationship()
    {
        $approval = Approval::factory()->create([
            'approvable_id' => $this->post->id,
            'approvable_type' => $this->post->getMorphClass(),
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
        ]);

        $this->assertInstanceOf(User::class, $approval->causer);
        $this->assertTrue($this->user->is($approval->causer));
    }

    /** @test */
    public function it_can_be_scoped_by_status()
    {
        Approval::factory()->count(2)->create([
            'status' => ApprovalStatus::APPROVED,
        ]);
        Approval::factory()->count(3)->create([
            'status' => ApprovalStatus::PENDING,
        ]);
        Approval::factory()->count(4)->create([
            'status' => ApprovalStatus::REJECTED,
        ]);

        $this->assertEquals(2, Approval::approved()->count());
        $this->assertEquals(3, Approval::pending()->count());
        $this->assertEquals(4, Approval::rejected()->count());
    }
}

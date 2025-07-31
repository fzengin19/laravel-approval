<?php

namespace LaravelApproval\Tests\Core;

use Illuminate\Support\Facades\Config;
use LaravelApproval\Core\ApprovalValidator;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;

class ApprovalValidatorTest extends TestCase
{
    private ApprovalValidator $validator;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ApprovalValidator;
        $this->post = Post::factory()->create();
    }

    /** @test */
    public function validate_approval_validate_rejection_validate_pending_always_returns_true_by_default()
    {
        $this->assertTrue($this->validator->validateApproval($this->post, 1));
        $this->assertTrue($this->validator->validateRejection($this->post, 1, 'spam'));
        $this->assertTrue($this->validator->validatePending($this->post, 1));
    }

    /** @test */
    public function validate_rejection_reason_returns_true_if_allowed_reasons_is_empty()
    {
        $this->assertTrue($this->validator->validateRejectionReason('spam', []));
    }

    /** @test */
    public function validate_rejection_reason_returns_true_if_reason_is_allowed()
    {
        $allowed = ['spam' => 'Spam', 'other' => 'Other'];
        $this->assertTrue($this->validator->validateRejectionReason('spam', $allowed));
    }

    /** @test */
    public function validate_rejection_reason_returns_false_if_reason_is_not_allowed()
    {
        $allowed = ['spam' => 'Spam', 'other' => 'Other'];
        $this->assertFalse($this->validator->validateRejectionReason('foo', $allowed));
    }

    /** @test */
    public function can_approve_can_reject_can_set_pending_always_returns_true_by_default()
    {
        $this->assertTrue($this->validator->canApprove($this->post, 1));
        $this->assertTrue($this->validator->canReject($this->post, 1));
        $this->assertTrue($this->validator->canSetPending($this->post, 1));
    }

    /** @test */
    public function validate_model_configuration_returns_true_for_default_config()
    {
        $this->assertTrue($this->validator->validateModelConfiguration($this->post));
    }

    /** @test */
    public function validate_model_configuration_returns_false_for_invalid_mode()
    {
        config(['approvals.models.'.get_class($this->post).'.mode' => 'invalid']);
        $this->assertFalse($this->validator->validateModelConfiguration($this->post));
    }

    /** @test */
    public function validate_model_configuration_returns_false_for_invalid_rejection_reasons()
    {
        config(['approvals.models.'.get_class($this->post).'.rejection_reasons' => 'not-an-array']);
        $this->assertFalse($this->validator->validateModelConfiguration($this->post));
    }

    /** @test */
    public function it_validates_model_configuration_with_invalid_rejection_reasons()
    {
        config(['approvals.models' => [
            Post::class => [
                'rejection_reasons' => 'invalid_string', // Should be array
            ],
        ]]);

        $result = $this->validator->validateModelConfiguration($this->post);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_validates_model_configuration_with_valid_config()
    {
        config([
            'approvals.models.'.Post::class => [
                'mode' => 'upsert',
                'rejection_reasons' => ['spam' => 'Spam Content', 'other' => 'Other Reason'],
                'allow_custom_reasons' => true,
            ],
        ]);

        $post = new Post;
        $this->assertTrue($this->validator->validateModelConfiguration($post));
    }

    /** @test */
    public function it_returns_false_for_invalid_rejection_reason_configuration_format()
    {
        Config::set('approvals.models.'.Post::class, [
            'rejection_reasons' => [
                'key1' => 'valid_reason', // This should be a simple array
                1 => 'another_reason',
            ],
        ]);

        $post = new Post;
        $this->assertFalse($this->validator->validateModelConfiguration($post));
    }

    /** @test */
    public function it_returns_false_for_invalid_mode_configuration()
    {
        Config::set('approvals.models.'.Post::class, ['mode' => 'invalid_mode']);
        $post = new Post;
        $this->assertFalse($this->validator->validateModelConfiguration($post));
    }

    /** @test */
    public function it_returns_false_for_non_string_rejection_reasons()
    {
        Config::set('approvals.models.'.Post::class, ['rejection_reasons' => [123, null]]);
        $post = new Post;
        $this->assertFalse($this->validator->validateModelConfiguration($post));
    }

    /** @test */
    public function it_returns_false_for_non_boolean_allow_custom_reasons()
    {
        Config::set('approvals.models.'.Post::class, ['allow_custom_reasons' => 'not-a-boolean']);
        $post = new Post;
        $this->assertFalse($this->validator->validateModelConfiguration($post));
    }
}

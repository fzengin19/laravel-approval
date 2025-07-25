<?php

namespace LaravelApproval\Tests\Notifications;

use LaravelApproval\Models\Approval;
use LaravelApproval\Notifications\ModelRejectedNotification;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class ModelRejectedNotificationTest extends TestCase
{
    public function test_notification_has_correct_mail_subject()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(1, 'spam', 'This is spam content');

        $notification = new ModelRejectedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('❌ Post Rejected', $mailMessage->subject);
    }

    public function test_notification_has_correct_mail_content()
    {
        // Config'i set et
        config(['approvals.rejection_reasons' => [
            'inappropriate_content' => 'Inappropriate Content',
            'spam' => 'Spam',
            'duplicate' => 'Duplicate',
            'incomplete' => 'Incomplete',
            'other' => 'Other',
        ]]);

        $user = User::factory()->create(['name' => 'John Doe']);
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'created_by' => $user->id,
        ]);
        $approval = $post->reject(1, 'spam', 'This is spam content');

        // Approval'ın reason ve comment alanlarının doğru set edildiğini kontrol et
        $this->assertEquals('spam', $approval->rejection_reason);
        $this->assertEquals('This is spam content', $approval->rejection_comment);

        $notification = new ModelRejectedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Hello John Doe!', $mailMessage->greeting);
        $this->assertStringContainsString('Post (ID: '.$post->id.') has been rejected.', $mailMessage->introLines[0]);
        $this->assertStringContainsString('Rejected by: User ID: 1', $mailMessage->introLines[1]);

        // Reason ve comment'in introLines'da olup olmadığını kontrol et
        $introLinesText = implode(' ', $mailMessage->introLines);
        $this->assertStringContainsString('Rejection Reason: spam', $introLinesText);
        $this->assertStringContainsString('Comment: This is spam content', $introLinesText);
    }

    public function test_notification_handles_rejection_without_reason_and_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(1);

        $notification = new ModelRejectedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Post (ID: '.$post->id.') has been rejected.', $mailMessage->introLines[0]);

        // Reason ve comment'in introLines'da olmadığını kontrol et
        $introLinesText = implode(' ', $mailMessage->introLines);
        $this->assertStringNotContainsString('Rejection Reason:', $introLinesText);
        $this->assertStringNotContainsString('Comment:', $introLinesText);
    }

    public function test_notification_has_correct_array_representation()
    {
        // Config'i set et
        config(['approvals.rejection_reasons' => [
            'inappropriate_content' => 'Inappropriate Content',
            'spam' => 'Spam',
            'duplicate' => 'Duplicate',
            'incomplete' => 'Incomplete',
            'other' => 'Other',
        ]]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(1, 'spam', 'This is spam');

        // Approval'ın reason ve comment alanlarının doğru set edildiğini kontrol et
        $this->assertEquals('spam', $approval->rejection_reason);
        $this->assertEquals('This is spam', $approval->rejection_comment);

        $notification = new ModelRejectedNotification($post, $approval);
        $array = $notification->toArray($user);

        $this->assertEquals('model_rejected', $array['type']);
        $this->assertEquals(Post::class, $array['model_type']);
        $this->assertEquals($post->id, $array['model_id']);
        $this->assertEquals($approval->id, $array['approval_id']);
        $this->assertEquals(1, $array['caused_by']);
        $this->assertEquals('spam', $array['reason']);
        $this->assertEquals('This is spam', $array['comment']);
        $this->assertEquals('Post reddedildi', $array['message']);
    }

    public function test_notification_via_method_returns_correct_channels()
    {
        // Mail enabled
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.database.enabled' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(1);

        $notification = new ModelRejectedNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertNotContains('database', $channels);

        // Database enabled
        config(['approvals.features.notifications.mail.enabled' => false]);
        config(['approvals.features.notifications.database.enabled' => true]);

        $notification = new ModelRejectedNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertNotContains('mail', $channels);
    }

    public function test_notification_handles_system_rejection()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(null, 'spam'); // System rejection

        $notification = new ModelRejectedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Rejected by: System', $mailMessage->introLines[1]);
    }

    public function test_notification_uses_custom_template_when_configured()
    {
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.mail.template' => 'custom.template']);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(1, 'spam', 'This is spam');

        $notification = new ModelRejectedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        // Custom template kullanıldığını kontrol et
        $this->assertEquals('custom.template', config('approvals.features.notifications.mail.template'));
    }
}

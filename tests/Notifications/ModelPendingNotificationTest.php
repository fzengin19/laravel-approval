<?php

namespace LaravelApproval\Tests\Notifications;

use LaravelApproval\Notifications\ModelPendingNotification;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class ModelPendingNotificationTest extends TestCase
{
    public function test_notification_has_correct_mail_subject()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(1);

        $notification = new ModelPendingNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('⏳ Post Pending Approval', $mailMessage->subject);
    }

    public function test_notification_has_correct_mail_content()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'created_by' => $user->id,
        ]);
        $approval = $post->setPending(1);

        $notification = new ModelPendingNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Hello John Doe!', $mailMessage->greeting);
        $this->assertStringContainsString('Post (ID: '.$post->id.') is pending approval.', $mailMessage->introLines[0]);
        $this->assertStringContainsString('This item will be activated once approved.', $mailMessage->introLines[2]);
    }

    public function test_notification_has_correct_array_representation()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(1);

        $notification = new ModelPendingNotification($post, $approval);
        $array = $notification->toArray($user);

        $this->assertEquals('model_pending', $array['type']);
        $this->assertEquals(Post::class, $array['model_type']);
        $this->assertEquals($post->id, $array['model_id']);
        $this->assertEquals($approval->id, $array['approval_id']);
        $this->assertEquals(1, $array['caused_by']);
        $this->assertEquals('Post onay bekliyor', $array['message']);
    }

    public function test_notification_via_method_returns_correct_channels_when_pending_enabled()
    {
        // Pending notifications enabled
        config(['approvals.features.notifications.events.pending' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.database.enabled' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(1);

        $notification = new ModelPendingNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_notification_via_method_returns_empty_channels_when_pending_disabled()
    {
        // Pending notifications disabled
        config(['approvals.features.notifications.events.pending' => false]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.database.enabled' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(1);

        $notification = new ModelPendingNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertEmpty($channels);
    }

    public function test_notification_handles_system_pending()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(null); // System pending

        $notification = new ModelPendingNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Post (ID: '.$post->id.') is pending approval.', $mailMessage->introLines[0]);
    }

    public function test_notification_uses_custom_template_when_configured()
    {
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.mail.template' => 'custom.template']);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(1);

        $notification = new ModelPendingNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        // Custom template kullanıldığını kontrol et
        $this->assertEquals('custom.template', config('approvals.features.notifications.mail.template'));
    }
}

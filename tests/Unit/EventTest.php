<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Listeners\SendApprovalNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;

class EventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_dispatches_model_approved_event_for_column_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $job->approve();

        Event::assertDispatched(ModelApproved::class, function ($event) use ($job, $user) {
            return $event->getModel()->id === $job->id &&
                   $event->getApprovedBy() === $user->id &&
                   $event->getApprovalMethod() === 'column';
        });
    }

    /** @test */
    public function it_dispatches_model_approved_event_for_pivot_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $article->approve();

        Event::assertDispatched(ModelApproved::class, function ($event) use ($article, $user) {
            return $event->getModel()->id === $article->id &&
                   $event->getApprovedBy() === $user->id &&
                   $event->getApprovalMethod() === 'pivot';
        });
    }

    /** @test */
    public function it_dispatches_model_rejected_event_for_column_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $job->reject('Spam content');

        Event::assertDispatched(ModelRejected::class, function ($event) use ($job, $user) {
            return $event->getModel()->id === $job->id &&
                   $event->getRejectedBy() === $user->id &&
                   $event->getRejectionReason() === 'Spam content' &&
                   $event->getApprovalMethod() === 'column';
        });
    }

    /** @test */
    public function it_dispatches_model_rejected_event_for_pivot_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $article->reject('Inappropriate content');

        Event::assertDispatched(ModelRejected::class, function ($event) use ($article, $user) {
            return $event->getModel()->id === $article->id &&
                   $event->getRejectedBy() === $user->id &&
                   $event->getRejectionReason() === 'Inappropriate content' &&
                   $event->getApprovalMethod() === 'pivot';
        });
    }

    /** @test */
    public function it_dispatches_model_pending_event_for_column_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        // First approve, then set to pending
        $job->approve();
        $job->setPending();

        Event::assertDispatched(ModelPending::class, function ($event) use ($job) {
            return $event->getModel()->id === $job->id &&
                   $event->getApprovalMethod() === 'column';
        });
    }

    /** @test */
    public function it_dispatches_model_pending_event_for_pivot_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        // First approve, then set to pending
        $article->approve();
        $article->setPending();

        Event::assertDispatched(ModelPending::class, function ($event) use ($article) {
            return $event->getModel()->id === $article->id &&
                   $event->getApprovalMethod() === 'pivot';
        });
    }

    /** @test */
    public function it_does_not_dispatch_events_when_disabled()
    {
        // Disable events for Job model
        config()->set('approval.models.' . Job::class . '.events', false);

        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $job->approve();
        $job->reject('Test reason');

        Event::assertNotDispatched(ModelApproved::class);
        Event::assertNotDispatched(ModelRejected::class);
    }

    /** @test */
    public function model_approved_event_has_correct_properties()
    {
        $user = User::factory()->create();
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $event = new ModelApproved($job, $user->id, now(), 'column');

        $this->assertEquals($job, $event->getModel());
        $this->assertEquals($user->id, $event->getApprovedBy());
        $this->assertEquals('column', $event->getApprovalMethod());
        $this->assertNotNull($event->getApprovedAt());
    }

    /** @test */
    public function model_rejected_event_has_correct_properties()
    {
        $user = User::factory()->create();
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $event = new ModelRejected($job, $user->id, 'Test reason', 'column');

        $this->assertEquals($job, $event->getModel());
        $this->assertEquals($user->id, $event->getRejectedBy());
        $this->assertEquals('Test reason', $event->getRejectionReason());
        $this->assertEquals('column', $event->getApprovalMethod());
    }

    /** @test */
    public function model_pending_event_has_correct_properties()
    {
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $event = new ModelPending($job, 'column');

        $this->assertEquals($job, $event->getModel());
        $this->assertEquals('column', $event->getApprovalMethod());
    }

    /** @test */
    public function listener_can_handle_model_approved_event()
    {
        $user = User::factory()->create();
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $event = new ModelApproved($job, $user->id, now(), 'column');
        $listener = new SendApprovalNotification();

        // This should not throw any exceptions
        $listener->handle($event);
        
        // Assert that the listener handled the event without throwing exceptions
        $this->assertTrue(true, 'Listener handled ModelApproved event successfully');
    }

    /** @test */
    public function listener_can_handle_model_rejected_event()
    {
        $user = User::factory()->create();
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $event = new ModelRejected($job, $user->id, 'Test reason', 'column');
        $listener = new SendApprovalNotification();

        // This should not throw any exceptions
        $listener->handle($event);
        
        // Assert that the listener handled the event without throwing exceptions
        $this->assertTrue(true, 'Listener handled ModelRejected event successfully');
    }

    /** @test */
    public function listener_can_handle_model_pending_event()
    {
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $event = new ModelPending($job, 'column');
        $listener = new SendApprovalNotification();

        // This should not throw any exceptions
        $listener->handle($event);
        
        // Assert that the listener handled the event without throwing exceptions
        $this->assertTrue(true, 'Listener handled ModelPending event successfully');
    }
} 
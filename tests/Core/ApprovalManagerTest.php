<?php

use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Core\ApprovalEventDispatcher;
use LaravelApproval\Core\ApprovalManager;
use LaravelApproval\Exceptions\UnauthorizedApprovalException;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    $this->repository = Mockery::mock(ApprovalRepositoryInterface::class);
    $this->validator = Mockery::mock(ApprovalValidatorInterface::class);
    $this->eventDispatcher = Mockery::mock(ApprovalEventDispatcher::class);
    $this->manager = new ApprovalManager($this->repository, $this->validator, $this->eventDispatcher);

    $this->post = Post::create(['title' => 'Test Post', 'content' => 'Content']);
    $this->approval = new Approval(['status' => 'approved', 'caused_by' => 1]);

    // Default expectations for happy paths
    $this->validator->shouldReceive('canApprove')->andReturn(true);
    $this->validator->shouldReceive('validateApproval')->andReturn(true);
    $this->validator->shouldReceive('canReject')->andReturn(true);
    $this->validator->shouldReceive('validateRejection')->andReturn(true);
    $this->validator->shouldReceive('canSetPending')->andReturn(true);
    $this->validator->shouldReceive('validatePending')->andReturn(true);
});

it('approves a model correctly', function () {
    $this->eventDispatcher->shouldReceive('dispatchApproving')->once();
    $this->repository->shouldReceive('create')->once()->andReturn($this->approval);
    $this->eventDispatcher->shouldReceive('dispatchApproved')->once();

    $this->manager->approve($this->post, 1);
});

it('rejects a model correctly', function () {
    $this->eventDispatcher->shouldReceive('dispatchRejecting')->once();
    $this->repository->shouldReceive('create')->once()->andReturn($this->approval);
    $this->eventDispatcher->shouldReceive('dispatchRejected')->once();

    $this->manager->reject($this->post, 1, 'spam', 'test comment');
});

it('sets a model to pending correctly', function () {
    $this->eventDispatcher->shouldReceive('dispatchSettingPending')->once();
    $this->repository->shouldReceive('create')->once()->andReturn($this->approval);
    $this->eventDispatcher->shouldReceive('dispatchPending')->once();

    $this->manager->setPending($this->post, 1);
});

it('returns early when validation fails for approval', function () {
    $validator = Mockery::mock(ApprovalValidatorInterface::class);
    $repository = Mockery::mock(ApprovalRepositoryInterface::class);
    $eventDispatcher = Mockery::mock(ApprovalEventDispatcher::class);
    $manager = new ApprovalManager($repository, $validator, $eventDispatcher);

    $validator->shouldReceive('canApprove')->andReturn(true);
    $validator->shouldReceive('validateApproval')->andReturn(false);

    $this->expectException(UnauthorizedApprovalException::class);

    $manager->approve($this->post, 1);

    $eventDispatcher->shouldNotHaveReceived('dispatchApproving');
    $repository->shouldNotHaveReceived('create');
});

it('uses upsert mode when configured', function () {
    config()->set('approvals.default.mode', 'upsert');

    $this->eventDispatcher->shouldReceive('dispatchApproving')->once();
    $this->repository->shouldReceive('updateOrCreate')->once()->andReturn($this->approval);
    $this->eventDispatcher->shouldReceive('dispatchApproved')->once();

    $this->manager->approve($this->post, 1);
});

it('uses insert mode when configured', function () {
    config()->set('approvals.default.mode', 'insert');

    $this->eventDispatcher->shouldReceive('dispatchApproving')->once();
    $this->repository->shouldReceive('create')->once()->andReturn($this->approval);
    $this->eventDispatcher->shouldReceive('dispatchApproved')->once();

    $this->manager->approve($this->post, 1);
});

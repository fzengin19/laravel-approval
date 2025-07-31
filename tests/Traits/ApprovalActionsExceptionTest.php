<?php

use LaravelApproval\Core\ApprovalEventDispatcher;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Exceptions\UnauthorizedApprovalException;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = Post::create(['title' => 'Test', 'content' => 'Test']);
});

test('setPending throws exception when cannot set pending', function () {
    $mockValidator = Mockery::mock(ApprovalValidatorInterface::class);
    $mockValidator->shouldReceive('canSetPending')->once()->andReturn(false);
    $mockValidator->shouldReceive('validatePending')->never();
    
    app()->instance(ApprovalValidatorInterface::class, $mockValidator);
    
    expect(fn() => $this->post->setPending(1))->toThrow(UnauthorizedApprovalException::class);
});

test('setPending throws exception when validation fails', function () {
    $mockValidator = Mockery::mock(ApprovalValidatorInterface::class);
    $mockValidator->shouldReceive('canSetPending')->once()->andReturn(true);
    $mockValidator->shouldReceive('validatePending')->once()->andReturn(false);
    
    app()->instance(ApprovalValidatorInterface::class, $mockValidator);
    
    expect(fn() => $this->post->setPending(1))->toThrow(UnauthorizedApprovalException::class);
});

test('approve throws exception when cannot approve', function () {
    $mockValidator = Mockery::mock(ApprovalValidatorInterface::class);
    $mockValidator->shouldReceive('canApprove')->once()->andReturn(false);
    $mockValidator->shouldReceive('validateApproval')->never();
    
    app()->instance(ApprovalValidatorInterface::class, $mockValidator);
    
    expect(fn() => $this->post->approve(1))->toThrow(UnauthorizedApprovalException::class);
});

test('approve throws exception when validation fails', function () {
    $mockValidator = Mockery::mock(ApprovalValidatorInterface::class);
    $mockValidator->shouldReceive('canApprove')->once()->andReturn(true);
    $mockValidator->shouldReceive('validateApproval')->once()->andReturn(false);
    
    app()->instance(ApprovalValidatorInterface::class, $mockValidator);
    
    expect(fn() => $this->post->approve(1))->toThrow(UnauthorizedApprovalException::class);
});

test('reject throws exception when cannot reject', function () {
    $mockValidator = Mockery::mock(ApprovalValidatorInterface::class);
    $mockValidator->shouldReceive('canReject')->once()->andReturn(false);
    $mockValidator->shouldReceive('validateRejection')->never();
    
    app()->instance(ApprovalValidatorInterface::class, $mockValidator);
    
    expect(fn() => $this->post->reject(1, 'spam', 'Test'))->toThrow(UnauthorizedApprovalException::class);
});

test('reject throws exception when validation fails', function () {
    $mockValidator = Mockery::mock(ApprovalValidatorInterface::class);
    $mockValidator->shouldReceive('canReject')->once()->andReturn(true);
    $mockValidator->shouldReceive('validateRejection')->once()->andReturn(false);
    
    app()->instance(ApprovalValidatorInterface::class, $mockValidator);
    
    expect(fn() => $this->post->reject(1, 'spam', 'Test'))->toThrow(UnauthorizedApprovalException::class);
}); 
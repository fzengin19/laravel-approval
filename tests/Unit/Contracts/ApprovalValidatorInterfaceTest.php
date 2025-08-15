<?php

declare(strict_types=1);

use LaravelApproval\LaravelApproval\Contracts\ApprovalValidatorInterface;

describe('ApprovalValidatorInterface', function () {
    it('defines validateApproval method', function () {
        $reflection = new ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateApproval');

        expect($method->getName())->toBe('validateApproval');
        expect($method->getReturnType()->getName())->toBe('void');

        $parameters = $method->getParameters();
        expect($parameters)->toHaveCount(2);
        expect($parameters[0]->getName())->toBe('model');
        expect($parameters[0]->getType()->getName())->toBe('LaravelApproval\LaravelApproval\Contracts\ApprovableInterface');
        expect($parameters[1]->getName())->toBe('causerId');
        expect($parameters[1]->getType()->getName())->toBe('int');
        expect($parameters[1]->isDefaultValueAvailable())->toBeTrue();
        expect($parameters[1]->getDefaultValue())->toBeNull();
    });

    it('defines validateRejection method', function () {
        $reflection = new ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateRejection');

        expect($method->getName())->toBe('validateRejection');
        expect($method->getReturnType()->getName())->toBe('void');

        $parameters = $method->getParameters();
        expect($parameters)->toHaveCount(4);
        expect($parameters[0]->getName())->toBe('model');
        expect($parameters[0]->getType()->getName())->toBe('LaravelApproval\LaravelApproval\Contracts\ApprovableInterface');
        expect($parameters[1]->getName())->toBe('causerId');
        expect($parameters[1]->getType()->getName())->toBe('int');
        expect($parameters[1]->isDefaultValueAvailable())->toBeTrue();
        expect($parameters[1]->getDefaultValue())->toBeNull();
        expect($parameters[2]->getName())->toBe('reason');
        expect($parameters[2]->getType()->getName())->toBe('string');
        expect($parameters[2]->isDefaultValueAvailable())->toBeTrue();
        expect($parameters[2]->getDefaultValue())->toBeNull();
        expect($parameters[3]->getName())->toBe('comment');
        expect($parameters[3]->getType()->getName())->toBe('string');
        expect($parameters[3]->isDefaultValueAvailable())->toBeTrue();
        expect($parameters[3]->getDefaultValue())->toBeNull();
    });

    it('defines validatePending method', function () {
        $reflection = new ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validatePending');

        expect($method->getName())->toBe('validatePending');
        expect($method->getReturnType()->getName())->toBe('void');

        $parameters = $method->getParameters();
        expect($parameters)->toHaveCount(2);
        expect($parameters[0]->getName())->toBe('model');
        expect($parameters[0]->getType()->getName())->toBe('LaravelApproval\LaravelApproval\Contracts\ApprovableInterface');
        expect($parameters[1]->getName())->toBe('causerId');
        expect($parameters[1]->getType()->getName())->toBe('int');
        expect($parameters[1]->isDefaultValueAvailable())->toBeTrue();
        expect($parameters[1]->getDefaultValue())->toBeNull();
    });

    it('defines validateStatusTransition method', function () {
        $reflection = new ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateStatusTransition');

        expect($method->getName())->toBe('validateStatusTransition');
        expect($method->getReturnType()->getName())->toBe('void');

        $parameters = $method->getParameters();
        expect($parameters)->toHaveCount(2);
        expect($parameters[0]->getName())->toBe('model');
        expect($parameters[0]->getType()->getName())->toBe('LaravelApproval\LaravelApproval\Contracts\ApprovableInterface');
        expect($parameters[1]->getName())->toBe('newStatus');
        expect($parameters[1]->getType()->getName())->toBe('LaravelApproval\LaravelApproval\Enums\ApprovalStatus');
    });

    it('defines validateModelConfiguration method', function () {
        $reflection = new ReflectionClass(ApprovalValidatorInterface::class);
        $method = $reflection->getMethod('validateModelConfiguration');

        expect($method->getName())->toBe('validateModelConfiguration');
        expect($method->getReturnType()->getName())->toBe('void');

        $parameters = $method->getParameters();
        expect($parameters)->toHaveCount(1);
        expect($parameters[0]->getName())->toBe('model');
        expect($parameters[0]->getType()->getName())->toBe('LaravelApproval\LaravelApproval\Contracts\ApprovableInterface');
    });
});
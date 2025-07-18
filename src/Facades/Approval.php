<?php

namespace LaravelApproval\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelApproval\LaravelApproval
 */
class Approval extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-approval';
    }
} 
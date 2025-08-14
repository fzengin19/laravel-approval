<?php

namespace LaravelApproval\LaravelApproval\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelApproval\LaravelApproval\LaravelApproval
 */
class LaravelApproval extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelApproval\LaravelApproval\LaravelApproval::class;
    }
}

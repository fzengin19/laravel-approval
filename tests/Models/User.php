<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    protected static function newFactory()
    {
        return \LaravelApproval\LaravelApproval\Tests\Database\Factories\UserFactory::new();
    }
}

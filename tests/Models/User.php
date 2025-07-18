<?php

namespace LaravelApproval\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function newFactory()
    {
        return \LaravelApproval\Database\Factories\UserFactory::new();
    }
} 
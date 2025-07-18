<?php

namespace LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\HasApproval;

class Company extends Model
{
    use HasApproval;

    protected $fillable = [
        'name',
        'description',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];
} 
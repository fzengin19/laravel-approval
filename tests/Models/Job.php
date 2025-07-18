<?php

namespace LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\HasApproval;

class Job extends Model
{
    use HasApproval;

    protected $fillable = [
        'title',
        'description',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];
} 
<?php

namespace LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\HasApproval;

class Post extends Model
{
    use HasApproval;

    protected $fillable = [
        'title',
        'content',
        'approval_status',
    ];

    protected $casts = [
        'approval_status' => 'string',
    ];
} 
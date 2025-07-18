<?php

namespace LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\HasApproval;

class Article extends Model
{
    use HasApproval;

    protected $fillable = [
        'title',
        'content',
    ];
} 
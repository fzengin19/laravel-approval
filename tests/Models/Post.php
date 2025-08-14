<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
    ];

    protected static function newFactory()
    {
        return \LaravelApproval\LaravelApproval\Tests\Database\Factories\PostFactory::new();
    }
}

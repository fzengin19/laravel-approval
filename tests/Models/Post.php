<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\Approvable;

class Post extends Model
{
    use Approvable, HasFactory;

    protected $fillable = [
        'title',
        'content',
        'created_by',
        'user_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

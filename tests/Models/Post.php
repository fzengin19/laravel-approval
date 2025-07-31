<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\Approvable;
use LaravelApproval\Contracts\ApprovableInterface;

class Post extends Model implements ApprovableInterface
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

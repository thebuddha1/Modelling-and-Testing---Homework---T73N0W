<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImpression extends Model
{
    protected $fillable = [
        'user_id',
        'forum_id',
        'like',
        'dislike'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
}
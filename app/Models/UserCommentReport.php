<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCommentReport extends Model
{
    protected $fillable = ['user_id', 'comment_id', 'reported'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
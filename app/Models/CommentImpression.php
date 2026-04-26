<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentImpression extends Model
{
    protected $fillable = [
        'likes',
        'dislikes',
        'comment_id',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}

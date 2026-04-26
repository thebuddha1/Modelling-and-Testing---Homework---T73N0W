<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impression extends Model
{
    protected $fillable = [
        'likes',
        'dislikes',
        'forum_id',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
}

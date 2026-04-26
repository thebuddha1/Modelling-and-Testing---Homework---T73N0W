<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['forum_id', 'count'];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
}

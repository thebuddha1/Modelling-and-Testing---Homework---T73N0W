<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'forum_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function impression()
    {
        return $this->hasOne(CommentImpression::class);
    }

    public function userImpressions()
    {
        return $this->hasMany(UserCommentImpression::class);
    }

    public function userImpressionForCurrentUser()
    {
        return $this->hasOne(UserCommentImpression::class)
            ->where('user_id', auth()->id());
    }

    public function report()
    {
        return $this->hasOne(CommentReport::class);
    }

    public function userReports()
    {
        return $this->hasMany(UserCommentReport::class);
    }

    public function userReportForCurrentUser()
    {
        return $this->userReports()->where('user_id', auth()->id());
    }
}

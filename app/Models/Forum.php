<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'content',
        'owner',
        'category_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner');
    }

    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function impression()
    {
        return $this->hasOne(Impression::class);
    }

    public function userImpressions()
    {
        return $this->hasMany(UserImpression::class);
    }

    public function userImpressionForCurrentUser()
    {
        return $this->hasOne(UserImpression::class)
            ->where('user_id', auth()->id());
    }

    public function report()
    {
        return $this->hasOne(Report::class);
    }

    public function userReports()
    {
        return $this->hasMany(UserReport::class);
    }

    public function userReportForCurrentUser()
    {
        return $this->hasOne(UserReport::class)
            ->where('user_id', auth()->id());
    }

    public function savedByUsers()
    {
        return $this->hasMany(SavedForum::class);
    }

    public function savedByCurrentUser()
    {
        return $this->savedByUsers()->where('user_id', auth()->id());
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRating extends Model
{
    protected $fillable = [
        'rating_user_id',
        'rated_user_id',
        'precision',
        'driving',
        'social',
    ];

    public function ratingUser()
    {
        return $this->belongsTo(User::class, 'rating_user_id');
    }

    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }
}

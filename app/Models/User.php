<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'experience_level',
        'bike_type',
        'bike_category',
        'bike_year',
        'avatar',
        'is_private',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'previous_login_at' => 'datetime',
            'password' => 'hashed',
            'bike_year' => 'integer',
            'subscription_start_at' => 'datetime',
            'subscription_end_at' => 'datetime',
        ];
    }

    public function forums()
    {
        return $this->hasMany(Forum::class, 'owner');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    public function joinedTours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class)->withTimestamps();
    }
  
    public function impressions()
    {
        return $this->hasMany(UserImpression::class);
    }

    public function commentImpressions()
    {
        return $this->hasMany(UserCommentImpression::class);
    }

    public function userReports()
    {
        return $this->hasMany(UserReport::class);
    }

    public function userCommentReports()
    {
        return $this->hasMany(UserCommentReport::class);
    }
    
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // default image in public/images/mock_pfp.png
        return asset('images/mock_pfp.png');
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
    
    public function friends()
    {
      return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(UserRating::class, 'rating_user_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(UserRating::class, 'rated_user_id');
    }

    public function ratingForUser($otherUserId)
    {
        return $this->ratingsGiven()
            ->where('rated_user_id', $otherUserId);
    }

    public function savedForums()
    {
        return $this->hasMany(SavedForum::class);
    }

    public function favoritePlaces(): BelongsToMany
    {
        return $this->belongsToMany(FavoritePlace::class, 'user_favorite_places')
            ->withTimestamps();
    }

    public function createdFavoritePlaces(): HasMany
    {
        return $this->hasMany(FavoritePlace::class, 'creator_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'date',
        'description',
        'max_participants',
        'location',
        'is_public',
        'route_geometry',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_public' => 'boolean',
        'route_geometry' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function scopeUpcomingPublic(Builder $query): Builder
    {
        return $query
            ->where('is_public', true)
            ->whereNotNull('date')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date');
    }

    public function getIsFullAttribute(): bool
    {
        $count = $this->participants_count ?? $this->participants()->count();

        return $count >= $this->max_participants;
    }
}

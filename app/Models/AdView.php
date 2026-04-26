<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'viewed_at',
    ];

    protected $dates = [
        'viewed_at',
    ];
}
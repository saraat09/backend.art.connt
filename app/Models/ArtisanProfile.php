<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArtisanProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'trade', 'description', 'location',
        'lat', 'lng', 'available'
    ];

    protected $casts = [
        'available' => 'boolean',
        'lat'       => 'float',
        'lng'       => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(ArtisanService::class);
    }

  // ArtisanProfile.php
public function reviews()
{
    return $this->hasMany(Review::class, 'artisan_id', 'user_id'); // user_id de ArtisanProfile correspond à artisan_id de Review
}

    public function getRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'client_id',
        'artisan_id',
        'rating',
        'comment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }
    public function artisan()
    {
        return $this->belongsTo(ArtisanProfile::class, 'artisan_id', 'user_id');
    }
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
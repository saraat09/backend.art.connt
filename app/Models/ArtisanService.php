<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArtisanService extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_profile_id',
        'service_name',
        'price_from',
    ];

    protected $casts = [
        'price_from' => 'float',
    ];

    /* ── Relations ───────────────────────────── */
    public function artisanProfile()
    {
        return $this->belongsTo(ArtisanProfile::class);
    }
}

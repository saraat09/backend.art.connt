<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /* ── Relation ── */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ── Marquer comme lu ── */
    public function markAsRead()
    {
        $this->update([
            'read_at' => now()
        ]);
    }
}
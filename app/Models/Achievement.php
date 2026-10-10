<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $fillable = ['user_id', 'type', 'title', 'description', 'icon', 'earned_at'];

    protected function casts(): array
    {
        return ['earned_at' => 'datetime'];
    }

public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $a) {
            $a->earned_at ??= now();
        });
    }
}
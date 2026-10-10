<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $fillable = ['serial', 'user_id', 'journey_id', 'issued_at'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $c) {
            $c->issued_at ??= now();
        });
    }
}
<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $fillable = ['user_id', 'journey_id', 'status', 'enrolled_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
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
        static::creating(function (self $e) {
            $e->enrolled_at ??= now();
        });
    }
}
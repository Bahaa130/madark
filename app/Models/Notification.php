<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'type', 'title', 'body', 'url', 'read', 'created_at'];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** notifications keeps created_at but has no updated_at. */
    protected static function booted(): void
    {
        static::creating(function (self $n) {
            $n->created_at ??= now();
        });
    }
}
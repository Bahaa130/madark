<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumThread extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'pinned', 'locked', 'author_id', 'journey_id'];

    protected function casts(): array
    {
        return ['pinned' => 'boolean', 'locked' => 'boolean'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'thread_id');
    }
}
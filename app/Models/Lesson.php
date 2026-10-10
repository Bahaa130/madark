<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title', 'description', 'video_url', 'duration_min', 'order', 'is_preview', 'module_id',
    ];

    protected function casts(): array
    {
        return ['is_preview' => 'boolean'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonAsset::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * The journey that owns this lesson, reached through its module.
     */
    public function journey(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Journey::class,
            Module::class,
            'module_id',   // FK on lessons
            'id',          // FK on modules
            'id',          // local key on lessons
            'journey_id',  // local key on modules
        );
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
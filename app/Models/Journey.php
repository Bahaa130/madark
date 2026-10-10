<?php

namespace App\Models;

use App\Enums\JourneyLevel;
use App\Enums\JourneyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title', 'title_en', 'tagline', 'description', 'cover_image',
        'level', 'status', 'is_free', 'price', 'language', 'duration_min', 'instructor_id',
    ];

    protected function casts(): array
    {
        return [
            'level' => JourneyLevel::class,
            'status' => JourneyStatus::class,
            'is_free' => 'boolean',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /** Every lesson across every module, in curriculum order. */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lesson::class,
            Module::class,
            'journey_id',   // FK on modules
            'module_id',    // FK on lessons
        )
            // Both tables have an `order` column, so an unqualified select is
            // ambiguous and `select *` would let modules.order shadow
            // lessons.order during hydration.
            ->select('lessons.*')
            ->orderBy('modules.order')
            ->orderBy('lessons.order');
    }

    public function lessonsCount(): int
    {
        return $this->lessons()->count();
    }

    public function studentsCount(): int
    {
        return $this->enrollments()->count();
    }

    /** Localised title based on the active locale. */
    public function localizedTitle(string $locale = 'ar'): string
    {
        return $locale === 'en' && $this->title_en ? $this->title_en : $this->title;
    }

    public function scopePublished($query)
    {
        return $query->where('status', JourneyStatus::PUBLISHED);
    }
}
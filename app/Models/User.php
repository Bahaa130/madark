<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'image',
        'role', 'headline', 'bio', 'country',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(Journey::class, 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'author_id');
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'author_id');
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    // ── Role helpers ──
    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isInstructor(): bool
    {
        return $this->role === Role::INSTRUCTOR;
    }

    public function isStudent(): bool
    {
        return $this->role === Role::STUDENT;
    }

    /** Instructors and admins may enter the instructor space. */
    public function canTeach(): bool
    {
        return in_array($this->role, [Role::INSTRUCTOR, Role::ADMIN], true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->email ? explode('@', $this->email)[0] : 'Madarek');
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->display_name, 0, 1));
    }
}
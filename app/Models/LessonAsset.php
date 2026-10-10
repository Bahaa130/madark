<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAsset extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $fillable = ['name', 'url', 'path', 'size_kb', 'mime_type', 'extension', 'lesson_id'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function getHumanSizeAttribute(): string
    {
        return $this->size_kb >= 1024
            ? number_format($this->size_kb / 1024, 1) . ' MB'
            : $this->size_kb . ' KB';
    }
}
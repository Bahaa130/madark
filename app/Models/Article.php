<?php

namespace App\Models;

use App\Enums\LibraryItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title', 'excerpt', 'content', 'cover_url',
        'type', 'language', 'read_min', 'published', 'author_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => LibraryItemType::class,
            'published' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
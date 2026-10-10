<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $table = 'modules';

    protected $fillable = ['title', 'order', 'journey_id'];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
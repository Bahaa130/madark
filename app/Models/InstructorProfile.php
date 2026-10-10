<?php

namespace App\Models;

use App\Enums\InstructorStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status', 'expertise', 'verified_at'];

    protected function casts(): array
    {
        return [
            'status' => InstructorStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
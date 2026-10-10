<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'user_id', 'type', 'provider', 'provider_account_id',
        'refresh_token', 'access_token', 'expires_at',
        'token_type', 'scope', 'id_token', 'session_state',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
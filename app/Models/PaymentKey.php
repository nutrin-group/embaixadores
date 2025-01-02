<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentKey extends Model
{
    protected $fillable = [
        'user_id',
        'pixKey'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

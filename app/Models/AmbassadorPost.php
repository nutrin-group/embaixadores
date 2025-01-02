<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbassadorPost extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'post_url',
        'post_date',
        'description'
    ];

    protected $casts = [
        'post_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

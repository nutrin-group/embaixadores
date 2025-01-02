<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'coins',
        'quantity',
        'is_active'
    ];

    protected $casts = [
        'coins' => 'integer',
        'quantity' => 'integer',
        'is_active' => 'boolean'
    ];

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->quantity === null) {
            return true;
        }

        return $this->quantity > $this->redemptions()->where('status', 'completed')->count();
    }
}

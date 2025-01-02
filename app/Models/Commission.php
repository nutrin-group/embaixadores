<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'total_amount',
        'locked_amount',
        'available_amount',
        'status',
        'ambassador_id'
    ];

    protected $casts = [
        'amount' => 'float',
        'total_amount' => 'float',
        'locked_amount' => 'float',
        'available_amount' => 'float'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ambassador()
    {
        return $this->belongsTo(User::class, 'ambassador_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

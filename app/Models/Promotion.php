<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    const COMMISSION_TYPE_PERCENTAGE = 'percentage';
    const COMMISSION_TYPE_FIXED = 'fixed';

    protected $fillable = [
        'commission_type',    // 'percentage' ou 'fixed'
        'amount',            // valor da comissão (% ou fixo)
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'amount' => 'float'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                });
            });
    }
}

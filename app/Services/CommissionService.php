<?php

namespace App\Services;

use App\Models\Commission;

class CommissionService
{
    public function getAvailableBalance($ambassadorId)
    {
        return Commission::where('ambassador_id', $ambassadorId)
            ->where('status', '!=', Commission::STATUS_CANCELLED)
            ->sum('available_amount');
    }

    public function getLockedBalance($ambassadorId)
    {
        return Commission::where('ambassador_id', $ambassadorId)
            ->where('status', '!=', Commission::STATUS_CANCELLED)
            ->sum('locked_amount');
    }
}

<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\CommissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReleaseLockedCommission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(CommissionService $commissionService)
    {
        $commissionService->releaseLockedCommission($this->order);
    }
}

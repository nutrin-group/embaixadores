<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Commission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AmbassadorSalesController extends Controller
{
    public function index()
    {
        $sales = Order::with(['commissions' => function ($query) {
            $query->where('ambassador_id', auth()->id())
                ->select('id', 'order_id', 'amount', 'total_amount', 'locked_amount', 'available_amount', 'status');
        }])
        ->whereHas('commissions', function ($query) {
            $query->where('ambassador_id', auth()->id());
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($order) {
            $commission = $order->commissions->first();

            return [
                'id' => $order->id,
                'created_at' => $order->created_at,
                'fullName' => $order->customer_name,
                'amount' => (float) $order->amount,
                'commission_amount' => (float) $commission?->total_amount ?? 0,
                'locked_commission' => (float) $commission?->locked_amount ?? 0,
                'available_commission' => (float) $commission?->available_amount ?? 0,
                'status' => $commission?->status ?? Commission::STATUS_PENDING,
            ];
        });

        // Calcular totais
        $totals = Commission::where('ambassador_id', auth()->id())
            ->selectRaw('
                SUM(total_amount) as total_commission,
                SUM(locked_amount) as total_locked,
                SUM(available_amount) as total_available,
                COUNT(*) as total_sales
            ')
            ->first();

        return Inertia::render('Ambassador/Sales/Index', [
            'sales' => [
                'data' => $sales,
                'totals' => [
                    'commission' => (float) $totals->total_commission ?? 0,
                    'locked' => (float) $totals->total_locked ?? 0,
                    'available' => (float) $totals->total_available ?? 0,
                    'average' => $totals->total_sales > 0
                        ? ($totals->total_commission / $totals->total_sales)
                        : 0
                ]
            ],
            'filters' => request()->only(['startDate', 'endDate'])
        ]);
    }
}

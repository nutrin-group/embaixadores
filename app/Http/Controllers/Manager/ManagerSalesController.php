<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Commission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManagerSalesController extends Controller
{
    public function index(Request $request): Response
    {
        $manager = $request->user();

        $query = Order::query()
            ->whereHas('commissions.ambassador', function ($query) use ($manager) {
                $query->where('manager_referral', $manager->id);
            })
            ->with(['commissions' => function ($query) {
                $query->select('id', 'order_id', 'ambassador_id', 'total_amount');
            }, 'commissions.ambassador' => function ($query) {
                $query->select('id', 'name', 'email', 'type', 'manager_referral');
            }]);

        // Filtro por período
        if ($request->startDate && $request->endDate) {
            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
        } elseif ($request->startDate) {
            $query->whereDate('created_at', '>=', $request->startDate);
        } elseif ($request->endDate) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        // Filtro por status
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filtro por embaixador
        if ($request->ambassador_id) {
            $query->whereHas('commissions', function ($query) use ($request) {
                $query->where('ambassador_id', $request->ambassador_id);
            });
        }

        // Filtro por busca
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('shopify_id', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%");
            });
        }

        $orders = $query->latest()->paginate(10);

        // Debug detalhado
        \Log::info('Query SQL:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        \Log::info('Orders Debug:', [
            'total_count' => $orders->count(),
            'has_data' => $orders->isNotEmpty(),
            'first_order' => $orders->first()?->toArray()
        ]);

        $salesData = $orders->map(function ($order) {
            // Debug para cada ordem
            \Log::info('Processing order:', [
                'id' => $order->id,
                'shopify_id' => $order->shopify_id,
                'customer' => $order->customer_name,
                'amount' => $order->amount,
                'commissions' => $order->commissions->toArray(),
                'ambassador' => $order->commissions->first()?->ambassador?->toArray()
            ]);

            return [
                'id' => $order->id,
                'shopifyId' => $order->shopify_id ?? '#-',
                'fullName' => $order->customer_name ?? '-',
                'user' => [
                    'name' => $order->commissions->first()?->ambassador?->name ?? '-'
                ],
                'amount' => $order->amount ?? 0,
                'commisionAmount' => $order->commissions->first()?->total_amount ?? 0,
                'status' => $order->status,
                'created_at' => $order->created_at
            ];
        })->all();

        \Log::info('Final sales data:', [
            'data_count' => count($salesData),
            'first_item' => $salesData[0] ?? null
        ]);

        return Inertia::render('Manager/Sales', [
            'auth' => [
                'user' => $manager
            ],
            'sales' => [
                'data' => $salesData,
                'total_count' => $orders->total(),
                'total_amount' => $query->sum('amount'),
                'total_commission' => $orders->sum(function ($order) {
                    return $order->commissions->sum('total_amount');
                }),
                'links' => [
                    'prev' => $orders->previousPageUrl(),
                    'next' => $orders->nextPageUrl(),
                ],
                'ambassadors' => $manager->ambassadors()->select('id', 'name')->get()
            ],
            'filters' => $request->only(['startDate', 'endDate', 'status', 'ambassador_id', 'search']),
            'status_options' => [
                'all' => 'Todos',
                'pending' => 'Pendente',
                'paid' => 'Pago',
                'cancelled' => 'Cancelado'
            ],
            'periods' => [
                'all' => 'Todo período',
                'today' => 'Hoje',
                'yesterday' => 'Ontem',
                'last7days' => 'Últimos 7 dias',
                'last30days' => 'Últimos 30 dias',
                'thisMonth' => 'Este mês',
                'lastMonth' => 'Mês passado'
            ]
        ]);
    }
}

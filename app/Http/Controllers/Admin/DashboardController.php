<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Commission;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Estatísticas Gerais
        $statistics = [
            'ambassadors' => [
                'total' => User::where('type', 'ambassador')->count(),
                'active' => User::where('type', 'ambassador')->where('status', 'active')->count(),
            ],
            'managers' => [
                'total' => User::where('type', 'manager')->count(),
                'active' => User::where('type', 'manager')->where('status', 'active')->count(),
            ],
            'sales' => [
                'total' => Order::count(),
                'amount' => Order::sum('amount'),
                'today' => Order::whereDate('created_at', Carbon::today())->count(),
                'month' => Order::whereMonth('created_at', Carbon::now()->month)->count(),
            ],
            'commissions' => [
                'total' => Commission::sum('amount'),
                'pending' => Commission::where('status', Commission::STATUS_PENDING)->sum('amount'),
                'paid' => Commission::where('status', Commission::STATUS_PAID)->sum('amount'),
                'cancelled' => Commission::where('status', Commission::STATUS_CANCELLED)->sum('amount'),
            ],
        ];

        // Top 5 Embaixadores (por vendas)
        $topAmbassadors = User::where('type', 'ambassador')
            ->withCount('sales')
            ->withSum('sales as total_amount', 'amount')
            ->withSum('commissions as total_commission', 'amount')
            ->orderByDesc('sales_count')
            ->take(5)
            ->get()
            ->map(function ($ambassador) {
                return [
                    'id' => $ambassador->id,
                    'name' => $ambassador->name,
                    'sales_count' => $ambassador->sales_count,
                    'total_amount' => $ambassador->total_amount,
                    'total_commission' => $ambassador->total_commission,
                    'status' => $ambassador->status,
                ];
            });

        // Dados de vendas dos últimos 30 dias para o gráfico
        $salesData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Dados de comissões dos últimos 30 dias para o gráfico
        $commissionsData = Commission::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Últimas vendas
        $recentSales = Order::with(['user', 'items'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'date' => $sale->created_at,
                    'ambassador' => $sale->user->name,
                    'amount' => $sale->amount,
                    'items_count' => $sale->items->count(),
                    'status' => $sale->status,
                ];
            });

        // Últimas comissões
        $recentCommissions = Commission::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'date' => $commission->created_at,
                    'ambassador' => $commission->user->name,
                    'amount' => $commission->amount,
                    'status' => $commission->status,
                ];
            });

        // Top Produtos
        $topProducts = OrderItem::with('product')
            ->select(
                'variant_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT order_id) as total_orders')
            )
            ->groupBy('variant_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->variant_id,
                    'name' => $item->product->title ?? 'Produto não encontrado',
                    'image_url' => $item->product->image_url ?? null,
                    'total_quantity' => $item->total_quantity,
                    'total_orders' => $item->total_orders
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'statistics' => $statistics,
            'topAmbassadors' => $topAmbassadors,
            'salesData' => $salesData,
            'commissionsData' => $commissionsData,
            'recentSales' => $recentSales,
            'recentCommissions' => $recentCommissions,
            'topProducts' => $topProducts,
        ]);
    }
}

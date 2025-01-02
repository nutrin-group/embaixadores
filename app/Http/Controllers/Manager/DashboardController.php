<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $manager = Auth::user();
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Ajuste no cálculo de embaixadores para considerar o total acumulado
        $currentMonthAmbassadors = $manager->ambassadors()->count();
        $lastMonthAmbassadors = $manager->ambassadors()
            ->where('created_at', '<', $currentMonth)
            ->count();

        // Calcula a diferença real entre os períodos
        $ambassadorsDiff = $currentMonthAmbassadors - $lastMonthAmbassadors;

        // Ajuste no cálculo de embaixadores ativos
        $currentMonthActiveAmbassadors = $manager->ambassadors()
            ->where('status', 'active')
            ->count();

        // Busca apenas embaixadores vinculados ao manager
        $ambassadorSales = $manager->ambassadors()
            ->with(['orders' => function ($query) use ($currentMonth) {
                $query->where('created_at', '>=', $currentMonth);
            }])
            ->get()
            ->map(function ($ambassador) {
                return [
                    'id' => $ambassador->id,
                    'name' => $ambassador->name,
                    'total_sales' => $ambassador->orders->count(),
                    'total_amount' => $ambassador->orders->sum('amount'),
                    'status' => $ambassador->status === 'active' ? 'Ativo' : 'Inativo'
                ];
            });

        // Ajuste nas estatísticas para calcular corretamente o faturamento e vendas
        $statistics = [
            'total_revenue' => $manager->ambassadors()
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->where('orders.created_at', '>=', $currentMonth)
                ->sum('orders.amount'),

            'last_month_revenue' => $manager->ambassadors()
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->whereBetween('orders.created_at', [$lastMonth, $currentMonth])
                ->sum('orders.amount') ?: 0.01, // Evita divisão por zero

            'total_sales' => $manager->ambassadors()
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->where('orders.created_at', '>=', $currentMonth)
                ->count('orders.id') // Conta apenas o ID das ordens
        ];

        // Busca vendas dos últimos 30 dias apenas dos embaixadores do manager
        $sales = $manager->ambassadors()
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->select('orders.*')
            ->get()
            ->map(function ($sale) {
                return [
                    'amount' => $sale->amount,
                    'created_at' => $sale->created_at
                ];
            });

        // Calcula a diferença de vendas entre os meses
        $lastMonthSales = $manager->ambassadors()
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$lastMonth, $currentMonth])
            ->count('orders.id');

        $currentMonthSales = $statistics['total_sales'];

        // Calcula a diferença percentual entre os meses
        $salesDiff = $lastMonthSales > 0
            ? round((($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100)
            : 0;

        return Inertia::render('Manager/Dashboard', [
            'sales' => $sales,
            'statistics' => $statistics,
            'ambassadorSales' => $ambassadorSales,
            'ambassadorsDiff' => $ambassadorsDiff,
            'currentMonthAmbassadors' => $currentMonthAmbassadors,
            'currentMonthActiveAmbassadors' => $currentMonthActiveAmbassadors,
            'lastMonthAmbassadors' => $lastMonthAmbassadors,
            'salesDiff' => $salesDiff // Agora é um valor percentual
        ]);
    }
}

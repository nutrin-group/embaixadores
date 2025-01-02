<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Commission;

class AmbassadorDashboard extends Controller
{
    public function index()
    {
        $ambassador = auth()->user();
        $commissions = Commission::where('ambassador_id', $ambassador->id)
            ->whereMonth('created_at', now()->month)
            ->get();

        return Inertia::render('Ambassador/Dashboard', [
            'comissaoLiberada' => $commissions->sum('available_amount'),
            'comissaoRetida' => $commissions->sum('locked_amount'),
            'comissaoTotal' => $commissions->sum('total_amount'),
            'comissaoDiff' => $this->calculateCommissionDifference($ambassador->id),
            'totalVendas' => $commissions->count(),
            'percentualMeta' => $this->calculateMonthlyGoalPercentage($ambassador->id),
            'vendasPorDia' => $this->getVendasPorDia($ambassador->id),
            'produtoMaisVendido' => $this->getMostSoldProduct($ambassador->id),
            'embaixador' => $ambassador->name
        ]);
    }

    private function calculateCommissionDifference($ambassadorId)
    {
        $thisMonth = Commission::where('ambassador_id', $ambassadorId)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        $lastMonth = Commission::where('ambassador_id', $ambassadorId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total_amount');

        if ($lastMonth == 0) return null;
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function calculateMonthlyGoalPercentage($ambassadorId)
    {
        // Mantendo o método existente
        return 75; // Exemplo - mantenha sua lógica atual
    }

    private function getVendasPorDia($ambassadorId)
    {
        // Mantendo o método existente
        return []; // Exemplo - mantenha sua lógica atual
    }

    private function getMostSoldProduct($ambassadorId)
    {
        // Mantendo o método existente
        return null; // Exemplo - mantenha sua lógica atual
    }
}

<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Commission;

class SaleController extends Controller
{
    public function index(Request $request): Response
    {
        $ambassador = auth()->user();
        $query = Commission::with('order')
            ->where('ambassador_id', $ambassador->id);

        // Aplicar filtros de data se existirem
        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        $sales = $query->paginate(10);

        return Inertia::render('Ambassador/Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['startDate', 'endDate', 'status']),
        ]);
    }
}

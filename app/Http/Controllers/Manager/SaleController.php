<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;


class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query()
            ->whereHas('ambassador', function ($query) {
                $query->where('manager_id', auth()->id());
            })
            ->with(['ambassador', 'customer'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('ambassador', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            });

        $sales = $query->latest()->paginate(10);

        $statistics = [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('total_amount'),
            'average_ticket' => $query->avg('total_amount'),
            'sales_this_month' => $query->whereMonth('created_at', now()->month)->count(),
        ];

        return Inertia::render('Manager/Sales/Index', [
            'sales' => $sales,
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'date_from', 'date_to'])
        ]);
    }
}

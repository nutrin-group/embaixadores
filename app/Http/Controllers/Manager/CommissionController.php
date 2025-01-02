<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Commission::query()
            ->whereHas('ambassador', function ($query) {
                $query->where('manager_id', auth()->id());
            })
            ->with(['ambassador', 'sale'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            });

        $commissions = $query->latest()->paginate(10);

        $statistics = [
            'total_commissions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'pending_amount' => $query->where('status', 'pending')->sum('amount'),
            'paid_amount' => $query->where('status', 'paid')->sum('amount'),
        ];

        return Inertia::render('Manager/Commissions/Index', [
            'commissions' => $commissions,
            'statistics' => $statistics,
            'filters' => $request->only(['status', 'date_from', 'date_to'])
        ]);
    }
}

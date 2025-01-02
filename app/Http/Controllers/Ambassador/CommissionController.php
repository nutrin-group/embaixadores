<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Commission;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = Commission::where('user_id', $request->user()->id)
            ->with('sale')
            ->latest()
            ->paginate(10);

        return Inertia::render('Ambassador/Commissions/Index', [
            'commissions' => $commissions
        ]);
    }
}

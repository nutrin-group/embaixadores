<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Commission;

class CommissionController extends Controller
{
    public function index()
    {
        $commissions = Commission::with('user')
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Commissions/Index', [
            'commissions' => $commissions
        ]);
    }
}

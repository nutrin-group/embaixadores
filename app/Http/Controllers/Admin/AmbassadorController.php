<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AmbassadorController extends Controller
{
    public function index()
    {
        $ambassadors = User::where('type', User::TYPE_AMBASSADOR)
            ->with('manager')
            ->select([
                'id',
                'name',
                'email',
                'social_network',
                'social_media_username',
                'status',
                'manager_referral'
            ])
            ->get();


        return Inertia::render('Admin/Ambassadors/Index', [
            'ambassadors' => $ambassadors,
            'auth' => [
                'user' => auth()->user()
            ]
        ]);
    }

    public function block(User $ambassador)
    {
        if ($ambassador->type !== 'ambassador') {
            abort(403);
        }

        $ambassador->block();

        return redirect()->back()->with('success', 'Embaixador bloqueado com sucesso.');
    }

    public function activate(User $ambassador)
    {
        if ($ambassador->type !== 'ambassador') {
            abort(403);
        }

        $ambassador->activate();

        return redirect()->back()->with('success', 'Embaixador ativado com sucesso.');
    }
}

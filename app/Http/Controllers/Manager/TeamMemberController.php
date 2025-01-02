<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamMemberController extends Controller
{
    public function show(User $ambassador)
    {
        // Verifica se o embaixador pertence ao manager logado
        if ($ambassador->manager_referral !== auth()->id()) {
            abort(403);
        }

        $ambassador->load([
            'orders' => function ($query) {
                $query->select('user_id')
                    ->selectRaw('COUNT(*) as total_sales')
                    ->selectRaw('SUM(amount) as total_amount')
                    ->groupBy('user_id');
            },
            'comissions' => function ($query) {
                $query->select('user_id')
                    ->selectRaw('SUM(amount) as total_commission')
                    ->groupBy('user_id');
            },
            'posts' => function ($query) {
                $query->orderBy('post_date', 'desc')
                    ->limit(5);
            }
        ]);

        $data = [
            'id' => $ambassador->id,
            'name' => $ambassador->name,
            'email' => $ambassador->email,
            'instagram_id' => $ambassador->instagram_id,
            'status' => $ambassador->status,
            'total_sales' => $ambassador->orders->first()?->total_sales ?? 0,
            'total_amount' => $ambassador->orders->first()?->total_amount ?? 0,
            'total_commission' => $ambassador->comissions->first()?->total_commission ?? 0,
            'recent_posts' => $ambassador->posts->map(fn($post) => [
                'type' => $post->type,
                'url' => $post->post_url,
                'date' => $post->post_date->format('d/m/Y'),
                'description' => $post->description
            ])
        ];

        return Inertia::render('Manager/TeamMember', [
            'ambassador' => $data
        ]);
    }

    public function updateStatus(Request $request, User $ambassador)
    {
        // Verifica se o embaixador pertence ao manager logado
        if ($ambassador->manager_referral !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,active,blocked']
        ]);

        $ambassador->update($validated);

        return back();
    }
}

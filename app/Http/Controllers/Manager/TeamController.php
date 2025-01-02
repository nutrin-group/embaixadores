<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Comission;
use App\Models\Promocode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index()
    {
        $manager = auth()->user();

        $ambassadors = User::where('manager_referral', $manager->id)
            ->where('type', 'ambassador')
            ->with([
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
                'promocodes',
                'posts' => function ($query) {
                    $query->orderBy('post_date', 'desc')
                        ->limit(5);
                }
            ])
            ->get()
            ->map(function ($ambassador) {
                return [
                    'id' => $ambassador->id,
                    'name' => $ambassador->name,
                    'email' => $ambassador->email,
                    'instagram_id' => $ambassador->instagram_id,
                    'status' => $ambassador->status,
                    'total_sales' => $ambassador->orders->first()?->total_sales ?? 0,
                    'total_amount' => $ambassador->orders->first()?->total_amount ?? 0,
                    'total_commission' => $ambassador->comissions->first()?->total_commission ?? 0,
                    'promocode' => $ambassador->promocodes->first()?->name,
                    'recent_posts' => $ambassador->posts->map(fn($post) => [
                        'type' => $post->type,
                        'url' => $post->post_url,
                        'date' => $post->post_date->format('d/m/Y'),
                        'description' => $post->description
                    ])
                ];
            });

        return Inertia::render('Manager/Team', [
            'ambassadors' => $ambassadors
        ]);
    }
}

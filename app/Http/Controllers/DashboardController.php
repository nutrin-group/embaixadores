<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Order;
use App\Models\Commission;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        switch ($user->userType) {
            case User::TYPE_ADMIN:
                return $this->adminDashboard();
            case User::TYPE_MANAGER:
                return $this->managerDashboard($user);
            case User::TYPE_AMBASSADOR:
                return $this->ambassadorDashboard($user);
            default:
                return redirect()->route('login');
        }
    }

    private function adminDashboard()
    {
        $globalStats = [
            'totalSales' => Order::sum('amount'),
            'totalCommissions' => Commission::sum('amount'),
            'activeManagers' => User::where('userType', User::TYPE_MANAGER)->count(),
            'activeAmbassadors' => User::where('userType', User::TYPE_AMBASSADOR)->count()
        ];

        // Busca os embaixadores com seus produtos mais vendidos
        $ambassadorsTopProducts = User::where('userType', User::TYPE_AMBASSADOR)
            ->with(['orders' => function ($query) {
                $query->with('items.product');
            }])
            ->withCount(['orders as total_sales' => function ($query) {
                $query->where('status', Order::STATUS_PAID);
            }])
            ->withSum(['orders as total_amount' => function ($query) {
                $query->where('status', Order::STATUS_PAID);
            }], 'amount')
            ->get()
            ->map(function ($ambassador) {
                // Usando array associativo em vez de Collection
                $productsSold = [];

                foreach ($ambassador->orders as $order) {
                    foreach ($order->items as $item) {
                        $productId = $item->product->id;
                        if (!isset($productsSold[$productId])) {
                            $productsSold[$productId] = [
                                'product' => $item->product,
                                'quantity' => 0
                            ];
                        }
                        $productsSold[$productId]['quantity'] += $item->quantity;
                    }
                }

                // Encontrar o produto mais vendido
                $topProduct = null;
                $maxQuantity = 0;
                foreach ($productsSold as $product) {
                    if ($product['quantity'] > $maxQuantity) {
                        $maxQuantity = $product['quantity'];
                        $topProduct = $product;
                    }
                }

                return [
                    'id' => $ambassador->id,
                    'name' => $ambassador->name,
                    'total_sales' => $ambassador->total_sales,
                    'total_amount' => $ambassador->total_amount,
                    'top_product' => $topProduct ? [
                        'title' => $topProduct['product']->title,
                        'quantity' => $topProduct['quantity'],
                        'image_url' => $topProduct['product']->image_url
                    ] : null
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $globalStats,
            'ambassadorsTopProducts' => $ambassadorsTopProducts
        ]);
    }

    private function managerDashboard(User $user)
    {
        $teamStats = [
            'totalSales' => Order::whereHas('user', function ($query) use ($user) {
                $query->where('manager_referral', $user->id);
            })->sum('amount'),
            'activeAmbassadors' => User::where('manager_referral', $user->id)->count()
        ];

        return Inertia::render('Manager/Dashboard', [
            'stats' => $teamStats
        ]);
    }

    private function ambassadorDashboard(User $user)
    {
        $personalStats = [
            'totalSales' => Order::where('user_id', $user->id)->sum('amount'),
            'availableBalance' => $user->balance,
            'totalCommissions' => Commission::where('user_id', $user->id)->sum('amount')
        ];

        return Inertia::render('Ambassador/Dashboard', [
            'stats' => $personalStats
        ]);
    }
}

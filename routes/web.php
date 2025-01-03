<?php

use Illuminate\Support\Facades\Log;
use App\Http\Middleware\{
    AdminMiddleware,
    ManagerMiddleware,
    AmbassadorMiddleware
};

use App\Http\Controllers\Admin\{
    AmbassadorController as AdminAmbassadorController,
    CommissionController as AdminCommissionController,
    ManagerController,
    ReportController,
    SettingController,
    WithdrawalController as AdminWithdrawalController
};

use App\Http\Controllers\Ambassador\{
    BriefingController,
    CommissionController as AmbassadorCommissionController,
    LinkController,
    SaleController as AmbassadorSaleController,
    WithdrawalController as AmbassadorWithdrawalController
};

use App\Http\Controllers\Auth\AmbassadorRegisterController;
use App\Http\Controllers\Admin\AmbassadorController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AmbassadorPostController;
use App\Http\Controllers\Manager\TeamController;
use App\Http\Controllers\Manager\SaleController as ManagerSaleController;
use App\Http\Controllers\Manager\CommissionController as ManagerCommissionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ambassador\SaleController;
use App\Http\Controllers\Ambassador\WithdrawalController;
use Inertia\Inertia;
use App\Http\Controllers\Manager\TeamMemberController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\Ambassador\AmbassadorDashboard;
use App\Http\Controllers\Manager\ManagerSalesController;
use App\Http\Controllers\Ambassador\RewardController;
use App\Http\Controllers\OpenPixController;
use Illuminate\Support\Facades\DB;
use App\Models\Promocode;
use App\Services\ShopifyDiscountService;

Route::get('/', function () {
    if (auth()->check()) {
        $userType = auth()->user()->type;

        return match($userType) {
            'admin' => redirect('/admin/dashboard'),
            'manager' => redirect('/manager/dashboard'),
            'ambassador' => redirect('/ambassador/dashboard'),
            default => redirect('/login')
        };
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('register', fn () => Inertia::render('Auth/Register'))->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('register/ambassador', fn () => Inertia::render('Auth/AmbassadorRegister'))
        ->name('ambassador.register');
    Route::post('register/ambassador', [AmbassadorRegisterController::class, 'store']);

    Route::get('login', fn () => Inertia::render('Auth/Login'))->name('login');

    Route::get('/onboarding', fn () => Inertia::render('Auth/Register/OnboardingSteps'))
        ->name('onboarding');
});

Route::get('/debug', function () {
    return response()->json([
        'logged_in' => auth()->check(),
        'user' => auth()->user(),
        'user_type' => auth()->user()?->type ?? 'not logged in'
    ]);
});

Route::get('/debug-admin', function() {
    return response()->json([
        'user' => auth()->user(),
        'is_admin' => auth()->user()?->type === 'admin',
        'session' => session()->all()
    ]);
})->middleware(['auth']);

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::middleware(['web', AdminMiddleware::class])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
                    ->name('dashboard');
                Route::get('/statistics', fn () => Inertia::render('Admin/Statistics'))->name('statistics');
                Route::get('/reports', fn () => Inertia::render('Admin/Reports'))->name('reports');
                Route::get('/settings', fn () => Inertia::render('Admin/Settings'))->name('settings');
                Route::resource('managers', ManagerController::class);

                Route::get('/ambassadors', [AdminAmbassadorController::class, 'index'])->name('ambassadors');

                Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
                Route::post('/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
                Route::post('/withdrawals/{id}/cancel', [AdminWithdrawalController::class, 'cancel'])->name('withdrawals.cancel');

                Route::put('/ambassadors/{ambassador}/block', function (\App\Models\User $ambassador) {
                    if ($ambassador->type !== 'ambassador') {
                        abort(403);
                    }
                    $ambassador->block();
                    return redirect()->back()->with('success', 'Embaixador bloqueado com sucesso.');
                })->name('ambassadors.block');

                Route::put('/ambassadors/{ambassador}/activate', function (\App\Models\User $ambassador) {
                    if ($ambassador->type !== 'ambassador') {
                        abort(403);
                    }
                    $ambassador->activate();
                    return redirect()->back()->with('success', 'Embaixador ativado com sucesso.');
                })->name('ambassadors.activate');

                Route::put('/ambassadors/{ambassador}/approve', function (\App\Models\User $ambassador) {
                    if ($ambassador->type !== 'ambassador') {
                        abort(403);
                    }

                    DB::transaction(function () use ($ambassador) {
                        try {
                            // Ativa o embaixador
                            $ambassador->activate();

                            // Ativa o promocode e cria na Shopify
                            $promocode = Promocode::where('user_id', $ambassador->id)->first();
                            if ($promocode) {
                                // Cria o cupom na Shopify
                                $shopifyService = app(ShopifyDiscountService::class);
                                $shopifyService->createDiscount($promocode->name);

                                // Só ativa no banco se a criação na Shopify foi bem sucedida
                                $promocode->update(['is_active' => true]);
                            }

                            return redirect()->back()->with('success', 'Embaixador e cupom aprovados com sucesso.');
                        } catch (\Exception $e) {
                            DB::rollBack();

                            // Log do erro para debug
                            Log::error('Erro ao aprovar embaixador: ' . $e->getMessage(), [
                                'ambassador_id' => $ambassador->id,
                                'promocode' => $promocode->name ?? null
                            ]);

                            throw new \Exception('Erro ao criar cupom na Shopify. Por favor, tente novamente.');
                        }
                    });
                })->name('ambassadors.approve');

                Route::get('/commissions', fn () => Inertia::render('Admin/Commissions'))->name('commissions');
            });

        Route::middleware(['web', ManagerMiddleware::class])
            ->prefix('manager')
            ->name('manager.')
            ->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])
                    ->name('dashboard');
                Route::get('/team', [TeamController::class, 'index'])->name('team');
                Route::get('/sales', fn () => Inertia::render('Manager/Sales'))->name('sales');
                Route::get('/commissions', fn () => Inertia::render('Manager/Commissions'))->name('commissions');
                Route::get('/team/{ambassador}', [TeamMemberController::class, 'show'])->name('manager.team.show');
                Route::put('/team/{ambassador}/status', [TeamMemberController::class, 'updateStatus'])->name('manager.team.status');

                Route::get('/sales', [ManagerSalesController::class, 'index'])->name('sales');
            });

        Route::middleware(['web', AmbassadorMiddleware::class])
            ->prefix('ambassador')
            ->name('ambassador.')
            ->group(function () {
                Route::get('/dashboard', [AmbassadorDashboard::class, 'index'])
                    ->name('dashboard');
                Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
                Route::get('/briefings', fn () => Inertia::render('Ambassador/Briefings'))->name('briefings');
                Route::get('/commissions', fn () => Inertia::render('Ambassador/Commissions'))->name('commissions');
                Route::get('/links', fn () => Inertia::render('Ambassador/Links'))->name('links');
                Route::resource('posts', AmbassadorPostController::class)->except(['show', 'edit', 'update']);
                Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
                Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
                Route::get('/rewards', [RewardController::class, 'index'])->name('rewards');
                Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
            });

        Route::get('/profile', fn () => Inertia::render('Profile/Edit'))->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/discounts', [DiscountController::class, 'create'])->name('discounts.create');
    });

Route::prefix('shopify')->name('shopify.')->group(function () {
    Route::post('/orders/new', [ShopifyController::class, 'newOrder'])
        ->name('orders.new')
        ->withoutMiddleware(['csrf']);

    Route::post('/orders/process', [ShopifyController::class, 'processOrder'])
        ->name('orders.process')
        ->middleware(['auth', 'verified']);

    Route::post('/products/get-image', [ShopifyController::class, 'getProductImage'])
        ->name('products.get-image')
        ->middleware(['throttle:60,1']);

    Route::post('/discounts/create', [ShopifyController::class, 'createDiscount'])
        ->name('discounts.create')
        ->middleware(['auth', 'verified']);
});

Route::post('/check-coupon', [PromoCodeController::class, 'checkAvailability'])
    ->name('check-coupon')
    ->middleware(['throttle:60,1']);

Route::get('/ambassador/pending', function () {
    return Inertia::render('Auth/PendingApproval');
})->name('ambassador.pending');

Route::post('/openpix/withdraw', [OpenPixController::class, 'withdraw'])->name('openpix.withdraw');

require __DIR__.'/auth.php';

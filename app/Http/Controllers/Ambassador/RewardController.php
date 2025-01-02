<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RewardController extends Controller
{
    public function index()
    {
        // Carregar todas as recompensas ativas
        $rewards = Reward::where('is_active', true)
            ->get()
            ->map(function ($reward) {
                return [
                    'id' => $reward->id,
                    'name' => $reward->name,
                    'description' => $reward->description,
                    'image' => $reward->image,
                    'coins' => $reward->coins,
                    'quantity' => $reward->quantity,
                ];
            });

        return Inertia::render('Ambassador/Rewards', [
            'rewards' => $rewards,
            'user_coins' => auth()->user()->coin_balance
        ]);
    }

    public function redeem(Request $request, Reward $reward)
    {
        $user = auth()->user();
        $currentBalance = $user->coin_balance;

        if ($currentBalance < $reward->coins) {
            return response()->json([
                'message' => 'Saldo insuficiente de moedas'
            ], 422);
        }

        // Registra o gasto das moedas
        $user->coins()->create([
            'amount' => -$reward->coins,
            'description' => "Resgate de recompensa: {$reward->name}"
        ]);

        // Cria o registro de resgate
        $user->rewardRedemptions()->create([
            'reward_id' => $reward->id,
            'coins_spent' => $reward->coins,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Resgate realizado com sucesso',
            'user_coins' => $user->fresh()->coin_balance
        ]);
    }
}

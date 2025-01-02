<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Withdrawal;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index()
    {
        $ambassador = auth()->user();

        return Inertia::render('Ambassador/Withdrawals/Index', [
            'availableBalance' => $this->commissionService->getAvailableBalance($ambassador->id),
            'lockedBalance' => $this->commissionService->getLockedBalance($ambassador->id),
            'lastWithdrawalDate' => $ambassador->withdrawals()
                ->where('status', 'completed')
                ->latest()
                ->first()?->created_at,
            'withdrawals' => $ambassador->withdrawals()->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:50']
        ]);

        $ambassador = auth()->user();

        try {
            DB::beginTransaction();

            // Verifica saldo disponível
            $availableBalance = $this->commissionService->getAvailableBalance($ambassador->id);

            if ($request->amount > $availableBalance) {
                DB::rollBack();
                return back()->withErrors(['amount' => 'Saldo insuficiente para realizar o saque.']);
            }

            // Verifica se já existe saque pendente no mês
            $hasPendingWithdrawal = Withdrawal::where('user_id', $ambassador->id)
                ->whereMonth('created_at', now()->month)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();

            if ($hasPendingWithdrawal) {
                DB::rollBack();
                return back()->withErrors(['amount' => 'Já existe um saque pendente para este mês.']);
            }

            // Cria o saque
            $withdrawal = Withdrawal::create([
                'user_id' => $ambassador->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'processed_at' => null
            ]);

            if (!$withdrawal) {
                throw new \Exception('Erro ao criar registro de saque');
            }

            // Atualiza o saldo disponível nas comissões
            $commissions = Commission::where('ambassador_id', $ambassador->id)
                ->where('status', '!=', 'cancelled')
                ->where('available_amount', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingAmount = $request->amount;

            foreach ($commissions as $commission) {
                if ($remainingAmount <= 0) break;

                $amountToDeduct = min($commission->available_amount, $remainingAmount);
                $commission->available_amount -= $amountToDeduct;
                $commission->save();

                $remainingAmount -= $amountToDeduct;
            }

            DB::commit();
            return redirect()->back()->with('status', 'Solicitação de saque realizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar saque: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao processar saque. Tente novamente.']);
        }
    }
}

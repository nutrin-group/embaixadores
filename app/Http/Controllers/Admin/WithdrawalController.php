<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with(['user.manager', 'user.paymentKey'])
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions = Transaction::with(['user.manager', 'user.paymentKey'])
            ->whereIn('type', [Transaction::TYPE_WITHDRAWAL, Transaction::TYPE_WITHDRAWAL_REFUND])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'data' => $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i:s') : '-',
                    'user' => [
                        'name' => $transaction->user?->name ?? 'Usuário removido',
                        'pix_key' => $transaction->user?->paymentKey?->pixKey ?? '-',
                        'manager' => [
                            'name' => $transaction->user?->manager?->name ?? '-'
                        ]
                    ],
                    'amount' => (float)$transaction->amount,
                    'type' => $this->translateTransactionType($transaction->type),
                    'status' => $this->translateStatus($transaction->status)
                ];
            })->values()->toArray();

        $mappedWithdrawals = $withdrawals->map(function ($withdrawal) {
            return [
                'id' => $withdrawal->id,
                'data' => $withdrawal->created_at ? $withdrawal->created_at->format('d/m/Y H:i:s') : '-',
                'user' => [
                    'name' => $withdrawal->user?->name ?? 'Usuário removido',
                    'pix_key' => $withdrawal->user?->paymentKey?->pixKey ?? '-',
                    'manager' => [
                        'name' => $withdrawal->user?->manager?->name ?? '-'
                    ]
                ],
                'valor' => (float)$withdrawal->amount,
                'status' => $this->translateStatus($withdrawal->status),
                'user_id' => $withdrawal->user_id,
                'acoes' => [
                    'can_approve' => $withdrawal->status === Withdrawal::STATUS_PENDING,
                    'can_cancel' => in_array($withdrawal->status, [Withdrawal::STATUS_PENDING]),
                ]
            ];
        })->values()->toArray();

        $statistics = [
            'totalPendente' => (float)$withdrawals->where('status', Withdrawal::STATUS_PENDING)->sum('amount'),
            'saquesPendentes' => $withdrawals->where('status', Withdrawal::STATUS_PENDING)->count(),
            'ultimoProcessamento' => $transactions[0]['data'] ?? 'Nenhum'
        ];

        return Inertia::render('Admin/Withdrawals', [
            'auth' => [
                'user' => auth()->user()
            ],
            'withdrawals' => $mappedWithdrawals,
            'transactions' => $transactions,
            'statistics' => $statistics
        ]);
    }

    public function cancel($id)
    {
        try {
            DB::beginTransaction();

            $withdrawal = Withdrawal::with('user')->findOrFail($id);

            // Atualiza o status do saque
            $withdrawal->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            // Devolve o saldo para o usuário
            if ($withdrawal->user) {
                $withdrawal->user->balance += $withdrawal->amount;
                $withdrawal->user->save();

                // Registra a transação de estorno
                Transaction::create([
                    'user_id' => $withdrawal->user_id,
                    'amount' => $withdrawal->amount,
                    'type' => Transaction::TYPE_WITHDRAWAL_REFUND,
                    'status' => 'completed',
                    'description' => 'Estorno de saque cancelado #' . $withdrawal->id
                ]);
            }

            DB::commit();

            return back()->with('success', 'Saque cancelado e saldo devolvido com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar saque: ' . $e->getMessage(), [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao cancelar saque: ' . $e->getMessage());
        }
    }

    private function translateStatus($status)
    {
        return match($status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'cancelled' => 'Cancelado',
            'completed' => 'Concluído',
            default => ucfirst($status)
        };
    }

    private function translateTransactionType($type)
    {
        return match($type) {
            Transaction::TYPE_WITHDRAWAL => 'Saque',
            Transaction::TYPE_WITHDRAWAL_REFUND => 'Estorno',
            default => ucfirst($type)
        };
    }
}

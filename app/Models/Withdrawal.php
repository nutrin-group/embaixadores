<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Withdrawal extends Model
{
    protected $table = 'withdrawals';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function cancel()
    {
        try {
            Log::info('Iniciando cancelamento do saque', [
                'withdrawal_id' => $this->id,
                'user_id' => $this->user_id,
                'amount' => $this->amount
            ]);

            DB::beginTransaction();

            // 1. Verifica se o saque pode ser cancelado
            if (!$this->isPending()) {
                throw new \Exception('Este saque não pode ser cancelado pois não está pendente.');
            }

            // 2. Verifica se o usuário existe
            if (!$this->user) {
                throw new \Exception('Usuário não encontrado.');
            }

            // 3. Cria a transação de estorno
            $transaction = Transaction::create([
                'user_id' => $this->user_id,
                'withdrawal_id' => $this->id,
                'amount' => $this->amount, // Valor positivo pois é um estorno
                'type' => Transaction::TYPE_WITHDRAWAL_REFUND,
                'description' => 'Estorno de saque cancelado',
                'status' => 'completed'
            ]);

            Log::info('Transação de estorno criada', ['transaction_id' => $transaction->id]);

            // 4. Atualiza o saldo do usuário
            $oldBalance = $this->user->balance;
            $this->user->increment('balance', $this->amount);

            Log::info('Saldo do usuário atualizado', [
                'user_id' => $this->user_id,
                'old_balance' => $oldBalance,
                'amount_added' => $this->amount,
                'new_balance' => $this->user->balance
            ]);

            // 5. Atualiza o status do saque
            $this->status = self::STATUS_CANCELLED;
            $this->processed_at = now();
            $this->save();

            Log::info('Saque cancelado com sucesso', [
                'withdrawal_id' => $this->id,
                'new_status' => $this->status
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Saque cancelado e valor estornado com sucesso',
                'transaction' => $transaction
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao cancelar saque', [
                'withdrawal_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao cancelar saque: ' . $e->getMessage()
            ];
        }
    }

    protected function createTransaction(string $type, string $description = null): Transaction
    {
        return Transaction::create([
            'user_id' => $this->user_id,
            'withdrawal_id' => $this->id,
            'amount' => $type === Transaction::TYPE_WITHDRAWAL_REFUND ? $this->amount : -$this->amount,
            'type' => $type,
            'description' => $description,
            'status' => 'completed'
        ]);
    }
}

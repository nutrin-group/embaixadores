<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Promocode;
use App\Models\User;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;
use App\Services\GetShopifyImage;
use App\Models\Coin;

class ProcessShopifyOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $orderData;

    public function __construct(array $orderData)
    {
        $this->orderData = $orderData;
    }

    public function handle(CommissionService $commissionService): void
    {
        try {
            DB::beginTransaction();

            // Verifica se o pedido já foi processado
            if (Order::where('shopify_id', $this->orderData['id'])->exists()) {
                Log::info('Pedido já processado', ['order_id' => $this->orderData['id']]);
                return;
            }

            // Procura por códigos de desconto
            $discountCode = $this->extractDiscountCode();

            if (!$discountCode) {
                Log::info('Nenhum código de desconto encontrado', ['order_id' => $this->orderData['id']]);
                return;
            }

            // Encontra o promocode ativo usando a coluna 'name'
            $promocode = Promocode::where('name', $discountCode)
                ->where('is_active', true)
                ->first();

            if (!$promocode) {
                Log::warning('Código promocional não encontrado ou inativo', [
                    'code' => $discountCode,
                    'order_id' => $this->orderData['id']
                ]);
                return;
            }

            // Calcula a comissão
            $totalPrice = floatval($this->orderData['total_price']);
            $commission = $commissionService->calculateCommission($totalPrice);

            Log::info('Comissão calculada', [
                'total_price' => $totalPrice,
                'commission' => $commission,
                'ambassador_id' => $promocode->user_id
            ]);

            // Cria o registro do pedido
            $order = Order::create([
                'shopify_id' => $this->orderData['id'],
                'user_id' => $promocode->user_id,
                'amount' => $totalPrice,
                'total_commission' => $commission['total'],
                'locked_commission' => $commission['locked'],
                'available_commission' => $commission['available'],
                'status' => 'pending',
                'customer_name' => $this->orderData['customer']['first_name'] . ' ' . $this->orderData['customer']['last_name']
            ]);

            // Atualiza os saldos do usuário
            $promocode->user->update([
                'balance' => DB::raw("balance + {$commission['available']}"),
                'locked_balance' => DB::raw("locked_balance + {$commission['locked']}")
            ]);

            // Registra os itens do pedido
            foreach ($this->orderData['line_items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity']
                ]);
            }

            // Adiciona moedas (se necessário)
            $coins = $totalPrice * 0.10;
            Coin::create([
                'user_id' => $promocode->user_id,
                'amount' => $coins,
                'description' => 'Comissão do pedido ' . $this->orderData['id']
            ]);

            DB::commit();

            Log::info('Pedido processado com sucesso', [
                'order_id' => $order->id,
                'ambassador_id' => $promocode->user_id,
                'commission' => $commission
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pedido Shopify', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $this->orderData
            ]);
            throw $e;
        }
    }

    private function extractDiscountCode(): ?string
    {
        if (!empty($this->orderData['discount_codes'])) {
            return strtoupper($this->orderData['discount_codes'][0]['code']);
        }

        if (!empty($this->orderData['discount_applications'])) {
            foreach ($this->orderData['discount_applications'] as $discount) {
                if (isset($discount['code'])) {
                    return strtoupper($discount['code']);
                }
                if (isset($discount['title'])) {
                    return strtoupper($discount['title']);
                }
            }
        }

        return null;
    }
}

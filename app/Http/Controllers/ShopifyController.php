<?php

namespace App\Http\Controllers;

use App\Services\ShopifyDiscountService;
use Illuminate\Http\Request;
use App\Models\Promocode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessShopifyOrder;
use App\Services\GetShopifyImage;
use App\Models\Order;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;
use App\Models\Coin;
use App\Models\Commission;
use App\Models\User;

class ShopifyController extends Controller
{
    private ShopifyDiscountService $shopifyService;
    private GetShopifyImage $getShopifyImage;

    public function __construct(ShopifyDiscountService $shopifyService, GetShopifyImage $getShopifyImage)
    {
        $this->shopifyService = $shopifyService;
        $this->getShopifyImage = $getShopifyImage;
    }

    public function createDiscount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:promocodes,name',
            'value' => 'required|numeric|min:1|max:100',
            'title' => 'required|string',
            'ends_at' => 'nullable|date|after:now'
        ]);

        $discountDetails = [
            'title' => $validated['title'],
            'value_type' => 'percentage',
            'value' => -abs($validated['value']), // Ensure negative value for discount
            'code' => strtoupper($validated['code']),
            'starts_at' => now()->toIso8601String(),
            'ends_at' => $validated['ends_at'] ? date('c', strtotime($validated['ends_at'])) : null
        ];

        $result = $this->shopifyService->createDiscount($discountDetails);

        if ($result['success']) {
            // Save promocode in our database
            Promocode::create([
                'name' => $discountDetails['code'],
                'manager_id' => Auth::id(),
                'user_id' => $request->user_id ?? Auth::id()
            ]);

            return back()->with('success', 'Discount code created successfully');
        }

        return back()->with('error', 'Failed to create discount code: ' . ($result['error'] ?? 'Unknown error'));
    }

    public function newOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            $shopifyOrder = $request->all();

            // Criar o pedido primeiro
            $order = Order::create([
                'shopify_id' => $shopifyOrder['name'],
                'amount' => $shopifyOrder['total_price'],
                'customer_name' => $shopifyOrder['customer']['first_name'] . ' ' . $shopifyOrder['customer']['last_name'],
                'customer_email' => $shopifyOrder['customer']['email'],
                'customer_phone' => $shopifyOrder['customer']['phone'] ?? null,
                'status' => Order::STATUS_PAID,
                'user_id' => Promocode::where('name', $shopifyOrder['discount_codes'][0]['code'])->first()->user_id,
                'city' => $shopifyOrder['customer']['default_address']['city'],
                'state' => $shopifyOrder['customer']['default_address']['province_code'],
            ]);

            // Procurar o código promocional usado
            $discountCode = collect($shopifyOrder['discount_codes'])->first();
            if ($discountCode) {
                $promocode = Promocode::where('name', $discountCode['code'])->first();

                if ($promocode) {
                    $commissionService = new CommissionService();
                    $commission = $commissionService->calculateCommission($order);

                    // Criar a comissão usando o ID interno do pedido
                    Commission::create([
                        'order_id' => $order->id,
                        'ambassador_id' => $promocode->user_id,
                        'user_id' => User::find($promocode->user_id)->manager_referral,
                        'amount' => $commission['total_commission'],
                        'total_amount' => $commission['total_commission'],
                        'locked_amount' => $commission['locked_commission'],
                        'available_amount' => $commission['available_commission'],
                        'status' => Commission::STATUS_PENDING
                    ]);
                }
            Coin::create([
                    'user_id' => $promocode->user_id,
                    'amount' => $shopifyOrder['total_price']*0.1,
                    'description' => 'Comissão do pedido ' . $order->id,

                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Pedido processado com sucesso'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pedido Shopify: ' . $e->getMessage(), [
                'order' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erro ao processar pedido'], 500);
        }
    }

    // public function processOrder(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $order = Order::where('shopify_id', $request->order_id)->firstOrFail();
    //         $order->status = Order::STATUS_PAID;
    //         $order->save();

    //         // Atualizar comissões relacionadas
    //         foreach ($order->commissions as $commission) {
    //             // Quando o pedido é pago, o valor bloqueado continua bloqueado
    //             // até aprovação manual do admin
    //             $commission->status = Commission::STATUS_PENDING;
    //             $commission->save();
    //         }

    //         DB::commit();
    //         return response()->json(['message' => 'Pedido atualizado com sucesso'], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Erro ao atualizar status do pedido: ' . $e->getMessage());
    //         return response()->json(['error' => 'Erro ao atualizar pedido'], 500);
    //     }
    // }

    public function webhook(Request $request)
    {
        try {
            // Log do payload recebido
            Log::info('Webhook Shopify recebido', [
                'raw_content' => $request->getContent(),
                'headers' => $request->headers->all()
            ]);

            // Decodifica o JSON
            $orderData = json_decode($request->getContent(), true);

            // Valida se o JSON foi decodificado corretamente
            if (!$orderData) {
                Log::error('Erro ao decodificar JSON do webhook', [
                    'error' => json_last_error_msg(),
                    'raw_content' => $request->getContent()
                ]);
                return response()->json(['error' => 'Invalid JSON'], 400);
            }

            // Valida se tem o ID do pedido
            if (!isset($orderData['id'])) {
                Log::error('ID do pedido não encontrado no webhook', [
                    'order_data' => $orderData
                ]);
                return response()->json(['error' => 'Order ID not found'], 400);
            }

            // Verifica se o pedido já existe
            if (Order::where('shopifyId', $orderData['id'])->exists()) {
                Log::info('Pedido já processado anteriormente', [
                    'order_id' => $orderData['id']
                ]);
                return response()->json(['message' => 'Order already processed']);
            }

            // Dispara o job para processar o pedido
            ProcessShopifyOrder::dispatch($orderData);

            Log::info('Job de processamento do pedido Shopify disparado', [
                'order_id' => $orderData['id']
            ]);

            return response()->json(['message' => 'Webhook received and processing']);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Shopify', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function verifyWebhook(Request $request): bool
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_WEBHOOK_SECRET'), true));

        return hash_equals($hmac, $calculatedHmac);
    }

    public function getProductImage(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string'
        ]);

        try {
            $result = $this->getShopifyImage->getOrderImages($validated['order_id']);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'urls' => $result['urls']
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erro ao processar imagens do pedido', [
                'error' => $e->getMessage(),
                'order_id' => $validated['order_id']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar imagens'
            ], 500);
        }
    }
}

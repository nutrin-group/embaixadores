<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Rest;
use Shopify\Context;

class ShopifyDiscountService
{
    private string $shopDomain;
    private string $accessToken;
    private string $apiVersion;
    private Rest $shopify;

    public function __construct()
    {
        $this->shopDomain = config('services.shopify.domain');
        $this->accessToken = config('services.shopify.token');
        $this->apiVersion = '2024-01';

        // Inicializa o cliente Shopify com a versão da API
        $this->shopify = new Rest(
            $this->shopDomain,
            $this->accessToken,
            $this->apiVersion
        );
    }

    public function createDiscount($code)
    {
        try {
            $response = $this->shopify->post('/admin/api/2024-01/price_rules.json', [
                'price_rule' => [
                    'title' => "Cupom Embaixador - {$code}",
                    'target_type' => 'line_item',
                    'target_selection' => 'all',
                    'allocation_method' => 'across',
                    'value_type' => 'percentage',
                    'value' => '-10.0', // 10% de desconto
                    'customer_selection' => 'all',
                    'starts_at' => now()->toIso8601String(),
                    'ends_at' => null, // Sem data de término
                    'once_per_customer' => false,
                    'usage_limit' => null, // Sem limite de uso
                    'status' => 'active'
                ]
            ]);

            if (!$response['success']) {
                throw new \Exception('Falha ao criar regra de preço na Shopify');
            }

            // Cria o código do cupom vinculado à regra de preço
            $priceRuleId = $response['body']['price_rule']['id'];

            $discountResponse = $this->shopify->post("/admin/api/2024-01/price_rules/{$priceRuleId}/discount_codes.json", [
                'discount_code' => [
                    'code' => $code
                ]
            ]);

            if (!$discountResponse['success']) {
                throw new \Exception('Falha ao criar código de desconto na Shopify');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao criar desconto na Shopify: ' . $e->getMessage());
            throw $e;
        }
    }

    public function discountCodeExists(string $code): bool
    {
        // URL para verificação de código de desconto
        $url = "https://{$this->shopDomain}/admin/api/{$this->apiVersion}/discount_codes/lookup.json";

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->accessToken
            ])->get($url, [
                'code' => $code
            ]);

            // Se a resposta for bem-sucedida, significa que o código já existe
            return $response->successful();
        } catch (\Exception $e) {
            // Em caso de erro, registre o log e retorne false
            Log::error('Erro ao verificar código de desconto', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

}

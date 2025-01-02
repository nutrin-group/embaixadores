<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Product;

class GetShopifyImage
{
    private string $shopifyUrl;
    private string $accessToken;

    public function __construct()
    {
        $shopifyUrl = 'https://' . env('SHOPIFY_DOMAIN') . '/admin/api/2024-10/graphql.json';
        $accessToken = env('SHOPIFY_TOKEN');

        if (!$shopifyUrl || !$accessToken) {
            throw new \Exception('As variáveis de ambiente SHOPIFY_URL e SHOPIFY_ACCESS_TOKEN são obrigatórias');
        }

        $this->shopifyUrl = $shopifyUrl;
        $this->accessToken = $accessToken;
    }

    public function getOrderImages(string $orderId): array
    {
        try {
            Log::info('Iniciando busca de imagens do pedido', [
                'order_id' => $orderId,
                'shopify_url' => $this->shopifyUrl
            ]);

            $query = <<<'GRAPHQL'
            query GetOrderProductImages($orderId: ID!) {
                order(id: $orderId) {
                    lineItems(first: 10) {
                        edges {
                            node {
                                variant {
                                    id
                                    title
                                    product {
                                        title
                                    }
                                    image {
                                        originalSrc
                                    }
                                }
                            }
                        }
                    }
                }
            }
            GRAPHQL;

            $variables = [
                'orderId' => "gid://shopify/Order/{$orderId}"
            ];

            Log::info('Enviando requisição para Shopify', [
                'variables' => $variables,
                'query' => $query
            ]);

            $response = $this->makeRequest($query, $variables);

            Log::info('Resposta da Shopify', [
                'response' => $response
            ]);

            $decodedResponse = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro ao decodificar resposta JSON', [
                    'error' => json_last_error_msg(),
                    'response' => $response
                ]);

                return [
                    'success' => false,
                    'error' => 'Erro ao decodificar resposta do servidor'
                ];
            }

            if (isset($decodedResponse['errors'])) {
                Log::error('Erro retornado pela API Shopify', [
                    'errors' => $decodedResponse['errors']
                ]);

                return [
                    'success' => false,
                    'error' => $decodedResponse['errors'][0]['message'] ?? 'Erro ao buscar imagens'
                ];
            }

            $products = [];
            if (isset($decodedResponse['data']['order']['lineItems']['edges'])) {
                foreach ($decodedResponse['data']['order']['lineItems']['edges'] as $edge) {
                    try {
                        $variantId = $this->extractId($edge['node']['variant']['id']);

                        // Aumenta o tempo limite para cada requisição individual
                        ini_set('max_execution_time', 120);

                        // Adiciona delay entre as requisições para evitar sobrecarga
                        usleep(500000); // 500ms de delay

                        // Verifica se o produto já existe no banco de dados
                        $existingProduct = Product::where('variant_id', $variantId)->first();

                        if (!$existingProduct) {
                            Log::info('Buscando detalhes do produto', ['variant_id' => $variantId]);

                            // Adiciona retry em caso de falha
                            $maxRetries = 3;
                            $attempt = 0;
                            $productDetails = null;

                            while ($attempt < $maxRetries && !$productDetails) {
                                try {
                                    $productDetails = $this->getProductByVariantId($variantId);

                                    if ($productDetails['success']) {
                                        $existingProduct = Product::create([
                                            'variant_id' => $productDetails['data']['variant_id'],
                                            'title' => $productDetails['data']['product_title'] . ' - ' . $productDetails['data']['variant_title'],
                                            'image_url' => $productDetails['data']['image_url']
                                        ]);

                                        Log::info('Produto criado com sucesso', [
                                            'variant_id' => $variantId,
                                            'attempt' => $attempt + 1
                                        ]);
                                        break;
                                    }
                                } catch (\Exception $e) {
                                    $attempt++;
                                    Log::warning('Falha ao buscar produto. Tentativa ' . $attempt, [
                                        'variant_id' => $variantId,
                                        'error' => $e->getMessage()
                                    ]);

                                    if ($attempt < $maxRetries) {
                                        sleep(2); // Espera 2 segundos antes de tentar novamente
                                    }
                                }
                            }
                        }

                        if ($existingProduct) {
                            $products[] = [
                                'url' => $existingProduct->image_url,
                                'variant_id' => $existingProduct->variant_id,
                                'title' => $existingProduct->title
                            ];
                        }
                    } catch (\Exception $itemException) {
                        Log::error('Erro ao processar item do pedido', [
                            'variant_id' => $variantId ?? 'unknown',
                            'error' => $itemException->getMessage()
                        ]);
                        // Continua para o próximo item mesmo se houver erro
                        continue;
                    }
                }
            }

            Log::info('URLs encontradas', [
                'urls_count' => count($products),
                'urls' => $products
            ]);

            return [
                'success' => true,
                'products' => $products,
                'total_processed' => count($products)
            ];

        } catch (\Exception $e) {
            Log::error('Exceção ao buscar imagens', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function makeRequest(string $query, array $variables): string
    {
        $curl = curl_init();

        $payload = json_encode([
            'query' => $query,
            'variables' => $variables
        ]);

        Log::debug('Configurando requisição cURL', [
            'url' => $this->shopifyUrl,
            'payload' => $payload
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->shopifyUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'X-Shopify-Access-Token: ' . $this->accessToken,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        Log::info('Resposta da requisição cURL', [
            'http_code' => $httpCode,
            'response' => $response
        ]);

        if ($error = curl_error($curl)) {
            Log::error('Erro cURL', [
                'error' => $error,
                'curl_info' => curl_getinfo($curl)
            ]);
            throw new \Exception("Erro na requisição: " . $error);
        }

        curl_close($curl);

        return $response;
    }

    private function extractId(string $gid): string
    {
        // Extrai apenas o número final de um GID como gid://shopify/ProductVariant/{id}
        $parts = explode('/', $gid);
        return end($parts);
    }

    private function getProductByVariantId(string $variantId): array
    {
        $query = <<<'GRAPHQL'
        query GetProductByVariantId($variantId: ID!) {
            productVariant(id: $variantId) {
                id
                title
                product {
                    id
                    title
                    description
                    images(first: 1) {
                        edges {
                            node {
                                originalSrc
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;

        $variables = [
            'variantId' => "gid://shopify/ProductVariant/{$variantId}"
        ];

        try {
            $response = $this->makeRequest($query, $variables);
            $decodedResponse = json_decode($response, true);

            if (isset($decodedResponse['data']['productVariant'])) {
                return [
                    'success' => true,
                    'data' => [
                        'variant_id' => $this->extractId($decodedResponse['data']['productVariant']['id']),
                        'variant_title' => $decodedResponse['data']['productVariant']['title'],
                        'product_id' => $this->extractId($decodedResponse['data']['productVariant']['product']['id']),
                        'product_title' => $decodedResponse['data']['productVariant']['product']['title'],
                        'product_description' => $decodedResponse['data']['productVariant']['product']['description'],
                        'image_url' => $decodedResponse['data']['productVariant']['product']['images']['edges'][0]['node']['originalSrc'] ?? null
                    ]
                ];
            }

            return [
                'success' => false,
                'error' => 'Variante não encontrada'
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto por variant_id', [
                'error' => $e->getMessage(),
                'variant_id' => $variantId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

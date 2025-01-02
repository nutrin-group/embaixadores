<?php

namespace App\Http\Controllers;

use App\Services\ShopifyDiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscountController extends Controller
{
    protected $shopifyDiscountService;

    public function __construct(ShopifyDiscountService $shopifyDiscountService)
    {
        $this->shopifyDiscountService = $shopifyDiscountService;
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'value' => 'required|numeric|min:1|max:100',
        ]);

        try {
            if ($this->shopifyDiscountService->discountCodeExists($validated['code'])) {
                return back()->withError('Este código de desconto já existe.');
            }

            // Add title using the code
            $validated['title'] = $validated['code'];
            
            $this->shopifyDiscountService->createDiscount($validated);
            return back()->withSuccess('Código de desconto criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Falha ao criar código de desconto', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            return back()->withError('Falha ao criar código de desconto. Por favor, tente novamente.');
        }
    }

    public function checkCode(Request $request, $code)
    {
        try {
            $exists = $this->shopifyDiscountService->discountCodeExists($code);
            return response()->json(['exists' => $exists]);
        } catch (\Exception $e) {
            Log::error('Failed to check discount code', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            return response()->json(['exists' => false]);
        }
    }
}

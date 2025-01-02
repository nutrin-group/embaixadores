<?php

namespace App\Http\Controllers;

use App\Models\Promocode;
use App\Services\ShopifyDiscountService;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    private ShopifyDiscountService $shopifyService;

    public function __construct(ShopifyDiscountService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    public function checkAvailability(Request $request)
    {
        $code = strtoupper($request->code);

        // Verifica se o código já existe, independente do status
        $exists = Promocode::where('name', $code)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Este código de cupom já está em uso' : null
        ]);
    }
}

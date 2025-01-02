<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentKey;
use Illuminate\Support\Facades\Http;

class OpenPixController extends Controller
{
    public function withdraw(Request $request)
    {
        try {
            $amount = ($request->amount) * 100;
            $pix = PaymentKey::where('user_id', $request->user_id)->first();

            if (!$pix) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave PIX não encontrada para este usuário'
                ], 404);
            }

            $response = Http::withHeaders([
                'Authorization' => env('OPENPIX_APPID'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openpix.com.br/api/v1/transfer', [
                'value' => $amount,
                'fromPixKey' => '4b5268a3-4eb3-4cfe-aab7-212ed068a08b',
                'toPixKey' => $pix->key,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento PIX',
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar pagamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}

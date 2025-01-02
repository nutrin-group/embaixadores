<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PaymentKey;
use App\Models\Promocode;
use App\Providers\RouteServiceProvider;
use App\Services\ShopifyDiscountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AmbassadorRegisterController extends Controller
{
    protected $shopifyDiscountService;

    public function __construct(ShopifyDiscountService $shopifyDiscountService)
    {
        $this->shopifyDiscountService = $shopifyDiscountService;
    }

    public function create(Request $request)
    {
        return Inertia::render('Auth/AmbassadorRegister', [
            'manager_ref' => $request->ref
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'social_network' => 'required|string|in:instagram,tiktok,kwai,x,facebook',
            'social_media_username' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:ambassador_profiles',
            'birth_date' => 'required|date',
            'cep' => 'required|string|max:9',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
            'phone' => 'required|string|max:15',
            'coupon_code' => 'required|string|unique:promocodes,name',
            'manager_referral' => 'nullable|exists:users,id,type,manager'
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'social_network' => $request->social_network,
                'social_media_username' => str_replace('@', '', $request->social_media_username),
                'type' => User::TYPE_AMBASSADOR,
                'status' => 'pending',
                'manager_referral' => $request->manager_referral
            ]);

            $user->ambassadorProfile()->create([
                'nationality' => $request->nationality,
                'cpf' => $request->cpf,
                'birth_date' => $request->birth_date,
                'cep' => $request->cep,
                'street' => $request->street,
                'number' => $request->number,
                'complement' => $request->complement,
                'neighborhood' => $request->neighborhood,
                'city' => $request->city,
                'state' => $request->state,
                'phone' => $request->phone
            ]);

            // Criar o promocode como inativo
            Promocode::create([
                'name' => strtoupper($request->coupon_code),
                'user_id' => $user->id,
                'manager_id' => $request->manager_referral,
                'is_active' => false // Começa como inativo
            ]);

            DB::commit();

            event(new Registered($user));

            return redirect(route('ambassador.pending'));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

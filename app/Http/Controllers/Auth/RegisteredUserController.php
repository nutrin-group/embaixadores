<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Promocode;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'instagram' => $request->instagram,
                'manager_id' => $request->manager_id,
                'type' => 'ambassador'
            ]);

            // Criar cupom inativo usando a coluna 'name'
            Promocode::create([
                'name' => strtoupper(str_replace('@', '', $user->instagram)),
                'user_id' => $user->id,
                'manager_id' => $request->manager_id,
                'is_active' => false
            ]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect(RouteServiceProvider::HOME);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro no registro:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erro ao criar conta.']);
        }
    }
}

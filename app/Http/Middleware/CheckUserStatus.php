<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isBlocked()) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Sua conta foi bloqueada. Entre em contato com o suporte.');
            }

            if ($user->isRefused()) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Sua conta foi recusada. Entre em contato com o suporte.');
            }

            if ($user->isPending()) {
                Auth::logout();
                return redirect()->route('login')->with('info', 'Sua conta está em análise. Aguarde a aprovação.');
            }

            return $next($request);
        }

        return $next($request);
    }
}

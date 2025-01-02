<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user() || $request->user()->type !== $role) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}

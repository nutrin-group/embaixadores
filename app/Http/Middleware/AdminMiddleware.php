<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth()->user();

            if (!auth()->check()) {
                return redirect()->route('login');
            }

            if (!$user || $user->type !== 'admin') {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }

                // Se não for admin, redireciona para a rota apropriada
                return match($user?->type) {
                    'manager' => redirect()->route('manager.dashboard'),
                    'ambassador' => redirect()->route('ambassador.dashboard'),
                    default => redirect()->route('login')
                };
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('AdminMiddleware: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login');
        }
    }
}

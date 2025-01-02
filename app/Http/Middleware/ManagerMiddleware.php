<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth()->user();

            if (!auth()->check()) {
                return redirect()->route('login');
            }

            if (!$user || $user->type !== 'manager') {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }

                return match($user?->userType) {
                    'admin' => redirect()->route('admin.dashboard'),
                    'ambassador' => redirect()->route('ambassador.dashboard'),
                    default => redirect()->route('login')
                };
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('ManagerMiddleware: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login');
        }
    }
}

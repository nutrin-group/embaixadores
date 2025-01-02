<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AmbassadorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth()->user();

            Log::info('AmbassadorMiddleware: Debug Info', [
                'is_authenticated' => auth()->check(),
                'user' => $user?->toArray(),
                'user_type' => $user?->userType,
                'request_path' => $request->path()
            ]);

            if (!auth()->check()) {
                return redirect()->route('login');
            }

            if (!$user || $user->type !== 'ambassador') {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }

                return match($user?->userType) {
                    'admin' => redirect()->route('admin.dashboard'),
                    'manager' => redirect()->route('manager.dashboard'),
                    default => redirect()->route('login')
                };
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('AmbassadorMiddleware: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login');
        }
    }
}

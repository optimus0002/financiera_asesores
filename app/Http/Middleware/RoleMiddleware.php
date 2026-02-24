<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        Log::info('RoleMiddleware: Request received for URL: ' . $request->fullUrl());
        Log::info('RoleMiddleware: Checking authentication');
        Log::info('RoleMiddleware: Required role: ' . $role);
        Log::info('RoleMiddleware: Session ID: ' . session()->getId());
        
        if (!Auth::check()) {
            Log::info('RoleMiddleware: User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        Log::info('RoleMiddleware: User authenticated');
        Log::info('RoleMiddleware: User role: ' . Auth::user()->role);

        if (Auth::user()->role !== $role) {
            Log::info('RoleMiddleware: Role mismatch, aborting 403');
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        Log::info('RoleMiddleware: Role check passed, proceeding');
        return $next($request);
    }
}

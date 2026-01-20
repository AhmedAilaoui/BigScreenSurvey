<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user instanceof \App\Models\AdminUser) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return $next($request);
    }
}
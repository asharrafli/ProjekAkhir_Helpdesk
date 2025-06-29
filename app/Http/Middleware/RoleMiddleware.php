<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Check if user has the required role
        if (!$user->hasRole($role)) {
            abort(403, 'Access denied. You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
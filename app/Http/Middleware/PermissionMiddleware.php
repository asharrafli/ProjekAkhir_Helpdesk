<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Check if user has the required permission
        if (!$user->can($permission)) {
            abort(403, 'Access denied. You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
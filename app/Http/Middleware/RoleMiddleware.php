<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        if (! $request->user()) {
            abort(403);
        }

        // Allow multiple roles separated by '|'
        $allowedRoles = explode('|', $roles);

        if (! in_array($request->user()->role, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}

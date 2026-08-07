<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            !$user ||
            !$user->systemRole ||
            $user->systemRole->system_role_name !== 'User'
        ) {
            abort(403);
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserAccess;
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

        $hasActiveAccess = UserAccess::where('id', session('active_access_id'))
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasActiveAccess) {
            return redirect()->route('context.choose');
        }

        return $next($request);
    }
}
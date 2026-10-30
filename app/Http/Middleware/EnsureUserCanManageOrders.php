<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageOrders
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->canManageOrders()) {
            abort(403, 'You are not authorized to manage orders.');
        }

        return $next($request);
    }
}

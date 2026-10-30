<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->canAccessAdmin()) {
            abort(403, 'You are not authorized to access the admin area.');
        }

        return $next($request);
    }
}

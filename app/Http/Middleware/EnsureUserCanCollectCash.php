<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanCollectCash
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->canCollectCash()) {
            abort(403, 'You are not authorized to collect cash.');
        }

        return $next($request);
    }
}

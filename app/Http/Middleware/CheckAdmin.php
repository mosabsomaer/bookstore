<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and is staff (admin)
        if (!$request->user() || !$request->user()->is_staff) {
            return response()->json(['error' => 'Unauthoridzed'], 403);
        }

        return $next($request);
    }
}


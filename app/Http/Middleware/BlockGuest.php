<?php

namespace App\Http\Middleware;

use App\Enum\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockGuest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === Role::GUEST->value) {
            return response()->json([
                'status'  => false,
                'message' => 'Guest users are not allowed to perform this action.',
                'code'    => 403
            ], 403);
        }

        return $next($request);
    }
}

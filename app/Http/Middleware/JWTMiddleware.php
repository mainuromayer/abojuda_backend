<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JWTMiddleware
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return $this->sendError("User not found or deleted.", [], 401);
            }
            if ($user->status === 'suspended') {
                return $this->sendError("Your account has been suspended. Please contact support.", [], 403);
            }
            return $next($request);
        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return $this->sendError('Token is Invalid', [], 401);
            } else if ($e instanceof TokenExpiredException) {
                return $this->sendError('Token is Expired', [], 401);
            } else {
                return $this->sendError('Unauthorised', [], 401);
            }
        }
    }
}

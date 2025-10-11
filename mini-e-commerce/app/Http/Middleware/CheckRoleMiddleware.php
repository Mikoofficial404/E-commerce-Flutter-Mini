<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user && ! in_array($user->role, $roles, true)) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'success' => false,
                ], 401);
            }
            return $next($request);
        } catch (JWTException $exception) {
            return response()->json([
                'message' => 'Token is Invalid',
                'success' => false,
            ], 401);
        }
    }
}

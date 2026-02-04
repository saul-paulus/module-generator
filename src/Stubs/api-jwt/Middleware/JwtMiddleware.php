<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ixspx\Support\ApiResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\{
    JWTException,
    TokenExpiredException,
    TokenInvalidException
};

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Parse & authenticate token
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return ApiResponse::error(
                    message: 'Authentication required',
                    status: 401,
                    errors: 'JWT user not found'
                );
            }

            // Inject authenticated user
            $request->setUserResolver(fn() => $user);
        } catch (TokenExpiredException) {
            return ApiResponse::error(
                message: 'Token expired',
                status: 401
            );
        } catch (TokenInvalidException) {
            return ApiResponse::error(
                message: 'Token invalid',
                status: 401
            );
        } catch (JWTException $e) {
            return ApiResponse::error(
                message: 'Authentication failed',
                status: 401,
                errors: $e->getMessage()
            );
        }

        return $next($request);
    }
}

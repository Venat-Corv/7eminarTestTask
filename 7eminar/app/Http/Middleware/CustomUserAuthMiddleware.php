<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomUserAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guards = ['sanctum', 'web'];
        $usedGuard = null;

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                auth()->shouldUse($guard);
                $usedGuard = $guard;
                break;
            }
        }

        if (!$usedGuard) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($usedGuard === 'web') {
            if ($request->method() !== 'GET' && !$request->hasValidCsrfToken()) {
                return response()->json(['message' => 'CSRF token mismatch'], 419);
            }
        }

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;");

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If the user belongs to the 'Atlet' (Athlete) role and is not verified,
        // they must not be allowed to proceed to the dashboard or management areas.
        if ($user && ! $user->is_verified && $user->role?->name === 'Atlet') {

            // Allow them to see a pending page or logout
            if ($request->routeIs('verification.pending') || $request->is('logout')) {
                return $next($request);
            }

            // Convert to a proper Symfony Response for strict return type compliance
            $inertiaResponse = Inertia::render('auth/PendingVerification');

            return $inertiaResponse->toResponse($request);
        }

        return $next($request);
    }
}

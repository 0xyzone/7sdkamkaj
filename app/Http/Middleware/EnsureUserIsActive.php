<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks deactivated accounts from standalone (non-Filament) authenticated pages,
 * mirroring the behaviour of the Filament panel Authenticate middleware.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && isset($user->is_active) && ! $user->is_active) {
            return redirect()->route('account.deactivated');
        }

        return $next($request);
    }
}


<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureStaffEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user?->role?->name === 'admin' || $user?->hasVerifiedEmail()) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}

<?php

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfWrongLoginType
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role->name !== $request->input('login_type')) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect(
                $user->role->name === 'admin'
                ? '/admin/login'
                : '/login'
            );
        }

        return $next($request);
    }
}
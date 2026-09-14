<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $role = strtolower(trim((string) Auth::guard($guard)->user()?->role));
                return redirect()->to($this->redirectPathByRole($role));
            }
        }

        return $next($request);
    }

    private function redirectPathByRole(string $role): string
    {
        if (Route::has($role . '.dashboard')) {
            return route($role . '.dashboard');
        }

        return match ($role) {
            'mp', 'mt' => route('superadmin.disposisi.index'),
            default => url('/'),
        };
    }
}

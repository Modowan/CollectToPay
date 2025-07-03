<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
{
    $guards = empty($guards) ? [null] : $guards;

    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            // Utilisez une URL absolue avec le port correct au lieu de RouteServiceProvider::HOME
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect('http://127.0.0.1:8001/admin/dashboard' );
            } elseif ($user->role === 'hotel_manager') {
                return redirect('http://127.0.0.1:8001/hotel/dashboard' );
            } elseif ($user->role === 'branch_manager') {
                return redirect('http://127.0.0.1:8001/branch/dashboard' );
            } elseif ($user->role === 'customer') {
                return redirect('http://127.0.0.1:8001/customer/dashboard' );
            }
            return redirect('http://127.0.0.1:8001/dashboard' );
        }
    }

    return $next($request);
}

}

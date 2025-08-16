<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessAllow;

class CheckDayAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $access = AccessAllow::latest()->first();

        // Si la journée n'est pas lancée et que l'utilisateur n'est pas admin
        if ($access && !$access->allowed && Auth::check() && !Auth::user()->hasRole('admin')) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'message' => 'La journée n\'est pas encore lancée.'
            ]);
        }
        return $next($request);
    }
}

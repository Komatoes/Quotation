<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        foreach($roles as $role) {
            if($user->hasRole($role)) {
                return $next($request);
            }
        }

        // If user doesn't have any of the required roles
        abort(403, 'Unauthorized action.');
    }
}
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
       if (!Auth::check() || !Auth::user() instanceof \App\Models\User) {
    return response()->json(['message' => 'Unauthorized'], 401);
}

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || !($user instanceof User)) {
            return response()->json(['message' => 'User access required'], 403);
        }
        
        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is inactive'], 403);
        }
        
        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user || !($user instanceof Admin)) {
            return response()->json(['message' => 'Admin access required'], 403);
        }
        
        return $next($request);
    }
}
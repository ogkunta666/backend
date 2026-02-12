<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ellenőrizzük, hogy be van-e jelentkezve a felhasználó
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Ellenőrizzük, hogy admin-e a felhasználó
        if (!auth()->user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Admin access required.'
                ], 403);
            }
            abort(403, 'Forbidden. Admin access required.');
        }

        return $next($request);
    }
}

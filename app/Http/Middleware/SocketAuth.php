<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SocketAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if(!$token || $token !== env('SOCKET_TOKEN')) {
            return response()->json([
                'success' => false, 
                'error' => 'Unauthorized'
            ], 401);
        }
        
        return $next($request);
    }
}

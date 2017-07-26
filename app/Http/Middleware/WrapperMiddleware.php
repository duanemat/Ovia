<?php

namespace App\Http\Middleware;

use Closure;

class WrapperMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Pre-Middleware Action

        $response = $next($request);

        // Format the output so it's consistent regardless of endpoint.
        return ["result"=>$response->original[0], "error"=>$response->original[1]];
    }
}

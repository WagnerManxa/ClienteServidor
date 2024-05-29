<?php

namespace App\Http\Middleware;

use Closure;

class OnlyJsonAllowed
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
        if ($request->expectsJson() || $request->isJson()) {
            return $next($request);
        }

        return response()->json(['mensagem' => 'Somente é permitido JSON.'], 400);
    }
}

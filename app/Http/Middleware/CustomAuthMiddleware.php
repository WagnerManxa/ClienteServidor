<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Empresa;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['mensagem' => 'Token não encontrado'], 401);
        }

        if (!Cache::has(TOKEN_CACHE_PREFIX . $token)) {
            return response()->json(['mensagem' => 'Token inválido'], 401);
        }

        $isEmpresa = false;
        $userIdFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);
        $empresa = Empresa::where('id', $userIdFromToken)->first();
        if ($empresa) {
            $isEmpresa = true;
        }

        $request->merge(['isEmpresa' => $isEmpresa]);

        return $next($request);
    }
}

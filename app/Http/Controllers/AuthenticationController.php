<?php

// AuthenticationController.php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Usuario;

class AuthenticationController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'senha' => 'required|min:8',
            ]);

            $usuario = Usuario::where('email', $request->email)->first();

            if (!$usuario) {
                $usuario = Empresa::where('email', $request->email)->first();
            }

            if (!$usuario || !Hash::check($request->senha, $usuario->senha)) {
                return response()->json(['mensagem' => 'Credenciais inválidas'], 401);
            }

            if($usuario instanceof Empresa){
                $isEmpresa = 1;
            }
            if($usuario instanceof Usuario){
                $isEmpresa = 0;
            }

            info($isEmpresa);
            $token = Str::random(60);
            $userData = ['id' => $usuario->id, 'isEmpresa' => $isEmpresa];
            Cache::put(TOKEN_CACHE_PREFIX . $token, $userData, now()->addHours(24));

            return response()->json(['mensagem' => 'Login realizado com sucesso', 'token' => $token], 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Credenciais inválidas'], 401);
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['mensagem' => 'Token não encontrado'], 401);
            }

            if (!Cache::has(TOKEN_CACHE_PREFIX . $token)) {
                return response()->json(['mensagem' => 'Token invaalido'], 401);
            }

            Cache::forget(TOKEN_CACHE_PREFIX . $token);

            return response()->json(['mensagem' => 'Logout realizado com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }

      public function listarTokens()
    {
        try {
            $activeTokens = [];

            $cacheItems = DB::table('cache')->get();

            if (!empty($cacheItems)) {
                foreach ($cacheItems as $item) {
                    if (Str::startsWith($item->key, TOKEN_CACHE_PREFIX)) {
                        $userData = unserialize($item->value);
                        $userId = $userData['id'];
                        $isEmpresa = $userData['isEmpresa'] ? 'true' : 'false';

                        // Buscar o nome do usuário ou empresa
                        if ($isEmpresa === 'true') {
                            $usuario = Empresa::find($userId);
                        } else {
                            $usuario = Usuario::find($userId);
                        }

                        $nome = $usuario ? $usuario->nome : 'N/A';

                        $activeTokens[] = [
                            'token' => Str::after($item->key, TOKEN_CACHE_PREFIX),
                            'user_id' => $userId,
                            'isEmpresa' => $isEmpresa,
                            'nome' => $nome
                        ];
                    }
                }
            }

            if (empty($activeTokens)) {
                return response()->json(['mensagem' => 'Nenhum token ativo encontrado'], 204);
            }

            return response()->json(['tokens' => $activeTokens], 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }

    public function getUserIdByToken($token)
    {
        if (!$token) {
            return null;
        }

        if (!Cache::has(TOKEN_CACHE_PREFIX . $token)) {
            return null;
        }

        $userData = Cache::get(TOKEN_CACHE_PREFIX . $token);

        if (!isset($userData['id'])) {
            return null;
        }
        return $userData['id'];
    }
}

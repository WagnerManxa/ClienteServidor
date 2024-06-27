<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Mensagem;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MensagemController extends Controller
{
    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'candidatos' => 'required|array',
            ]);

            if (!$request->bearerToken()) {
                return response()->json(['mensagem' => 'Token não encontrado'], 401);
            }

            $userData = Cache::get(TOKEN_CACHE_PREFIX . $request->bearerToken());

            if (!$userData || !isset($userData['id'])) {
                return response()->json(['mensagem' => 'Token inválido'], 401);
            }

            $id_empresa = $userData['id'];
            $mensagemPadrao = 'Temos interesse em seu perfil!';

            foreach ($request->candidatos as $email) {
                $usuario = Usuario::where('email', $email)->first();

                if ($usuario) {
                    $novaMensagem = new Mensagem();
                    $novaMensagem->mensagem = $mensagemPadrao;
                    $novaMensagem->lida = 0;
                    $novaMensagem->id_usuario = $usuario->id;
                    $novaMensagem->id_empresa = $id_empresa;
                    $novaMensagem->save();
                }
            }

            return response()->json(['mensagem' => 'Mensagem enviada com sucesso'], 200);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];

            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = ['field' => $field, 'mensagem' => $message];
                }
            }

            return response()->json(['mensagem' => $errorMessages], 422);
        } catch (\Exception $e) {
            info($e);
            return response()->json(['mensagem' => 'Erro interno do servidor'.$e], 500);
        }
    }

    public function index(Request $request){
        $token = $request->bearerToken();
        $idFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);

        $mensagens = Mensagem::where('id_usuario',  $idFromToken['id'])->get();

        if (!$mensagens->isEmpty()) {
            $empresaIds = $mensagens->pluck('id_empresa')->unique();
            $empresas = Empresa::whereIn('id', $empresaIds)->get()->keyBy('id');

            $mensagens = $mensagens->map(function ($mensagem) use ($empresas) {
                return [
                    'empresa' => $empresas[$mensagem->id_empresa]->nome ?? 'Empresa desconhecida',
                    'mensagem' => $mensagem->mensagem,
                    'lida' => $mensagem->lida

                ];

            });
            Mensagem::where('id_usuario',  $idFromToken['id'])->update(['lida' => true]);

            return response()->json($mensagens, 200);
        } else {
            return response()->json(['mensagem' => 'Nenhuma vaga cadastrada.'], 204);
        }
    }

}

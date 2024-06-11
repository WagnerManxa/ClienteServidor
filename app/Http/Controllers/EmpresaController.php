<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Empresa::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string',
                'email' => 'required|email|unique:empresas,email|unique:usuarios,email',
                'senha' => 'required|string|min:8',
                'ramo' => 'required|string',
                'descricao' => 'required|string',
            ]);

            $empresa = Empresa::create([
                'nome' => $request->nome,
                'email' => $request->email,
                'senha' => Hash::make($request->senha),
                'ramo' => $request->ramo,
                'descricao' => $request->descricao,
            ]);


            return response()->json(['mensagem' => 'Empresa criada com sucesso', 'empresa' => $empresa], 200);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];

            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    if ($message === 'validation.unique') {
                        return response()->json(['mensagem' => 'email ja cadastrado'], 422);
                    } else {
                        $errorMessages[] = ['field' => $field, 'mensagem' => $message];
                    }
                }
            }

            return response()->json(['mensagem' => $errorMessages], 422);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor'.$e], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
{
    try {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['mensagem' => 'Token não encontrado'], 401);
        }

        $empresaIdFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);

        if (!$empresaIdFromToken) {
            return response()->json(['mensagem' => 'Token inválido'], 401);
        }
        $empresa = Empresa::find($empresaIdFromToken['id']);

        $data = [
            'nome' => $empresa->nome,
            'email' => $empresa->email,
            'tipo' => 'empresa',
            'ramo' => $empresa->ramo,
            'descricao' => $empresa->descricao
        ];

        if (!$empresa) {
            return response()->json(['mensagem' => 'Usuário não encontrado'], 404);
        }

        return response()->json($data,200);
    } catch (\Exception $e) {
        info($e);
        return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
    }
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $idFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);
        if (!$idFromToken) {
            return response()->json(['mensagem' => 'Token inválido'], 401);
        }
        $empresa = Empresa::findOrFail($idFromToken);
        if (!$empresa) {
            return response()->json(['mensagem' => 'Empresa nao encontrada'], 404);
        }

        try {
            $request->validate([
                'nome' => 'required|string',
                'email' => 'required|email',
                'senha' => 'nullable|string|min:8',
                'ramo' => 'required|string',
                'descricao' => 'required|string',
            ]);
            if ($request->has('nome')) {
                $empresa->nome = $request->nome;
            }

            if ($request->has('email')) {
                $empresa->email = $request->email;
            }

            if ($request->has('senha')) {
                $empresa->senha = Hash::make($request->senha);
            }
            if ($request->has('descricao')) {
                $empresa->descricao = $request->descricao;
            }
            if ($request->has('ramo')) {
                $empresa->ramo = $request->ramo;
            }

            $empresa->save();

            return response()->json($empresa, 200);
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
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (Request $request)
    {
        try {
            $token = $request->bearerToken();

            $empresaData = Cache::get(TOKEN_CACHE_PREFIX . $token);

            if (!isset($empresaData['id'])) {
                return response()->json(['mensagem' => 'Token invalido'], 401);
            }

            $empresaIdFromToken = $empresaData['id'];

            $empresa = Empresa::find($empresaIdFromToken);
            if (!$empresa) {
                return response()->json(['mensagem' => 'Empresa nao encontrada'], 404);
            }

            $empresa->forceDelete();
            Cache::forget(TOKEN_CACHE_PREFIX . $token);

            return response()->json(['mensagem' => 'Empresa excluida com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['mensagem' => 'Erro interno do servidor: '.$e->getMessage()], 500);
        }
    }

    // VAGAS////////////////////

    /**
     * Mostra as vagas da empresa.
     */
    public function vagas($empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        if (!$empresa) {
            return response()->json(['mensagem' => 'Empresa não encontrada'], 404);
        }

        $vagas = Vaga::where('empresa_id', $empresaId)->get();

        return response()->json(['vagas' => $vagas], 200);
    }

    /**
     * Cria uma nova vaga para a empresa.
     */
    public function criarVaga(Request $request, $empresaId)
    {
        $empresa = Empresa::findOrFail($empresaId);
        if (!$empresa) {
            return response()->json(['mensagem' => 'Empresa não encontrada'], 404);
        }

        $request->validate([
            'titulo' => 'required|string',
            'descricao' => 'required|string',
        ]);

        $vaga = new Vaga();
        $vaga->titulo = $request->titulo;
        $vaga->descricao = $request->descricao;
        $vaga->empresa_id = $empresaId;

        $vaga->save();

        return response()->json(['mensagem' => 'Vaga criada com sucesso', 'vaga' => $vaga], 200);
    }

}

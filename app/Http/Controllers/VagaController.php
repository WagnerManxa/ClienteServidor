<?php

namespace App\Http\Controllers;

use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class VagaController extends Controller
{
    public function index(Request $request)
    {
        if(request()->isEmpresa){
            $token = $request->bearerToken();
            $idFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);

            $vagas = Vaga::where('empresa_id',  $idFromToken['id'])
                        ->with(['competencias', 'ramo'])
                        ->get();

            if (!$vagas->isEmpty()) {
                $vagas = $vagas->map(function ($vaga) {
                    return [
                        'id' => $vaga->id,
                        'titulo' => $vaga->titulo,
                        'descricao' => $vaga->descricao,
                        'competencias' => $vaga->competencias->map(function ($competencia) {
                            return [
                                'id' => $competencia->id,
                                'nome' => $competencia->nome,
                            ];
                        }),
                        'experiencia' => $vaga->experiencia,
                        'salario_min' => $vaga->salario_min,
                        'salario_max' => $vaga->salario_max,
                        'empresa_id' => $vaga->empresa_id,
                        'ativo' => (bool)$vaga->ativo,
                        'ramo' => [
                            'id' => $vaga->ramo->id,
                            'nome' => $vaga->ramo->nome,
                            'descricao' => $vaga->ramo->descricao,
                        ],
                    ];
                });

                return response()->json($vagas);
            } else {
                return response()->json(['mensagem' => 'Nenhuma vaga cadastrada.'], 204);
            }
        }else{
            $vagas = Vaga::where('ativo',  true)
            ->with(['competencias', 'ramo'])
            ->get();

            if (!$vagas->isEmpty()) {
                $vagas = $vagas->map(function ($vaga) {
                    return [
                        'id' => $vaga->id,
                        'titulo' => $vaga->titulo,
                        'descricao' => $vaga->descricao,
                        'competencias' => $vaga->competencias->map(function ($competencia) {
                            return [
                                'id' => $competencia->id,
                                'nome' => $competencia->nome,
                            ];
                        }),
                        'experiencia' => $vaga->experiencia,
                        'salario_min' => $vaga->salario_min,
                        'salario_max' => $vaga->salario_max,
                        'empresa_id' => $vaga->empresa_id,
                        'ativo' => (bool)$vaga->ativo,
                        'ramo' => [
                            'id' => $vaga->ramo->id,
                            'nome' => $vaga->ramo->nome,
                            'descricao' => $vaga->ramo->descricao,
                        ],
                    ];
                });

                return response()->json($vagas);
            } else {
                return response()->json(['mensagem' => 'Nenhuma vaga cadastrada.'], 204);
            }
        }
    }


    public function store(Request $request)
{
    $token = $request->bearerToken();
    $idFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);

    $validator = Validator::make($request->all(), [
        'ramo_id' => 'required|exists:ramos,id',
        'titulo' => 'required|string',
        'descricao' => 'required|string',
        'competencias' => 'required|array',
        'competencias.*.id' => 'required_with:competencias|exists:competencias,id',
        'experiencia' => 'required|integer',
        'salario_min' => 'required|numeric',
        'salario_max' => 'nullable|numeric',
        'ativo' => 'required|boolean',
    ]);


    if ($validator->fails()) {
        return response()->json(['mensagem' => $validator->errors()], 422);
    }

    if (!request()->isEmpresa) {
        return response()->json(['mensagem' => "Somente empresas podem cadastrar vagas."], 403);
    }

     $competencias = $request->input('competencias');


    $vaga = Vaga::create([
        'empresa_id' => $idFromToken['id'],
        'ramo_id' => $request->ramo_id,
        'titulo' => $request->titulo,
        'descricao' => $request->descricao,
        'experiencia' => $request->experiencia,
        'salario_min' => $request->salario_min,
        'salario_max' => $request->salario_max,
        'ativo' => $request->ativo,
    ]);

   $vaga->competencias()->attach(array_column($competencias, 'id'));

    return response()->json(['message' => 'Vaga criada com sucesso', 'vaga' => $vaga], 201);
}

    public function show($id)
    {

        $vaga = Vaga::with(['competencias', 'ramo'])->find($id);
        if ($vaga === null) {
            return response()->json(['mensagem' => 'Vaga nao encontrada. ID: '.$id], 200);
        }
        return response()->json($vaga);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ramo_id' => 'required|exists:ramos,id',
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'competencias' => 'required|array',
            'competencias.*.id' => 'required|integer|exists:competencias,id',
            'experiencia' => 'required|integer',
            'salario_min' => 'required|numeric',
            'salario_max' => 'nullable|numeric',
            'ativo' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['mensagem' => $validator->errors()], 422);
        }

        $vaga = Vaga::find($id);
        if (!$vaga) {
            return response()->json(['mensagem' => 'Vaga não encontrada'], 404);
        }

        $token = $request->bearerToken();
        $idFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);
        if ($vaga->empresa_id !== $idFromToken['id']) {
            return response()->json(['mensagem' => "Vaga não pertence a sua empresa"], 422);
        }


        $vaga->update([
            'ramo_id' => $request->ramo_id,
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'experiencia' => $request->experiencia,
            'salario_min' => $request->salario_min,
            'salario_max' => $request->salario_max,
            'ativo' => $request->ativo,
        ]);

        $idCompetencias = array_column($request->competencias, 'id');
        if (!is_array($idCompetencias)) {
            return response()->json(['mensagem' => 'Competências deve ser um array de IDs'], 422);
        }
        $vaga->competencias()->sync($idCompetencias);

        return response()->json(['message' => 'Vaga atualizada com sucesso', 'vaga' => $vaga], 200);
    }

    public function destroy(Request $request, $id)
    {
        $vaga = Vaga::find($id);
        if (!$vaga) {
            return response()->json(['mensagem' => 'Vaga não encontrada'], 404);
        }

        $token = $request->bearerToken();
        $idFromToken = Cache::get(TOKEN_CACHE_PREFIX . $token);
        if ($vaga->empresa_id !== $idFromToken['id']) {
            return response()->json(['mensagem' => "Erro ao excluir. Vaga não pertence a sua empresa"], 422);
        }

        $vaga->competencias()->detach();
        $vaga->delete();

        return response()->json(['message' => 'Vaga excluída com sucesso'], 204);
    }
}

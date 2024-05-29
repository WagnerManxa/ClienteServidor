<?php

namespace App\Http\Controllers;

use App\Models\Experiencia;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExperienciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome_empresa' => 'required|string',
                'inicio' => 'required|date',
                'fim' => 'nullable|date|after_or_equal:inicio',
                'cargo' => 'required|string',
                'id_candidato' => 'required|exists:usuarios,id'
            ]);

            $experiencia = new Experiencia();
            $experiencia->nome_empresa = $request->nome_empresa;
            $experiencia->inicio = $request->inicio;
            $experiencia->fim = $request->fim;
            $experiencia->cargo = $request->cargo;
            $experiencia->id_candidato = $request->id_candidato;
            $experiencia->save();

            return response()->json(['mensagem' => 'Experiência cadastrada com sucesso', 'experiencia' => $experiencia], 201);
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
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($idCandidato)
    {
        try {
            $experiencias = Experiencia::where('id_candidato', $idCandidato)->get();

            if ($experiencias->isEmpty()) {
                return response()->json(['mensagem' => 'Nenhuma experiência encontrada para este candidato', 'experiencias' => null], 200);
            }

            return response()->json(['mensagem' => 'Experiências encontradas', 'experiencias' => $experiencias], 200);
        } catch (\Exception $e) {
            info($e);
            return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Experiencia $experiencia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Experiencia $experiencia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Experiencia $experiencia)
    {
        //
    }
}

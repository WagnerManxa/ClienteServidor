<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use Illuminate\Http\Request;

class CompetenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    try {
        $competencias = Competencia::all()->map(function ($competencia) {
            return [
                'id' => $competencia->id,
                'nome' => $competencia->nome
            ];
        });

        $jsonCompetencias = json_encode($competencias);

        return response($jsonCompetencias, 200)
            ->header('Content-Type', 'application/json');
    } catch (\Exception $e) {
        info($e);
        return response()->json(['mensagem' => 'Erro interno do servidor'], 500);
    }
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Competencia $competencia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Competencia $competencia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competencia $competencia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competencia $competencia)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Ramo;
use Illuminate\Http\Request;

class RamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Ramo::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|unique:ramos',
        ]);

        $ramo = Ramo::create([
            'nome' => $request->nome,
        ]);

        return response()->json(['mensagem' => 'Ramo criado com sucesso', 'ramo' => $ramo], 200);
    }
}


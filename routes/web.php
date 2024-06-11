<?php

use App\Http\Controllers\CompetenciaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ExperienciaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RamoController;
use App\Http\Controllers\VagaController;

define('TOKEN_CACHE_PREFIX', 'token_');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('custom.auth')->group(function () {

    Route::get('/usuario', function () {
        if (request()->isEmpresa) {
            return app()->make(EmpresaController::class)->show(request());
        } else {
            return app()->make(UsuarioController::class)->show(request());
        }
    });
    Route::put('/usuario', function () {
        if (request()->isEmpresa) {
            return app()->make(EmpresaController::class)->update(request());
        } else {
            return app()->make(UsuarioController::class)->update(request());
        }
    });
    Route::delete('/usuario', [UsuarioController::class, 'destroy']);
    Route::post('/vagas', [VagaController::class, 'store']);
    Route::put('/vagas/{id}', [VagaController::class, 'update']);
    Route::delete('/vagas/{id}', [VagaController::class, 'destroy']);
    Route::get('/vagas/{id}', [VagaController::class, 'show']);
    Route::get('/vagas', [VagaController::class, 'index']);
    Route::get('/ramos', [RamoController::class, 'index']);


});


Route::get('/ramos', [RamoController::class, 'index']);
Route::post('/ramos', [RamoController::class, 'store']);



Route::post('/usuarios/empresa', [EmpresaController::class, 'store']);
Route::post('/usuarios/candidatos', [UsuarioController::class, 'store']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::get('/listartokens', [AuthenticationController::class, 'listarTokens']);
Route::post('/logout', [AuthenticationController::class, 'logout']);
Route::put('/experiencias', [ExperienciaController::class, 'store']);
Route::get('/experiencias/{id}', [ExperienciaController::class, 'show']);
Route::get('/competencias', [CompetenciaController::class, 'index']);
Route::get('/usuarios', [ExperienciaController::class, 'index']);


//Route::put('/usuario', [UsuarioController::class, 'update']);
// Route::get('/usuario', [UsuarioController::class, 'show']);
// Route::put('/usuario', [UsuarioController::class, 'update']);
//Route::get('/competencias', [CompetenciaController::class, 'index']);

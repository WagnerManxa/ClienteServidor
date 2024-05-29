<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experiencia extends Model
{
    use HasFactory;
    protected $fillable = ['nome_empresa', 'inicio', 'fim', 'cargo','id_candidato'];

    public $timestamps = false;
}


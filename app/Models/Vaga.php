<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'ramo_id', 'titulo', 'descricao', 'experiencia', 'salario_min', 'salario_max', 'ativo'
    ];

    public function ramo()
    {
        return $this->belongsTo(Ramo::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function competencias()
    {
        return $this->belongsToMany(Competencia::class, 'vagas_competencias', 'vaga_id', 'competencia_id');
    }
}

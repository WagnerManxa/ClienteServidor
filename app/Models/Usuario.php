<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model

{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = ['senha'];

    protected $fillable = ['nome', 'email', 'senha'];

    public function competencias()
    {
        return $this->belongsToMany(Competencia::class, 'usuario_competencia', 'usuario_id', 'competencia_id');
    }

    public function experiencias()
    {
        return $this->hasMany(Experiencia::class, 'id_candidato', 'id');
    }

    public function mensagems()
    {
        return $this->belongsToMany(Mensagem::class, 'id_candidato', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($usuario) {
            $usuario->experiencias()->delete();
        });
    }

}


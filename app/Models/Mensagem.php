<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    use HasFactory;
    protected $table = 'mensagens';
    protected $fillable = ['mensagem', 'lida', 'id_empresa','id_usuario'];

    public $timestamps = false;
}

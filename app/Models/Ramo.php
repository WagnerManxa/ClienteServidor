<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ramo extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao'];

    public function vagas()
    {
        return $this->hasMany(Vaga::class);
    }
}

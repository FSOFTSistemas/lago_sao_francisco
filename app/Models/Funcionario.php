<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'nome',
        'cpf',
        'endereco',
        'salario',
        'data_contratacao',
        'status',
        'setor',
        'cargo',
        'empresa_id'
    ];

    public function empresa()
{
    return $this->belongsTo(Empresa::class);
}

}

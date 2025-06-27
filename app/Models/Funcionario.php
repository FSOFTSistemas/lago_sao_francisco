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
        'endereco_id',
        'salario',
        'data_contratacao',
        'status',
        'setor',
        'cargo',
        'empresa_id',
        'vendedor',
        'caixa',
        'senha_supervisor'
    ];

    public function empresa()
{
    return $this->belongsTo(Empresa::class);
}
    public function endereco()
{
    return $this->belongsTo(Endereco::class);
}
}

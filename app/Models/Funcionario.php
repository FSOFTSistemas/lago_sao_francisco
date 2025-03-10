<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;
    protected $fillable = [
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
}

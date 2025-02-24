<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $fillable = [
        'nome',
        'endereco',
        'salario',
        'data_contratacao',
        'status',
        'setor',
        'empresa_id'
    ];
}

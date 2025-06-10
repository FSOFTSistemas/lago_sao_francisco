<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'agencia',
        'numero_banco',
        'numero_conta',
        'digito_numero',
        'digito_agencia',
        'digito_conta',
        'agencia_uf',
        'agencia_cidade',
        'taxa',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco',
        'inscricao_estadual'
    ];
}
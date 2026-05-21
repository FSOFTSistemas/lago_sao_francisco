<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco',
        'inscricao_estadual',
        'forma_pagamento',
        'plano_de_conta_id',
    ];

    public function planoDeConta()
    {
        return $this->belongsTo(PlanoDeConta::class, 'plano_de_conta_id');
    }
}

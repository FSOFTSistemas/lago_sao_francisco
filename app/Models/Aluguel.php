<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluguel extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'data',
        'observacoes',
        'subtotal',
        'total',
        'acrescimo',
        'desconto',
        'parcelas',
        'vencimento',
        'contrato',
        'adicionais',
        'status',
        'espaco_id',
        'cliente_id',
        'empresa_id',
        'forma_pagamento_id',
    ];
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
    public function espaco()
    {
        return $this->belongsTo(Espaco::class, 'espaco_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'forma_pagamento_id',
        'empresa_id',
        'data',
        'cliente_id',
        'usuario_id',
        'observacao',
        'total',
        'subtotal',
        'desconto',
        'acrescimo',
        'situacao',
        'gerado_nf'
    ];
    public function daEmpresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function formaPagamento(){
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}

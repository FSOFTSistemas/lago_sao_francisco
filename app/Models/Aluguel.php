<?php

namespace App\Models;

use App\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluguel extends Model
{
    use HasFactory;
    protected $fillable = [
        'data_inicio',
        'data_fim',
        'observacoes',
        'subtotal',
        'total',
        'acrescimo',
        'desconto',
        'parcelas',
        'vencimento',
        'contrato',
        'status',
        'espaco_id',
        'cliente_id',
        'empresa_id',
        'forma_pagamento_id',
        'numero_pessoas_buffet'
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
    public function adicionais()
    {
        return $this->belongsToMany(Adicional::class, 'adicionais_aluguel');
    }
    public function buffetItens()
    {
        return $this->belongsToMany(BuffetItem::class, 'aluguel_buffet_item');
    }
    protected static function booted()
    {
        static::addGlobalScope(new EmpresaScope);
    }
}

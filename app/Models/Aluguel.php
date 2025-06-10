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
        'numero_pessoas_buffet',
        'cardapio_id'
    ];

    // === Relacionamentos diretos ===

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    public function espaco()
    {
        return $this->belongsTo(Espaco::class);
    }

    public function cardapio()
    {
        return $this->belongsTo(Cardapio::class);
    }

    // === Relacionamentos Many-to-Many ===

    public function adicionais()
    {
        return $this->belongsToMany(Adicional::class, 'adicionais_aluguel');
    }

    public function buffetItens()
    {
        return $this->belongsToMany(BuffetItem::class, 'aluguel_buffet_item', 'aluguel_id', 'buffet_item_id');
    }

    // === Relacionamento com itens agrupados por categoria ===

    public function categoriaItens()
    {
        return $this->hasMany(AluguelCategoriaItem::class);
    }
    

    // === Escopos globais ===

    protected static function booted()
    {
        static::addGlobalScope(new EmpresaScope);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AluguelPagamento extends Model
{
    use HasFactory;

    protected $table = 'aluguel_pagamentos';

    protected $fillable = [
        'aluguel_id',
        'forma_pagamento_id',
        'valor',
        'observacoes'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    // === Relacionamentos ===

    /**
     * Relacionamento com o aluguel
     */
    public function aluguel()
    {
        return $this->belongsTo(Aluguel::class);
    }

    /**
     * Relacionamento com a forma de pagamento
     */
    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    // === Métodos auxiliares ===

    /**
     * Formatar valor para exibição
     */
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Scope para filtrar por aluguel
     */
    public function scopeDoAluguel($query, $aluguelId)
    {
        return $query->where('aluguel_id', $aluguelId);
    }

    /**
     * Scope para filtrar por forma de pagamento
     */
    public function scopeFormaPagamento($query, $formaPagamentoId)
    {
        return $query->where('forma_pagamento_id', $formaPagamentoId);
    }
}


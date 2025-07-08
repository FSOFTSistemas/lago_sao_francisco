<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    protected $table = 'transacoes';

    protected $fillable = [
        'descricao',
        'status',
        'forma_pagamento_id',
        'categoria',
        'data_pagamento',
        'data_vencimento',
        'tipo',
        'valor',
        'observacoes',
        'reserva_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'data_pagamento' => 'date',
        'data_vencimento' => 'date',
        'valor' => 'decimal:2',
    ];

    // Relacionamentos
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('status', true);
    }

    public function scopePagamentos($query)
    {
        return $query->where('tipo', 'pagamento');
    }

    public function scopeDescontos($query)
    {
        return $query->where('tipo', 'desconto');
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorReserva($query, $reservaId)
    {
        return $query->where('reserva_id', $reservaId);
    }

    // Accessors
    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function getDataPagamentoFormatadaAttribute()
    {
        return $this->data_pagamento ? $this->data_pagamento->format('d/m/Y') : null;
    }

    public function getDataVencimentoFormatadaAttribute()
    {
        return $this->data_vencimento ? $this->data_vencimento->format('d/m/Y') : null;
    }

    // Mutators
    public function setValorAttribute($value)
    {
        // Remove formatação brasileira se presente
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        $this->attributes['valor'] = $value;
    }
}

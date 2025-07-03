<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelaContasAPagar extends Model
{
    use HasFactory;

    protected $table = 'parcelas_contas_a_pagar';

    protected $fillable = [
        'contas_a_pagar_id',
        'numero_parcela',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'valor_pago',
        'status',
    ];

    public function conta()
    {
        return $this->belongsTo(ContasAPagar::class, 'contas_a_pagar_id');
    }

    public function isPaga()
    {
        return $this->status === 'finalizado';
    }
}


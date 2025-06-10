<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'status',
        'forma_pagamento_id',
        'categoria', //['hospedagem', 'alimentos', 'servicos', 'produtos']
        'data_pagamento',
        'data_vencimento',
        'tipo',
        'valor',
        'observacoes',
        'reserva_id',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}

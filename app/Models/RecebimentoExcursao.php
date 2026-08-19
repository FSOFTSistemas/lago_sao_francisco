<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecebimentoExcursao extends Model
{
    use HasFactory;

    protected $table = 'recebimento_excursao';

    protected $fillable = [
        'excursao_id',
        'data_recebimento',
        'valor',
        'forma_pagamento_id',
        'comprovante_path',
    ];

    protected $casts = [
        'data_recebimento' => 'date',
        'valor' => 'decimal:2',
    ];

    public function excursao(): BelongsTo
    {
        return $this->belongsTo(Excursao::class);
    }

    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class);
    }
}

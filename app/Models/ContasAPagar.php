<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContasAPagar extends Model
{
    use HasFactory;
    protected $table = 'contas_a_pagar';
    protected $fillable = [
        'id',
        'descricao',
        'valor',
        'valor_pago',
        'data_vencimento',
        'data_pagamento',
        'status',
        'empresa_id',
        'plano_de_conta_id',
        'fornecedor'
    ];
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor');
    }
    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }
}


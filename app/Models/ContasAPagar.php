<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContasAPagar extends Model
{
    use HasFactory;

    protected $table = 'contas_a_pagar';

    protected $fillable = [
        'descricao',
        'valor',
        'valor_pago',
        'data_vencimento',
        'data_pagamento',
        'status',
        'empresa_id',
        'plano_de_conta_id',
        'fornecedor_id'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function parcelas()
    {
        return $this->hasMany(ParcelaContasAPagar::class);
    }

    public function parcelasPagas()
    {
        return $this->parcelas()->where('status', 'finalizado');
    }

    public function parcelaAtual()
    {
        return $this->parcelas()
            ->where('status', 'pendente')
            ->orderBy('data_vencimento')
            ->first();
    }

    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // Filtro por mês/ano nas parcelas
    public function scopeComParcelasDoMes($query, $mes, $ano)
    {
        return $query->whereHas('parcelas', function ($q) use ($mes, $ano) {
            $q->whereMonth('data_vencimento', $mes)
              ->whereYear('data_vencimento', $ano);
        });
    }
}

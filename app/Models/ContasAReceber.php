<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContasAReceber extends Model
{
    use HasFactory;
    protected $table = 'contas_a_receber';
    protected $fillable = [
        'id',
        'descricao',
        'valor',
        'valor_recebido',
        'data_vencimento',
        'data_pagamento',
        'status',
        'venda_id',
        'parcela',
        'cliente_id',
        'empresa_id',
        'plano_de_contas_id'
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function planoDeConta()
    {
        return $this->belongsTo(PlanoDeConta::class, 'plano_de_contas_id');
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function venda(){
        return $this->belongsTo(Venda::class, 'venda_id');
    }
}

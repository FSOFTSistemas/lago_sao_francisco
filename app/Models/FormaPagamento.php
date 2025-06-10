<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function venda()
    {
        return $this->hasMany(Venda::class, 'forma_pagamento_id');
    }

    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }
}

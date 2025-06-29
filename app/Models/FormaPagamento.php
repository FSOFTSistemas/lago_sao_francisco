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

    public function venda()
    {
        return $this->hasMany(Venda::class, 'forma_pagamento_id');
    }

}

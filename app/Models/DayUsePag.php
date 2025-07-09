<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayUsePag extends Model
{
    use HasFactory;
    protected $fillable = [
        'forma_pagamento_id',
        'dayuse_id',
        'valor'
    ];

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id', 'id');
    }
    public function dayuse()
    {
        return $this->belongsTo(DayUse::class, 'dayuse_id', 'id');
    }
}

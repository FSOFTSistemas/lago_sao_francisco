<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'subtotal',
        'acrescimo',
        'deconto',
        'total',
        'venda_id',

    ];

    public function venda(){
        return $this->belongsTo(Venda::class, 'venda_id');
    }
    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    use HasFactory;

    protected  $fillable = [
        'id',
        'produto_id',
        'estoque_atual',
        'empresa_id',
        'entradas',
        'saidas'
    ];

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'tipo',
        'situacao',
        'ean',
        'preco_custo',
        'preco_venda',
        'ncm',
        'cst',
        'cfop_interno',
        'cfop_externo',
        'aliquota',
        'csosn',
        'empresa_id'
    ];
    public function daEmpresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}

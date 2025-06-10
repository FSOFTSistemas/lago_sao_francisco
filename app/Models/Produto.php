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
        'categoria_produto_id',
        'ativo',
        'ean',
        'preco_custo',
        'preco_venda',
        'ncm',
        'cst',
        'cfop_interno',
        'cfop_externo',
        'aliquota',
        'csosn',
        'empresa_id',
        'comissao',
        'observacoes'
    ];
    public function daEmpresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaProduto::class, 'categoria_produto_id');
    }
}

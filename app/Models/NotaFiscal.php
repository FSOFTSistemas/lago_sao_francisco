<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'cliente_id',
        'mcm',
        'cfop',
        'usuario_id',
        'data',
        'empresa_id',
        'chave',
        'numero',
        'serie',
        'observacoes',
        'info_complementares',
        'peso_liquido',
        'peso_bruto',
        'pt_frete',
        'pt_transporte',
        'pt_nota',
        'nfe_referenciavel',
        'total_produtos',
        'total_notas',
        'total_desconto',
        'outras_despesas',
        'base_ICMS',
        'vICMS',
        'base_ST',
        'vST'
    ];

    public function cliente (){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

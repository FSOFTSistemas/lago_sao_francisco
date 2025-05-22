<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFiscalItens extends Model
{
    use HasFactory;

     protected $fillable = [
        'id',
        'nota_fiscal_id',
        'produto_id',
        'quantidade',
        'v_unitario',
        'desconto',
        'subtotal',
        'cst',
        'cfop_id',
        'csosm',
        'total',
        'base_ICMS',
        'vICMS',
        'base_st',
        'vST'
     ];

     public function notalFiscal(){
        return $this->belongsTo(NotaFiscal::class, "nota_fiscal_id");
     }

    public function produto(){
        return $this->belongsTo(Produto::class, "produto_id");
     }

     public function cfop(){
        return $this->belongsTo(CFOP::class, 'cfop_id');
     }
}

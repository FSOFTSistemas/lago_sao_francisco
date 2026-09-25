<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaFiscalItem extends Model
{
    use HasFactory;

    protected $table = 'nota_fiscal_itens';

    protected $fillable = [
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
        'base_ST',
        'v_ST',
    ];

    public function notaFiscal(): BelongsTo
    {
        return $this->belongsTo(NotaFiscal::class, 'nota_fiscal_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}

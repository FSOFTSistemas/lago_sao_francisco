<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcursaoAlmoco extends Model
{
    use HasFactory;

    protected $table = 'excursao_almoco';

    protected $fillable = [
        'excursao_id',
        'cardapio_excursao_id',
        'nome_cardapio',
        'descricao_cardapio',
        'quantidade',
        'valor_unitario',
        'total',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'valor_unitario' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function excursao(): BelongsTo
    {
        return $this->belongsTo(Excursao::class);
    }

    public function cardapio(): BelongsTo
    {
        return $this->belongsTo(CardapioExcursao::class, 'cardapio_excursao_id');
    }
}

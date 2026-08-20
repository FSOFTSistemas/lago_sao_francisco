<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardapioExcursao extends Model
{
    use HasFactory;

    protected $table = 'cardapios_excursao';

    protected $fillable = [
        'nome',
        'descricao_cardapio',
        'valor_por_pessoa',
        'ativo',
    ];

    protected $casts = [
        'valor_por_pessoa' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function almocosExcursao(): HasMany
    {
        return $this->hasMany(ExcursaoAlmoco::class, 'cardapio_excursao_id');
    }
}

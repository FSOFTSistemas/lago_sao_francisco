<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AluguelCategoriaItem extends Model
{
    protected $table = 'aluguel_categoria_item';

    protected $fillable = [
        'aluguel_id',
        'cardapio_categoria_id',
        'buffet_item_id',
    ];

    public function aluguel()
    {
        return $this->belongsTo(Aluguel::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CardapioCategoria::class, 'cardapio_categoria_id');
    }

    public function item()
    {
        return $this->belongsTo(BuffetItem::class, 'buffet_item_id');
    }
}


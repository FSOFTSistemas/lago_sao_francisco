<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardapioCategoria extends Model
{
    protected $table = 'cardapio_categorias';

    protected $fillable = [
        'cardapio_id',
        'categoria_id',
        'quantidade_maxima',
    ];

    public function cardapio()
    {
        return $this->belongsTo(Cardapio::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class); // Pode ser BuffetCategoria ou Categoria
    }

    public function itens()
    {
        return $this->belongsToMany(BuffetItem::class, 'cardapio_categoria_item', 'cardapio_categoria_id', 'buffet_item_id');
    }
}

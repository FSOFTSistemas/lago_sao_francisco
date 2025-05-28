<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardapioCategoriaItem extends Model
{
    use HasFactory;
    protected $table = 'cardapio_categoria_item';

    protected $fillable = [
        'cardapio_id',
        'categoria_id',
        'buffet_item_id',
    ];

    public function categoria()
{
    return $this->belongsTo(CategoriasCardapio::class, 'categoria_id');
}

public function item()
{
    return $this->belongsTo(BuffetItem::class, 'buffet_item_id');
}

}

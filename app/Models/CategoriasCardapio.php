<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriasCardapio extends Model
{
    use HasFactory;
    protected $table = 'categorias_cardapio';
    protected $fillable = ['nome'];

    public function cardapios()
    {
        return $this->belongsToMany(Cardapio::class, 'cardapio_categoria', 'categoria_id', 'cardapio_id')
                    ->withPivot('quantidade_itens')
                    ->withTimestamps();
    }

    public function itens()
    {
        return $this->hasMany(BuffetItem::class, 'categoria_id', 'id');
    }
}

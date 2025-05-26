<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cardapio extends Model
{
    use HasFactory;
    protected $fillable = ['nome', 'observacoes'];

    public function categorias()
    {
        return $this->belongsToMany(CategoriasCardapio::class, 'cardapio_categoria', 'cardapio_id', 'categoria_id')
                    ->withPivot('quantidade_itens')
                    ->withTimestamps();
    }
    public function itensPorCategoria()
{
    return $this->belongsToMany(BuffetItem::class, 'cardapio_categoria_item')
        ->withPivot('categoria_cardapio_id')
        ->withTimestamps();
}

}

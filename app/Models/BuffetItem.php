<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuffetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'valor_unitario',
        'categoria_id'
    ];

    public function alugueis()
    {
        return $this->belongsToMany(Aluguel::class, 'aluguel_buffet_item');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriasCardapio::class, 'categoria_id');
    }
}

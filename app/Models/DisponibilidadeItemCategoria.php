<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadeItemCategoria extends Model
{
    // protected $table = 'DisponibilidadeDeItemNaCategoria';
    protected $primaryKey = 'DisponibilidadeID';

    protected $fillable = [
        'ItemInclusoPadrao',
        'OrdemExibicao',
        'CategoriaItemID',
        'ItemID',
    ];

    protected $casts = [
        'ItemInclusoPadrao' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriasDeItensCardapio::class, 'CategoriaItemID');
    }

    public function item()
    {
        return $this->belongsTo(ItensDoCardapio::class, 'ItemID');
    }

}

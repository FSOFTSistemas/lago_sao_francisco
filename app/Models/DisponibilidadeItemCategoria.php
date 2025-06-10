<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadeItemCategoria extends Model
{
    // protected $table = 'DisponibilidadeDeItemNaCategoria';
    protected $primaryKey = 'DisponibilidadeID';
    
    public $timestamps = true;
    
    protected $fillable = [
        'ItemInclusoPadrao',
        'OrdemExibicao',
        'CategoriaItemID',
        'ItemID'
    ];

    protected $casts = [
        'ItemInclusoPadrao' => 'boolean',
        'OrdemExibicao' => 'integer'
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

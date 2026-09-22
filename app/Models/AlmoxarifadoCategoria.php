<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlmoxarifadoCategoria extends Model
{
    use HasFactory;

    protected $table = 'almoxarifado_categorias';

    protected $fillable = [
        'nome',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(AlmoxarifadoItem::class, 'categoria_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}

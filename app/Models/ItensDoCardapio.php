<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItensDoCardapio extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome_item',
        'tipo_item',
    ];
    public function disponibilidade()
    {
        return $this->hasMany(DisponibilidadeItemCategoria::class);
    }
}

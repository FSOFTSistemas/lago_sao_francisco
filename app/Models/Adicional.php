<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adicional extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'valor',
    ];

    public function alugueis()
    {
        return $this->belongsToMany(Aluguel::class, 'adicionais_aluguel');
    }
}

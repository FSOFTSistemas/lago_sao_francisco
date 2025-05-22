<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adicional extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'empresa_id',
        'valor',
    ];

    public function alugueis()
    {
        return $this->belongsToMany(Aluguel::class, 'adicionais_aluguel');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}

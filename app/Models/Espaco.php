<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espaco extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome',
        'status',
        'valor_semana',
        'valor_fim',
        'empresa_id'
    ];
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
    public function alugueis()
{
    return $this->hasMany(Aluguel::class, 'espaco_id', 'id');
}
}

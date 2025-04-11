<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarto extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome',
        'categoria_id',
        'descricao',
        'posicao',
        'status'
    ];

    public function reservas()
    {
    return $this->hasMany(Reserva::class);
    }
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected $fillable = [
        'titulo',
        'ocupantes',
        'descricao',
        'status',
        'posicao'
    ];
    public function quartos()
    {
        return $this->hasMany(Quarto::class);
    }
    public function tarifa()
    {
        return $this->hasOne(Tarifa::class);
    }
}

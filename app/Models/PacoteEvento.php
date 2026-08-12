<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacoteEvento extends Model
{
    use HasFactory;

    protected $table = 'pacotes_evento';

    protected $fillable = [
        'categoria',
        'nome',
        'descricao',
        'observacao_padrao',
        'ano',
        'valor',
    ];

    public function alugueis()
    {
        return $this->belongsToMany(Aluguel::class, 'pacote_evento_aluguel')
            ->withPivot(['quantidade', 'valor_total', 'observacao']);
    }
}

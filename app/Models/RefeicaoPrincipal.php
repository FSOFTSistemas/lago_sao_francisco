<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RefeicaoPrincipal extends Model
{
    use HasFactory;

    protected $table = 'refeicao_principals';

    protected $fillable = [
        'NomeOpcaoRefeicao',
        'PrecoPorPessoa',
        'DescricaoOpcaoRefeicao',
        'cardapio_id',
    ];

    public function cardapio()
    {
        return $this->belongsTo(Cardapio::class, 'cardapio_id');
    }
}

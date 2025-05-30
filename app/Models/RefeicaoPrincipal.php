<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RefeicaoPrincipal extends Model
{
    use HasFactory;

    protected $table = 'OpcoesDeRefeicaoPrincipal';
    protected $primaryKey = 'OpcaoRefeicaoPrincipalID';

    protected $fillable = [
        'NomeOpcaoRefeicao',
        'PrecoPorPessoa',
        'DescricaoOpcaoRefeicao',
        'CardapioID',
    ];

    public function cardapio()
    {
        return $this->belongsTo(Cardapio::class, 'CardapioID');
    }
}

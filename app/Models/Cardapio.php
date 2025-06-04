<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cardapio extends Model
{
    use HasFactory;
    protected $table = 'cardapios';

    protected $fillable = [
        'NomeCardapio',
        'AnoCardapio',
        'PrecoBasePorPessoa',
        'ValidadeOrcamentoDias',
        'PoliticaCriancaGratisLimiteIdade',
        'PoliticaCriancaDescontoPercentual',
        'PoliticaCriancaDescontoIdadeInicio',
        'PoliticaCriancaDescontoIdadeFim',
        'PoliticaCriancaPrecoIntegralIdadeInicio',
        'PossuiOpcaoEscolhaConteudoPrincipalRefeicao',
    ];

    protected $casts = [
        'PrecoBasePorPessoa' => 'decimal:2',
        'PoliticaCriancaDescontoPercentual' => 'decimal:2',
        'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => 'boolean',
    ];

    public function secoes()
{
    return $this->hasMany(SecoesCardapio::class, 'cardapio_id');
}

public function opcoes()
{
    return $this->hasMany(refeicaoPrincipal::class, 'Cardapio_id');
}

}

<?php

namespace App\Livewire;

use App\Models\Cardapio;
use Livewire\Component;

class CardapioNew extends Component
{
    public $NomeCardapio, $AnoCardapio, $PrecoBasePorPessoa, $ValidadeOrcamentoDias;
    public $PoliticaCriancaGratisLimiteIdade, $PoliticaCriancaDescontoPercentual;
    public $PoliticaCriancaDescontoIdadeInicio, $PoliticaCriancaDescontoIdadeFim;
    public $PoliticaCriancaPrecoIntegralIdadeInicio, $PossuiOpcaoEscolhaConteudoPrincipalRefeicao = false;
    public $cardapioID;
    public $abaAtual = 'geral';

    protected $rules = [
        'NomeCardapio' => 'required|string|max:255',
        'AnoCardapio' => 'required|integer|min:2000',
        'PrecoBasePorPessoa' => 'required|numeric|min:0',
        'ValidadeOrcamentoDias' => 'required|integer|min:1',
        'PoliticaCriancaGratisLimiteIdade' => 'nullable|integer|min:0',
        'PoliticaCriancaDescontoPercentual' => 'nullable|numeric|min:0|max:100',
        'PoliticaCriancaDescontoIdadeInicio' => 'nullable|integer|min:0',
        'PoliticaCriancaDescontoIdadeFim' => 'nullable|integer|min:0',
        'PoliticaCriancaPrecoIntegralIdadeInicio' => 'nullable|integer|min:0',
        'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        $cardapio = Cardapio::create($this->only(array_keys($this->rules)));

        $this->cardapioID = $cardapio->CardapioID;
        $this->abaAtual = 'sessoes';
    }

    public function render()
    {
        return view('livewire.cardapio-new');
    }
}

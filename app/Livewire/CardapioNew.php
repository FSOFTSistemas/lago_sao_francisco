<?php

namespace App\Livewire;

use App\Models\Cardapio;
use Livewire\Component;
use Livewire\Attributes\On;

class CardapioNew extends Component
{
    public $NomeCardapio, $AnoCardapio, $PrecoBasePorPessoa, $ValidadeOrcamentoDias;
    public $PoliticaCriancaGratisLimiteIdade, $PoliticaCriancaDescontoPercentual;
    public $PoliticaCriancaDescontoIdadeInicio, $PoliticaCriancaDescontoIdadeFim;
    public $PoliticaCriancaPrecoIntegralIdadeInicio;
    public $PossuiOpcaoEscolhaConteudoPrincipalRefeicao = 0;
    public $cardapioID;
    public $abaAtual = 'geral';
    public $cardapioSalvo = false;

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

        if($this->cardapioSalvo) {
            $cardapio = Cardapio::findOrFail($this->cardapioID);
            $cardapio->update($this->only(array_keys($this->rules)));
            session()->flash('success', 'Cardápio atualizado com sucesso');
            $this->proximo($this->PossuiOpcaoEscolhaConteudoPrincipalRefeicao);
        } else {
            $cardapio = Cardapio::create($this->only(array_keys($this->rules)));
            $this->cardapioSalvo = true;
            session()->flash('success', 'Cardápio criado com sucesso');
        }
        $this->cardapioID = $cardapio->id;
        $this->abaAtual = 'sessoes';
    }

    public function render()
    {
        return view('livewire.cardapio-new');
    }

     #[On('mudarAba')]
    public function atualizarAba($aba)
    {
        $this->abaAtual = $aba;
    }

    public function proximo($opcaoRefeicao)
    {
        $this->dispatch('atualizar', refeicao: $opcaoRefeicao);
    }
}

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

    public function mount($id = null)
    {
        if($id){
        $this->cardapioSalvo = true;
        $cardapio = Cardapio::findOrFail($id);
        $this->cardapioID = $cardapio->id;
        $this->NomeCardapio = old('NomeCardapio', $cardapio->NomeCardapio);
        $this->AnoCardapio = $cardapio->AnoCardapio;
        $this->PrecoBasePorPessoa = old('PrecoBasePorPessoa', $cardapio->PrecoBasePorPessoa);
        $this->ValidadeOrcamentoDias = old('ValidadeOrcamentoDias', $cardapio->ValidadeOrcamentoDias);
        $this->PoliticaCriancaGratisLimiteIdade = old('PoliticaCriancaGratisLimiteIdade', $cardapio-> PoliticaCriancaGratisLimiteIdade);
        $this->PoliticaCriancaDescontoIdadeInicio = old('PoliticaCriancaDescontoIdadeInicio', $cardapio->PoliticaCriancaDescontoIdadeInicio);
        $this->PoliticaCriancaDescontoIdadeFim = old('PoliticaCriancaDescontoIdadeFim', $cardapio->PoliticaCriancaDescontoIdadeFim);
        $this->PoliticaCriancaDescontoPercentual = old('PoliticaCriancaDescontoPercentual', $cardapio->PoliticaCriancaDescontoPercentual);
        $this->PoliticaCriancaPrecoIntegralIdadeInicio = old('PoliticaCriancaPrecoIntegralIdadeInicio', $cardapio->PoliticaCriancaPrecoIntegralIdadeInicio);
        $this->PossuiOpcaoEscolhaConteudoPrincipalRefeicao = old('PossuiOpcaoEscolhaConteudoPrincipalRefeicao', $cardapio->PossuiOpcaoEscolhaConteudoPrincipalRefeicao);
        
        } else {
            $this->AnoCardapio = date('Y');
            $this->PoliticaCriancaGratisLimiteIdade = 6;
            $this->PoliticaCriancaDescontoIdadeInicio = 7;
            $this->PoliticaCriancaDescontoIdadeFim = 12;
            $this->PoliticaCriancaDescontoPercentual = 50;
            $this->PoliticaCriancaPrecoIntegralIdadeInicio = 13;
        }
    }


     #[On('avancou')]
    public function save()
    {
        $this->validate();
        
        if($this->cardapioSalvo) {
            $cardapio = Cardapio::findOrFail($this->cardapioID);
            $cardapio->update($this->only(array_keys($this->rules)));
            $this->proximoAba($this->PossuiOpcaoEscolhaConteudoPrincipalRefeicao);
        } else {
            $cardapio = Cardapio::create($this->only(array_keys($this->rules)));
            $this->cardapioSalvo = true;
            
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

    public function proximoAba($opcaoRefeicao)
    {
        $this->dispatch('atualizar', refeicao: $opcaoRefeicao);
    }

    public function avancar()
    {
        $this->dispatch("confirmed");
    }
}

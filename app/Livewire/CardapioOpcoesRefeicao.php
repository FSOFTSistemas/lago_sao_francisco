<?php

namespace App\Livewire;

use App\Models\RefeicaoPrincipal;
use Livewire\Component;
use Livewire\Attributes\On;

class CardapioOpcoesRefeicao extends Component
{
    public $cardapioId;
    public $nomeOpcao, $precoPorPessoa, $descricaoOpcao;
    public $opcoes;
    public $inputKey;

    protected $rules = [
        'nomeOpcao' => 'required|string|max:255',
        'precoPorPessoa' => 'required|numeric|min:0',
        'descricaoOpcao' => 'nullable|string|max:255',
    ];

    public function mount($cardapioId)
    {
        $this->cardapioId = $cardapioId;
        $this->loadOpcoes();
    }

    public function loadOpcoes()
    {
        $this->opcoes = RefeicaoPrincipal::where('cardapio_id', $this->cardapioId)->get();
    }

    public function addOpcao()
    {
        $this->validate();

        RefeicaoPrincipal::create([
            'cardapio_id' => $this->cardapioId,
            'NomeOpcaoRefeicao' => $this->nomeOpcao,
            'PrecoPorPessoa' => $this->precoPorPessoa,
            'DescricaoOpcaoRefeicao' => $this->descricaoOpcao,
        ]);

        $this->reset(['nomeOpcao', 'precoPorPessoa', 'descricaoOpcao']);
        $this->inputKey = now()->timestamp;
        $this->loadOpcoes();
        $this->atualizarOpIndex();
    }

    public function render()
    {
        return view('livewire.cardapio-opcoes-refeicao');
    }

        public function deletarOpcao($id)
    {
        $this->dispatch("confirm", id: $id);
    }

    #[On('deleteOpcao')]
    public function deleteOpcao($id)
    {
        $opcao = RefeicaoPrincipal::find($id);
        $opcao->delete();
        $this->loadOpcoes();
    }

    public function proximoCategoria($id)
    {
        $this->dispatch("confirmProxAba", id: $id);
    }

    #[On('proxAbaConfirmadoref')]
    public function proxAbaConfirmadoref($id)
    {
        $this->dispatch('mudarAba', aba: 'categorias');
        $this->dispatch('categoriaObserver', id: $id );
    }

    public function atualizarOpIndex()
    {
        $this->dispatch('atualizarListaOp');
    }


}

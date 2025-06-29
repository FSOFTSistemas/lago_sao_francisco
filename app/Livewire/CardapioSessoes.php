<?php

namespace App\Livewire;

use App\Models\CategoriasDeItensCardapio;
use App\Models\SecoesCardapio;
use Livewire\Component;
use Livewire\Attributes\On;

class CardapioSessoes extends Component
{
    public $cardapioId;
    public $nomeSessao, $ordemExibicao, $ehOpcaoPrincipal = 0;
    public $sessoes;
    public $inputKey;
    public $ordemExibicaoError;
    public $sessaoIdToDelete;
    public $refeicao;
    public $categorias = [];



    protected $rules = [
        'nome_secao_cardapio' => 'required|string|max:255',
        'ordem_exibicao' => 'required|integer|min:0',
        'opcao_conteudo_principal_refeicao' => 'required|boolean',
    ];

    public function mount($cardapioId = null)
    {
        $this->cardapioId = $cardapioId;
        $this->loadSessoes();
    }

    public function loadSessoes()
    {
        $this->sessoes = SecoesCardapio::where('cardapio_id', $this->cardapioId)
            ->orderBy('ordem_exibicao')
            ->get();
    }

    public function addSessao()
    {
        try {

             if ($this->sessoes->contains('ordem_exibicao', (int) $this->ordemExibicao)) {
                $this->ordemExibicaoError = 'Este número de ordem já está em uso.';
                return;
            }

            SecoesCardapio::create([
                'cardapio_id' => $this->cardapioId,
                'nome_secao_cardapio' => $this->nomeSessao,
                'opcao_conteudo_principal_refeicao' => $this->ehOpcaoPrincipal,
                'ordem_exibicao' => $this->ordemExibicao,
            ]);

            //  $this->categorias = CategoriasDeItensCardapio::where('sessao_cardapio_id', $secao->id);

            //  $this->atualizarCategoria($this->categorias);

            $this->nomeSessao = '';
            $this->ordemExibicao = '';
            $this->ehOpcaoPrincipal = 0;
            $this->inputKey = now()->timestamp;
            $this->loadSessoes();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $mensagem = 'Erro de validação: ' . implode(' ', $e->validator->errors()->all());
            session()->flash('error', $mensagem);
        } catch (\Exception $e) {
            $mensagem = 'Erro ao adicionar sessão: ' . $e->getMessage();
            session()->flash('error', $mensagem);
        }
    }

    public function render()
    {
        return view('livewire.cardapio-sessoes');
    }

    public function verificarOrdemExibicao()
    {
        $this->ordemExibicaoError = null;

        if ($this->ordemExibicao !== null && $this->ordemExibicao !== '') {
            $existe = $this->sessoes->contains('ordem_exibicao', (int) $this->ordemExibicao);

            if ($existe) {
                $this->ordemExibicaoError = 'Este número de ordem já está em uso.';
            }
        }
    }

    public function deletarSessao($id)
    {
        $this->dispatch("confirm", id: $id);
    }

    #[On('delete')]
    public function delete($id)
    {
        $sessao = SecoesCardapio::find($id);
        $sessao->delete();
        $this->loadSessoes();
    }
    public function proximo()
    {
        $this->dispatch("confirmProximo");
    }

    #[On('proximoConfirmado')]
    public function proximoConfirmado()
    {
        $this->dispatch('mudarAba', aba: 'opcoes');
    }

    public function proximoCategoria($cardapioId)
    {
        $this->dispatch("confirmProxAba", id: $cardapioId);
    }

    #[On('proxAbaConfirmado')]
    public function proxAbaConfirmado($id)
    {
        $this->dispatch('mudarAba', aba: 'categorias');
        $this->dispatch('categoriaObserver', id: $id );
    }


     #[On('atualizar')]
    public function atualizar($refeicao)
    {
        $this->refeicao = $refeicao;
    }

    public function atualizarCategoria($id)
    {
        $this->dispatch('categoriaObserver', id: $id);
    }

    
}

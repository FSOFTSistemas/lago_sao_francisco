<?php

namespace App\Livewire;

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



    protected $rules = [
        'nome_secao_cardapio' => 'required|string|max:255',
        'ordem_exibicao' => 'required|integer|min:0',
        'opcao_conteudo_principal_refeicao' => 'required|boolean',
    ];

    public function mount($cardapioId)
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
        $this->dispatch('mudarAba', aba: 'opcoes');
    }
    public function finalizar()
    {
        return redirect()->route('cardapios.index')->with('success', 'Cardápio Finalizado com sucesso');
    }


     #[On('atualizar')]
    public function atualizar($refeicao)
    {
        $this->refeicao = $refeicao;
    }
}

<?php

namespace App\Livewire;

use App\Models\SecaoCardapio;
use App\Models\SecoesCardapio;
use Livewire\Component;

class CardapioSessoes extends Component
{
    public $cardapioId;
    public $nomeSessao, $ordemExibicao, $ehOpcaoPrincipal = 0;
    public $sessoes;

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
        $this->sessoes = SecoesCardapio::where('id', $this->cardapioId)
            ->orderBy('ordem_exibicao')
            ->get();
    }

    public function addSessao()
    {
        try {

            SecoesCardapio::create([
                'cardapio_id' => $this->cardapioId,
                'nome_secao_cardapio' => $this->nomeSessao,
                'opcao_conteudo_principal_refeicao' => $this->ehOpcaoPrincipal,
                'ordem_exibicao' => $this->ordemExibicao,
            ]);

            $this->reset(['nomeSessao', 'ordemExibicao', 'ehOpcaoPrincipal']);
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
}

<?php

namespace App\Livewire;

use App\Http\Controllers\CategoriasDeItensCardapioController;
use App\Models\CategoriasDeItensCardapio;
use App\Models\SecoesCardapio;
use App\Models\RefeicaoPrincipal;
use App\Models\ItensDoCardapio;
use Illuminate\Http\Request;
use Livewire\Component;

class CategoriaItensNew extends Component
{
    public $sessao_cardapio_id, $refeicao_principal_id, $nome_categoria_item;
    public $numero_escolhas_permitidas = 1, $ordem_exibicao = 1;
    public $eh_grupo_escolha_exclusiva = false;
    public $categoriaID;
    
    // Variáveis para itens
    public $selectedItem;
    //public $itensDaCategoria = [];
    public $itensTemporarios = [];
    public $allItems;
    public $categoriaSalva = false;

    public $modalAberto = false;

    public $inputKey;

    protected $rules = [
        'sessao_cardapio_id' => 'required|exists:secoes_cardapios,id',
        'refeicao_principal_id' => 'nullable|exists:refeicao_principals,id',
        'nome_categoria_item' => 'required|string|max:255',
        'numero_escolhas_permitidas' => 'required|integer|min:1|max:10',
        'eh_grupo_escolha_exclusiva' => 'required|boolean',
        'ordem_exibicao' => 'required|integer|min:1',
    ];

    protected $messages = [
        'sessao_cardapio_id.required' => 'O campo seção do cardápio é obrigatório.',
        'sessao_cardapio_id.exists' => 'A seção do cardápio selecionada é inválida.',
        'refeicao_principal_id.exists' => 'A refeição principal selecionada é inválida.',
        'nome_categoria_item.required' => 'O campo nome da categoria é obrigatório.',
        'nome_categoria_item.max' => 'O nome da categoria não pode ter mais que 255 caracteres.',
        'numero_escolhas_permitidas.required' => 'O campo número de escolhas permitidas é obrigatório.',
        'numero_escolhas_permitidas.min' => 'O número mínimo de escolhas permitidas é 1.',
        'numero_escolhas_permitidas.max' => 'O número máximo de escolhas permitidas é 10.',
        'eh_grupo_escolha_exclusiva.required' => 'O campo grupo de escolha exclusiva é obrigatório.',
        'ordem_exibicao.required' => 'O campo ordem de exibição é obrigatório.',
        'ordem_exibicao.min' => 'A ordem de exibição mínima é 1.',
    ];

    public function mount($id = null)
    {
        $this->allItems = ItensDoCardapio::all(); 
        if ($id) {
            $this->categoriaID = $id;
            $this->loadCategoriaData($id);
            $this->categoriaSalva = true;
            $this->loadItensDaCategoria();
        }
    }

    protected function loadCategoriaData($id)
    {
        $categoria = CategoriasDeItensCardapio::findOrFail($id);
        $this->sessao_cardapio_id = $categoria->sessao_cardapio_id;
        $this->refeicao_principal_id = $categoria->refeicao_principal_id;
        $this->nome_categoria_item = $categoria->nome_categoria_item;
        $this->numero_escolhas_permitidas = $categoria->numero_escolhas_permitidas;
        $this->eh_grupo_escolha_exclusiva = $categoria->eh_grupo_escolha_exclusiva;
        $this->ordem_exibicao = $categoria->ordem_exibicao;
    }

    protected function loadItensDaCategoria()
    {
        if ($this->categoriaSalva) {
            $this->itensTemporarios = CategoriasDeItensCardapio::find($this->categoriaID)['itens']??[];
            $this->itensTemporarios = collect($this->itensTemporarios)->map(function($item) {
                return $item;
            });
            
               
        } else {
            $this->itensTemporarios = collect($this->itensTemporarios)->map(function($item) {
                return $item;
            });
           
        }
    }

    public function save()
{
    $this->validate();

    $dados = $this->only(array_keys($this->rules));
    
    if ($this->categoriaSalva) {
        // // Atualização da categoria existente
        // $categoria = CategoriasDeItensCardapio::findOrFail($this->categoriaID);
        // $categoria->update($dados);
    } else {
        $dados['itens']= $this->itensTemporarios;
        $request = new Request($dados);
        $controller = new CategoriasDeItensCardapioController();
        return $controller->store($request);
        
    }

    $this->loadItensDaCategoria();
    
}

    public function addItem()
    {
        $this->validate([
            'selectedItem' => 'required|exists:itens_do_cardapios,id',
        ]);

        $item = ItensDoCardapio::find($this->selectedItem);
        $this->itensTemporarios[] = [
                'id' => $item->id,
                'nome_item' => $item->nome_item,
                'tipo_item' => $item->tipo_item
        ];
        

        $this->reset(['selectedItem']);
        $this->inputKey = now()->timestamp;
        $this->loadItensDaCategoria();
        $this->fecharModal();
    }

    public function removeItem($itemId)
    {
        if ($this->categoriaSalva) {
            CategoriasDeItensCardapio::find($this->categoriaID)
                ->itens()
                ->detach($itemId);
        } else {
             $this->itensTemporarios = array_filter($this->itensTemporarios, 
                 fn($item) => $item['id'] != $itemId);
            if (empty($this->itensTemporarios)) {
                $this->itensTemporarios = [];
            }
        }

        $this->loadItensDaCategoria();
        session()->flash('success', 'Item removido com sucesso');
    }

    public function render()
    {
        return view('livewire.categoria-itens-new', [
            'secoes' => SecoesCardapio::all(),
            'refeicoes' => RefeicaoPrincipal::all(),
        ]);
    }

        public function openModal()
    {
        $this->modalAberto = true;
    }

    public function fecharModal()
    {
        $this->modalAberto = false;
    }
}
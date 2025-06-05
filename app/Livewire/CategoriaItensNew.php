<?php

namespace App\Livewire;

use App\Http\Controllers\CategoriasDeItensCardapioController;
use App\Models\CategoriasDeItensCardapio;
use App\Models\DisponibilidadeItemCategoria;
use App\Models\SecoesCardapio;
use App\Models\RefeicaoPrincipal;
use App\Models\ItensDoCardapio;
use Illuminate\Http\Request;
use Livewire\Component;

class CategoriaItensNew extends Component
{
    public $sessao_cardapio_id, $refeicao_principal_id, $nome_categoria_item;
    public $numero_escolhas_permitidas = 1, $ordem_exibicao = 1;
    public $eh_grupo_escolha_exclusiva = 0;
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
        'sessao_cardapio_id' => 'nullable',
        'refeicao_principal_id' => 'nullable|exists:refeicao_principals,id',
        'nome_categoria_item' => 'required|string|max:255',
        'numero_escolhas_permitidas' => 'required|integer|min:1|max:10',
        'eh_grupo_escolha_exclusiva' => 'boolean',
        'ordem_exibicao' => 'required|integer|min:1',
    ];

    protected $messages = [
        'refeicao_principal_id.exists' => 'A refeição principal selecionada é inválida.',
        'nome_categoria_item.required' => 'O campo nome da categoria é obrigatório.',
        'nome_categoria_item.max' => 'O nome da categoria não pode ter mais que 255 caracteres.',
        'numero_escolhas_permitidas.required' => 'O campo número de escolhas permitidas é obrigatório.',
        'numero_escolhas_permitidas.min' => 'O número mínimo de escolhas permitidas é 1.',
        'numero_escolhas_permitidas.max' => 'O número máximo de escolhas permitidas é 10.',
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
        $categoria = CategoriasDeItensCardapio::with('itens.item')->find($this->categoriaID);

        $this->itensTemporarios = $categoria->itens->map(function ($disponibilidade) {
            return [
                'id' => $disponibilidade->item->id ?? null,
                'nome_item' => $disponibilidade->item->nome_item ?? '',
                'tipo_item' => $disponibilidade->item->tipo_item ?? '',
            ];
        })->filter(fn($item) => $item['id'])->values()->toArray();
    }else {
            $this->itensTemporarios = collect($this->itensTemporarios)->map(function($item) {
                return $item;
            });
           
        }
    }

    public function save()
{
    $this->validate();

    // Validação XOR
    if ($this->sessao_cardapio_id && $this->refeicao_principal_id) {
        $this->addError('sessao_cardapio_id', 'Você deve escolher apenas uma: Seção do Cardápio ou Refeição Principal.');
        $this->addError('refeicao_principal_id', 'Você deve escolher apenas uma: Seção do Cardápio ou Refeição Principal.');
        return;
    }

    if (!$this->sessao_cardapio_id && !$this->refeicao_principal_id) {
        $this->addError('sessao_cardapio_id', 'Você deve preencher Seção do Cardápio ou Refeição Principal.');
        $this->addError('refeicao_principal_id', 'Você deve preencher Seção do Cardápio ou Refeição Principal.');
        return;
    }

    //validação para que escolha exclusiva seja 1 se true
    if ($this->eh_grupo_escolha_exclusiva && $this->numero_escolhas_permitidas != 1) {
        $this->addError('numero_escolhas_permitidas', 'Para grupos de escolha exclusiva, o número de escolhas deve ser 1.');
        return;
    }

    $dados = $this->only(array_keys($this->rules));
    
    if ($this->categoriaSalva) {
        // // Atualização da categoria existente
        $categoria = CategoriasDeItensCardapio::findOrFail($this->categoriaID);
        $categoria->update($dados);
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
        if ($this->categoriaSalva) {
        // Evita duplicidade
        $existe = DisponibilidadeItemCategoria::where('CategoriaItemID', $this->categoriaID)
            ->where('ItemID', $item->id)
            ->exists();

        if (!$existe) {
            DisponibilidadeItemCategoria::create([
                'CategoriaItemID' => $this->categoriaID,
                'ItemID' => $item->id,
            ]);
        }
    }

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
        DisponibilidadeItemCategoria::where('CategoriaItemID', $this->categoriaID)
            ->where('itemID', $itemId)
            ->delete();
        } else {
             $this->itensTemporarios = collect($this->itensTemporarios)
            ->filter(fn($item) => $item['id'] != $itemId)
            ->values()
            ->toArray();
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

    public function limparRefeicao()
    {
        $this->refeicao_principal_id = null;
    }

    public function limparSessao()
    {
        $this->sessao_cardapio_id = null;
    }

}
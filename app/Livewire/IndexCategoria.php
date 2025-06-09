<?php

namespace App\Livewire;

use App\Models\Cardapio;
use App\Models\CategoriasDeItensCardapio;
use Livewire\Component;
use Livewire\Attributes\On;

class IndexCategoria extends Component
{
    public $cardapio;
    public $inputKey;

    public function render()
    {
        return view('livewire.index-categoria');
    }

    public function novaCategoria()
    {
        $this->dispatch('criarCategoria', aba: 'categoriasCreate');
    }


    #[On('categoriaObserver')]
    public function getCategorias($id)
    {
        $cardapio = Cardapio::with([
            'secoes.categorias',
            'opcoes.categorias'
            ])->findOrFail($id);
            $this->cardapio = $cardapio;
    }

        public function deletarCat($id)
    {
        $this->dispatch("confirmCat", id: $id);
    }

    #[On('deleteCat')]
    public function deleteCat($id)
    {
        $categoria = CategoriasDeItensCardapio::find($id);
        $categoria->delete();
    }

    public function editCat($id)
    {  
        $this->dispatch('editCategoria', id: $id);
    }

    #[On('atualizarListaOp')]
    public function atualizarListaOp()
    {
        $this->inputKey = now()->timestamp;
    }

    public function finalizarCardapio (){
        $this->dispatch('confirmFinalizarCardapio');
    }

    #[On('finalizadoCardapio')]
    public function finalizou()
    {
        session(['categoriaId' => null]);
        session(['cardapioID' => null]);
        redirect()->route('cardapios.index')->with('success', 'Cardapio Criado com sucesso!');
    }
}

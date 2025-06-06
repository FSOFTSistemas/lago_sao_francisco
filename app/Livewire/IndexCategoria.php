<?php

namespace App\Livewire;

use App\Models\Cardapio;
use Livewire\Component;
use Livewire\Attributes\On;

class IndexCategoria extends Component
{
    public $cardapio;

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
}

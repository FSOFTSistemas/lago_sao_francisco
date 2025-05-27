<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Produto;
use Livewire\Component;

class NFeNew extends Component
{
    public $clientes;
    public $produtos;
    public function render()
    {
        $this->produtos = Produto::all();
        $this->clientes = Cliente::all();
        return view('livewire.n-fe-new');
    }
}

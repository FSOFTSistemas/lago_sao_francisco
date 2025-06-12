<?php

namespace App\Livewire;

use Livewire\Component;

class DayUse extends Component
{
    public $abaAtual = 'geral';
    public $cliente;
    public $vendedor;
    public $data;
    public $total;
    public $dayuseSalvo;
    public $dayuseID;

    protected $rules = [
        'cliente_id' => 'required|exists:clientes,id',
        'vendedor_id' => 'required|exists:vendedors,id',
        'data' => 'required|date',
        'total' => 'required|numeric',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $dayuse = DayUse::findOrFail($id);
            $this->cliente = old('cliente_id', $dayuse->cliente_id);
            $this->data = old('data', $dayuse->data);
            $this->vendedor = old('vendedor_id', $dayuse->vendedor_id);
            $this->total = old('total', $dayuse->total);
        }
    }

    public function save()
    {
        $this->validate();

        if($this->dayuseSalvo){
            $dayuse = DayUse::findOrFail($this->dayuseID);
            $dayuse->update($this->only(array_keys($this->rules)));
        } else {
             $dayuse = DayUse::create($this->only(array_keys($this->rules)));
             $this->dayuseSalvo = true;
        }
        $this->dayuseID = $dayuse->id;
        $this->abaAtual = 'pagamento';
    }

    public function render()
    {
        return view('livewire.day-use');
    }
}

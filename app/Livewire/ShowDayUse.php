<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DayUse; 

class ShowDayUse extends Component
{
    public $dayUseId;
    public $dayUse;

    public $itens;

    public function mount($id = null)
    {
        $this->dayUseId = $id;
        $this->loadDayUse();
    }

    public function loadDayUse()
    {
        if ($this->dayUseId) {
            $this->dayUse = DayUse::findOrFail($this->dayUseId);
            $this->itens = [];
        }
    }

    public function render()
    {
        return view('livewire.show-dayuse');
    }
}
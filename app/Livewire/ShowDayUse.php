<?php

namespace App\Livewire;

use App\Models\ItensDayUse;
use Livewire\Component;
use App\Models\DayUse;


class ShowDayUse extends Component
{
    public $dayUseId;
    public $dayUse;
    public $itens;

    public $itensCompletos;
    public $valorPago;
    public $valorLiquido;
    public $saldo;

    public $dataformatada;

    public $pagamentos;

    public function mount($id = null)
    {
        $this->dayUseId = $id;
        $this->loadDayUse();
    }

    public function loadDayUse()
    {
        if ($this->dayUseId) {
            // Carrega o DayUse com todos os relacionamentos necessários
            $this->dayUse = DayUse::with([
                'cliente',
                'vendedor',
                'itens.item', // Certifique-se que este relacionamento existe
                'formaPag.formaPagamento'
            ])->findOrFail($this->dayUseId);
            $this->dataformatada = $this->getDataFormatadaProperty();

            // Processamento dos itens corrigido
            $this->itens = $this->dayUse->itens->map(function ($item) {
                // Acessa o item relacionado através do relacionamento 'item' já carregado
                $itemRelacionado = $item->item;
                return [
                    'id' => $item->id,
                    'quantidade' => $item->quantidade,
                    'descricao' => $itemRelacionado->descricao ?? 'Descrição não disponível',
                    'valor' => $itemRelacionado->valor ?? 0,
                    'passeio' => $itemRelacionado->passeio ?? false,
                    'valor_total' => $item->quantidade * ($itemRelacionado->valor ?? 0)
                ];
            });
            
                $this->pagamentos = $this->dayUse->formaPag->map(function ($pag) {
                $pagRelacionado = $pag->formaPagamento;
                
                return [
                    'id' => $pag->id,
                    'valor' => $pagRelacionado->valor ?? 0,
                    "descricao" => $pagRelacionado->descricao
                ];
            });

            // Calcula os valores financeiros
            $this->valorPago = $this->dayUse->formaPag->sum('valor') ?? 0;
            $this->valorLiquido = ($this->dayUse->total ?? 0) + 
                                ($this->dayUse->acrescimo ?? 0) - 
                                ($this->dayUse->desconto ?? 0);
            $this->saldo = $this->valorLiquido - $this->valorPago;
        } else {
            $this->dayUse = null;
            $this->itens = [];
            $this->valorPago = 0;
            $this->valorLiquido = 0;
            $this->saldo = 0;
        }
    }

    public function render()
    {
        // dd($this->dayUse->data);
        return view('livewire.show-dayuse', [
            'dayUse' => $this->dayUse,
            'itens' => $this->itens,
            'valorPago' => $this->valorPago,
            'valorLiquido' => $this->valorLiquido,
            'saldo' => $this->saldo
        ]);
    }

    // Formata a data completa
public function getDataCompletaFormatadaProperty()
{
    return $this->dayUse->data ? $this->dayUse->data->format('d/m/Y H:i:s') : '';
}

// Formata apenas a data
public function getDataFormatadaProperty()
{
    // Verifica se existe data e se já é um objeto Carbon
    if (!$this->dayUse || !$this->dayUse->data) {
        return 'Data não informada';
    }
    
    // Se for string, converte para Carbon primeiro
    if (is_string($this->dayUse->data)) {
        return \Carbon\Carbon::parse($this->dayUse->data)->format('d/m/Y');
    }
    
    // Se já for Carbon, formata diretamente
    return $this->dayUse->data->format('d/m/Y');
}

// Formata apenas a hora
public function getHoraFormatadaProperty()
{
    return $this->dayUse->data ? $this->dayUse->data->format('H:i') : '';
}

public function itens()
{
    return $this->hasMany(ItensDayUse::class); // Ajuste para o nome real do seu model
}
}
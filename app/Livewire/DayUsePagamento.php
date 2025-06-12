<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DayUse; 
use App\Models\DayUsePag;
use App\Models\FormaPagamento;

class DayUsePagamento extends Component
{
    public $dayUseId;
    public $itemSubtotal; 

    public $acrescimo = 0;
    public $desconto = 0;
    public $finalPagamentoTotal = 0; 

    public $formaPagamento; 
    public $selectedPaymentMethodId; 
    public $pagamentoValor; 
    public $currentPayments = []; // Array para armazenar os pagamentos adicionados na tela
    public $remainingToPay = 0; // Valor restante a ser pago

    protected $rules = [
        'acrescimo' => 'numeric|min:0',
        'desconto' => 'numeric|min:0',
        'selectedPaymentMethodId' => 'required|exists:forma_pagamentos,id',
        'pagamentoValor' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'selectedPaymentMethodId.required' => 'Selecione um método de pagamento.',
        'pagamentoValor.required' => 'Informe o valor do pagamento.',
        'pagamentoValor.numeric' => 'O valor do pagamento deve ser um número.',
        'pagamentoValor.min' => 'O valor do pagamento deve ser maior que zero.',
    ];

    public function mount($dayUseId, $itemSubtotal)
    {
        $this->dayUseId = $dayUseId;
        $this->itemSubtotal = $itemSubtotal; // Subtotal dos itens

        $this->formaPagamento = FormaPagamento::all();

        // Carrega dados de pagamento existentes se for um DayUse já salvo
        if ($this->dayUseId) {
            $dayUse = DayUse::find($this->dayUseId);
            if ($dayUse) {
                $this->acrescimo = $dayUse->acrescimo ?? 0;
                $this->desconto = $dayUse->desconto ?? 0;

                foreach ($dayUse->formaPag as $dayUsePagEntry) {
                    $this->currentPayments[] = [
                        'id' => $dayUsePagEntry->pagamento_id,
                        'descricao' => $dayUsePagEntry->pagamento->descricao, // Assume que Pagamento tem 'descricao'
                        'valor' => $dayUsePagEntry->valor,
                    ];
                }
            }
        }

        $this->calculateFinalPaymentTotal(); // Calcula o total final do pagamento
    }

    public function updatedAcrescimo()
    {
        $this->acrescimo = (float) str_replace(',', '.', $this->acrescimo); // Garante que é um float
        $this->calculateFinalPaymentTotal();
    }

    public function updatedDesconto()
    {
        $this->desconto = (float) str_replace(',', '.', $this->desconto); // Garante que é um float
        $this->calculateFinalPaymentTotal();
    }

    public function calculateFinalPaymentTotal()
    {
        $this->finalPagamentoTotal = $this->itemSubtotal + $this->acrescimo - $this->desconto;
        if ($this->finalPagamentoTotal < 0) {
            $this->finalPagamentoTotal = 0; // Evita total negativo
        }
        $this->calculateRemainingToPay();
    }

    public function addPayment()
    {
        $this->validate([
            'selectedPaymentMethodId' => 'required|exists:forma_pagamentos,id',
            'pagamentoValor' => 'required|numeric|min:0.01',
        ]);

        $method = $this->formaPagamento->find($this->selectedPaymentMethodId);

        $this->currentPayments[] = [
            'pagamento_id' => $this->selectedPaymentMethodId,
            'descricao' => $method->descricao,
            'valor' => (float) $this->pagamentoValor,
        ];

        $this->selectedPaymentMethodId = null;
        $this->pagamentoValor = null;
        $this->calculateRemainingToPay();
    }

    public function removePayment($index)
    {
        unset($this->currentPayments[$index]);
        $this->currentPayments = array_values($this->currentPayments); // Reindexa o array
        $this->calculateRemainingToPay();
    }

    public function calculateRemainingToPay()
    {
        $paidAmount = array_sum(array_column($this->currentPayments, 'valor'));
        $this->remainingToPay = $this->finalPagamentoTotal - $paidAmount;
    }

    public function savePayments()
    {
        $this->validate([
            'acrescimo' => 'numeric|min:0',
            'desconto' => 'numeric|min:0',
            'finalPagamentoTotal' => 'numeric|min:0',
            'currentPayments' => 'array',
        ]);

        // Validação final: o valor restante deve ser zero (ou muito próximo de zero para evitar problemas de float)
        if (abs($this->remainingToPay) > 0.01) { // Tolerância de 1 centavo
            $this->addError('remainingToPay', 'O valor restante a pagar deve ser zero.');
            return;
        }

        $dayUse = DayUse::find($this->dayUseId);
        if (!$dayUse) {
            session()->flash('error', 'DayUse não encontrado para salvar pagamentos.');
            return;
        }

        // Atualiza o DayUse com acrescimo e desconto
        $dayUse->update([
            'acrescimo' => $this->acrescimo,
            'desconto' => $this->desconto,
        ]);

        // Salva os pagamentos na tabela DayUsePag
        $dayUse->formaPag()->delete(); // Remove pagamentos antigos
        foreach ($this->currentPayments as $payment) {
            DayUsePag::create([
                'dayuse_id' => $this->dayUseId,
                'forma_pagamento_id' => $payment['pagamento_id'],
                'valor' => $payment['valor'],
            ]);
        }

        session()->flash('message', 'DayUse e Pagamentos salvos com sucesso!');
        // Opcional: Redirecionar ou emitir um evento para o componente pai
        // $this->dispatch('dayUseFinalized');
        return redirect()->route('dayuse.index'); // Exemplo de redirecionamento
    }

    public function render()
    {
        return view('livewire.day-use-pagamento');
    }
}

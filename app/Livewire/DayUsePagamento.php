<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DayUse;
use App\Models\DayUsePag;
use App\Models\FormaPagamento;
use App\Services\CaixaService;
use App\Models\Caixa;
use Illuminate\Support\Facades\Auth;

class DayUsePagamento extends Component
{
    public $dayUseId;
    public $itemSubtotal;

    public $acrescimo = 0;
    public $desconto = 0;
    public $finalPagamentoTotal = 0;

    public $formaPagamento;
    public $metodoSelecionadoID;
    public $pagamentoValor;
    public $pagamentosAtuais = []; // Array para armazenar os pagamentos adicionados na tela
    public $restante = 0; // Valor restante a ser pago
    public $inputKey;

       protected CaixaService $caixaService;

    public function __construct()
    {
        $this->caixaService = app(CaixaService::class);
    }

    protected $rules = [
        'acrescimo' => 'numeric|min:0',
        'desconto' => 'numeric|min:0',
        'metodoSelecionadoID' => 'required|exists:forma_pagamentos,id',
        'pagamentoValor' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'metodoSelecionadoID.required' => 'Selecione um método de pagamento.',
        'pagamentoValor.required' => 'Informe o valor do pagamento.',
        'pagamentoValor.numeric' => 'O valor do pagamento deve ser um número.',
        'pagamentoValor.min' => 'O valor do pagamento deve ser maior que zero.',
    ];

    public function mount($dayUseId, $itemSubtotal)
    {
        $this->caixaService = app(CaixaService::class); // ou resolve(CaixaService::class)

        $this->dayUseId = $dayUseId;
        $this->itemSubtotal = $itemSubtotal;

        $this->formaPagamento = FormaPagamento::where('descricao', '!=', 'crediário')->get();

        if ($this->dayUseId) {
            $dayUse = DayUse::find($this->dayUseId);
            if ($dayUse) {
                $this->acrescimo = $dayUse->acrescimo ?? 0;
                $this->desconto = $dayUse->desconto ?? 0;

                foreach ($dayUse->formaPag as $dayUsePagEntry) {
                    $this->pagamentosAtuais[] = [
                        'id' => $dayUsePagEntry->pagamento_id,
                        'descricao' => $dayUsePagEntry->pagamento->descricao,
                        'valor' => $dayUsePagEntry->valor,
                    ];
                }
            }
        }

        $this->calculateFinalPaymentTotal();
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
        if (floatval($this->pagamentoValor) > floatval($this->restante)) {
            $this->addError('pagamentoValor', 'O valor não pode ser maior que o restante.');
        } else {
            $this->validate([
                'metodoSelecionadoID' => 'required|exists:forma_pagamentos,id',
                'pagamentoValor' => 'required|numeric|min:0.01',
            ]);

            $method = $this->formaPagamento->find($this->metodoSelecionadoID);

            $this->pagamentosAtuais[] = [
                'pagamento_id' => $this->metodoSelecionadoID,
                'descricao' => $method->descricao,
                'valor' => (float) $this->pagamentoValor,
            ];

            $this->metodoSelecionadoID = null;
            $this->pagamentoValor = $this->restante;
            $this->inputKey = now()->timestamp;
            $this->calculateRemainingToPay();
        }
    }

    public function removePayment($index)
    {
        unset($this->pagamentosAtuais[$index]);
        $this->pagamentosAtuais = array_values($this->pagamentosAtuais); // Reindexa o array
        $this->calculateRemainingToPay();
    }

    public function calculateRemainingToPay()
    {
        $paidAmount = array_sum(array_column($this->pagamentosAtuais, 'valor'));
        $this->restante = $this->finalPagamentoTotal - $paidAmount;
        if (floatval($this->restante) > 0) {
            $this->pagamentoValor = $this->restante;
        } else {
            $this->pagamentoValor = null;
        }
    }

    public function savePayments()
    {
        $this->validate([
            'acrescimo' => 'numeric|min:0',
            'desconto' => 'numeric|min:0',
            'finalPagamentoTotal' => 'numeric|min:0',
            'pagamentosAtuais' => 'array',
        ]);

        // Validação final: o valor restante deve ser zero (ou muito próximo de zero para evitar problemas de float)
        if (abs($this->restante) > 0.01) { // Tolerância de 1 centavo
            $this->addError('restante', 'O valor restante a pagar deve ser zero.');
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
        foreach ($this->pagamentosAtuais as $payment) {
            DayUsePag::create([
                'dayuse_id' => $this->dayUseId,
                'forma_pagamento_id' => $payment['pagamento_id'],
                'valor' => $payment['valor'],
            ]);
        }


        $empresaId = Auth::user()->empresa_id;

        $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
            ->where('status', 'aberto')
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', Auth::id())
            ->first();;

        if (!$caixa) {
            session()->flash('error', 'Nenhum caixa aberto encontrado para registrar movimentações.');
            return;
        }

        // Criar movimentações no Fluxo de Caixa
        foreach ($this->pagamentosAtuais as $payment) {
            $formaPagamento = FormaPagamento::find($payment['pagamento_id']);

            // Verifica tipo com base no nome da forma de pagamento
            $tipoMov = 'venda-' . strtolower(str_replace(' ', '-', $formaPagamento->descricao));
            $movimentoId = \App\Models\Movimento::where('descricao', $tipoMov)->value('id');
            if (!$movimentoId) {
                continue; // Pula se não tiver mapeado corretamente
            }

            $this->caixaService->inserirMovimentacao($caixa, [
                'descricao' => 'DayUse #' . $dayUse->id,
                'valor' => $payment['valor'],
                'tipo' => 'entrada',
                'movimento_id' => $movimentoId,
                'valor_total' => $payment['valor'],
                'plano_de_conta_id' => 1,
            ]);
        }


        return redirect()->route('dayuse.create')->with('success', 'Cadastro Day Use realizado com sucesso!');
    }

    public function render()
    {
        return view('livewire.day-use-pagamento');
    }
}

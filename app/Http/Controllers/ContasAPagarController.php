<?php

namespace App\Http\Controllers;

use App\Models\ContasAPagar;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\ParcelaContasAPagar;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContasAPagarController extends Controller
{
public function index(Request $request)
{
    $planoDeContas = PlanoDeConta::all();
    $empresas = Empresa::all();
    $fornecedores = Fornecedor::all();

    $query = ContasAPagar::where('empresa_id', Auth::user()->empresa_id);

    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    if ($request->filled('fornecedor_id')) {
        $query->where('fornecedor_id', $request->input('fornecedor_id'));
    }

    // Carrega com parcelas
    $contasAPagar = $query->with('parcelas')->get();
    $contasComParcelas = [];

    // Intervalo de datas
    $inicio = $request->input('data_inicio') ?? Carbon::now()->startOfYear()->toDateString();
    $fim = $request->input('data_fim') ?? Carbon::now()->endOfYear()->toDateString();

    foreach ($contasAPagar as $conta) {
        if ($conta->parcelas->isEmpty()) {
            // Conta sem parcelas
            if ($conta->data_vencimento >= $inicio && $conta->data_vencimento <= $fim) {
                $conta->pode_excluir = $conta->status !== 'pago';
                $contasComParcelas[] = $conta;
            }
        } else {
            // Conta com parcelas
            $temParcelaPaga = $conta->parcelas->contains(fn ($p) => $p->status === 'pago');
            $totalParcelas = $conta->parcelas->count();

            foreach ($conta->parcelas as $parcela) {
                if ($parcela->data_vencimento >= $inicio && $parcela->data_vencimento <= $fim) {
                    $contaClone = clone $conta;
                    $contaClone->descricao .= " - Parcela {$parcela->numero_parcela}/{$totalParcelas}";
                    $contaClone->valor = $parcela->valor;
                    $contaClone->data_vencimento = $parcela->data_vencimento;
                    $contaClone->status = $parcela->status;
                    $contaClone->data_pagamento = $parcela->data_pagamento;
                    $contaClone->numero_parcela = $parcela->numero_parcela;
                    $contaClone->total_parcelas = $totalParcelas;
                    $contaClone->parcela_id = $parcela->id;
                    $contaClone->pode_excluir = !$temParcelaPaga;
                    $contasComParcelas[] = $contaClone;
                }
            }
        }
    }

    return view('contasAPagar.index', compact('contasComParcelas', 'planoDeContas', 'empresas', 'fornecedores'));
}


    public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date|after_or_equal:today',
            'status' => 'required|in:pendente,finalizado',
            'plano_de_contas_id' => [
                'nullable',
                'exists:plano_de_contas,id',
                function ($attribute, $value, $fail) {
                    if ($value && !PlanoDeConta::where('id', $value)
                        ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                        $fail('O plano de contas selecionado não pertence à sua empresa.');
                    }
                }
            ],
            'fornecedor_id' => [
                'nullable',
                'exists:fornecedors,id',
            ],
            'parcelas' => 'nullable|integer|min:1'
        ]);

        $validatedData['empresa_id'] = Auth::user()->empresa_id;

        $numParcelas = $request->input('parcelas', 1);

        // Define o valor total e número de parcelas
        $validatedData['total_parcelas'] = $numParcelas > 1 ? $numParcelas : null;
        $validatedData['numero_parcela'] = null;

        // Cria a conta principal
        $conta = ContasAPagar::create($validatedData);

        // Se for parcelada, cria as parcelas
        if ($numParcelas > 1) {
            $valorParcela = round($conta->valor / $numParcelas, 2);
            $dataBase = Carbon::parse($validatedData['data_vencimento']);

            for ($i = 1; $i <= $numParcelas; $i++) {
                ParcelaContasAPagar::create([
                    'contas_a_pagar_id' => $conta->id,
                    'numero_parcela' => $i,
                    'valor' => $valorParcela,
                    'data_vencimento' => $dataBase->copy()->addMonths($i - 1),
                    'status' => 'pendente'
                ]);
            }
        }

        return redirect()
            ->route('contasAPagar.index')
            ->with('success', 'Conta a pagar cadastrada com sucesso!');
    } catch (\Exception $e) {
        return redirect()
            ->route('contasAPagar.index')
            ->with('error', 'Erro ao cadastrar conta: ' . $e->getMessage());
    }
}


    public function update(Request $request, ContasAPagar $contasAPagar)
    {
        try {
            $validatedData = $request->validate([
                'descricao' => 'required|string|max:255',
                'valor' => [
                    'required', 'numeric', 'min:0.01',
                    function ($attribute, $value, $fail) use ($contasAPagar) {
                        if ($contasAPagar->valor_pago > 0 && $value < $contasAPagar->valor) {
                            $fail('Não é possível reduzir o valor quando já existe um pagamento registrado.');
                        }
                    }
                ],
                'data_vencimento' => 'required|date',
                'status' => [
                    'required', 'in:pendente,finalizado',
                ],
                'plano_de_contas_id' => [
                    'nullable', 'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !PlanoDeConta::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    }
                ],
                'fornecedor_id' => [
                    'nullable', 'exists:fornecedors,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !Fornecedor::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                            $fail('O fornecedor selecionado não pertence à sua empresa.');
                        }
                    }
                ],
            ]);

            $contasAPagar->update($validatedData);

            return redirect()
                ->route('contasAPagar.index')
                ->with('success', 'Conta a pagar atualizada com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', 'Erro ao atualizar conta: ' . $e->getMessage());
        }
    }

    public function destroy(ContasAPagar $contasAPagar)
    {
        $contasAPagar->delete();
        return redirect()->route('contasAPagar.index')->with('success', 'Conta a pagar excluída com sucesso!');
    }


public function pagar(Request $request, ContasAPagar $contasAPagar)
{
    try{
    //dd($request);
    $request->validate([
        'data_pagamento' => 'required|date',
        'valor_pago' => 'required|numeric|min:0.01',
        'parcela_id' => 'nullable|exists:parcelas_contas_a_pagar,id',
        'fonte_pagadora' => 'required|in:caixa,conta_corrente',
    ], [
        'data_pagamento.required' => 'A data do pagamento é obrigatória.',
        'data_pagamento.date' => 'A data do pagamento deve ser uma data válida.',

        'valor_pago.required' => 'O valor pago é obrigatório.',
        'valor_pago.numeric' => 'O valor pago deve ser um número.',
        'valor_pago.min' => 'O valor pago deve ser no mínimo R$ 0,01.',

        'parcela_id.exists' => 'A parcela informada não existe.',

        'fonte_pagadora.required' => 'A fonte pagadora é obrigatória.',
        'fonte_pagadora.in' => 'A fonte pagadora deve ser "caixa" ou "conta corrente".',
    ]);


    
        if ($request->filled('parcela_id')) {
            $parcela = \App\Models\ParcelaContasAPagar::findOrFail($request->parcela_id);
            //dd($request->valor_pago);

            $parcela->update([
                'status' => 'pago',
                'valor_pago' => '200.00',
                'data_pagamento' => $request->data_pagamento,
            ]);
            
            $conta = $parcela->conta;

            $todasPagas = $conta->parcelas->every(fn($p) => $p->status === 'pago');

            if ($todasPagas) {
                $conta->update([
                    'status' => 'pago',
                    'valor_pago' => $conta->parcelas->sum('valor_pago'),
                    'data_pagamento' => now()->toDateString(),
                ]);
            }

        } else {
            $contasAPagar->update([
                'status' => 'pago',
                'valor_pago' => $request->valor_pago,
                'data_pagamento' => $request->data_pagamento,
            ]);
        }

        // Aqui você pode lançar o fluxo de caixa com base em $request->fonte_pagadora
        // Exemplo: CaixaService::registrarSaida(...)

        return redirect()
            ->route('contasAPagar.index')
            ->with('success', 'Pagamento registrado com sucesso!');
    } catch (\Throwable $e) {
        \Log::error('Erro ao registrar pagamento da conta ID ' . $contasAPagar->id . ': ' . $e->getMessage());

        return redirect()
            ->route('contasAPagar.index')
            ->with('error', 'Erro ao registrar o pagamento. Verifique os dados ou tente novamente.' . $e);
    }
}

}

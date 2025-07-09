<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\ContaCorrente;
use App\Models\ContasAPagar;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\ParcelaContasAPagar;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CaixaService;

class ContasAPagarController extends Controller
{
public function index(Request $request)
{
    $usuario = Auth::user();
    $empresaSelecionada = session('empresa_id');

    // Preparar a query base
    if ($empresaSelecionada == null) {
        $planoDeContas = PlanoDeConta::all();
        $fornecedores = Fornecedor::all();
        $contas_corrente = ContaCorrente::all();
        $caixas = Caixa::all();
        $query = ContasAPagar::query();
    } else {
        $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;

        $planoDeContas = PlanoDeConta::where('empresa_id', $empresa_id)->get();
        $fornecedores = Fornecedor::all();
        $contas_corrente = ContaCorrente::all();
        $caixas = Caixa::where('empresa_id', $empresa_id)->get();
        $query = ContasAPagar::where('empresa_id', $empresa_id);
    }

    if ($request->filled('fornecedor_id')) {
        $query->where('fornecedor_id', $request->input('fornecedor_id'));
    }

    // Adiciona o eager loading de parcelas antes de executar a query
    $query->with('parcelas');

    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    $contasAPagar = $query->get();
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
                    $contaClone->id = $parcela->id;
                    $contaClone->descricao .= " - Parcela {$parcela->numero_parcela}/{$totalParcelas}";
                    $contaClone->valor = $parcela->valor;
                    $contaClone->data_vencimento = $parcela->data_vencimento;
                    $contaClone->status = $parcela->status;
                    $contaClone->data_pagamento = $parcela->data_pagamento;
                    $contaClone->numero_parcela = $parcela->numero_parcela;
                    $contaClone->total_parcelas = $totalParcelas;
                    $contaClone->conta_id = $conta->id;
                    $contaClone->conta_descricao = $conta->descricao;
                    $contaClone->pode_excluir = !$temParcelaPaga;
                    $contaClone->valor_pago = $parcela->valor_pago;
                    $contasComParcelas[] = $contaClone;
                }
            }
        }
    }

    return view('contasAPagar.index', compact('contasComParcelas', 'planoDeContas', 'fornecedores', 'contas_corrente','caixas'));
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


public function pagar(Request $request)
{
    try {
        $request->validate([
            'data_pagamento' => 'required|date',
            'valor_pago' => 'required|numeric|min:0.01',
            'id' => 'nullable|exists:parcelas_contas_a_pagar,id',
            'fonte_pagadora' => 'required|in:caixa,conta_corrente',
        ]);

        $valorPago = $request->valor_pago;
        $contasAPagar = null;

        // Se for pagamento de parcela
        if ($request->filled('id')) {
            $parcela = \App\Models\ParcelaContasAPagar::findOrFail($request->id);
            $valor_pago_total = $parcela->valor_pago + $valorPago;

            $conta = $parcela->conta;

            // Se a fonte for CAIXA, verificar e registrar antes
            if ($request->fonte_pagadora == 'caixa') {
                $usuario = Auth::user();
                $empresaSelecionada = session('empresa_id');
                $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;

                $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
                    ->where('status', 'aberto')
                    ->where('empresa_id', $empresa_id)
                    ->where('usuario_id', Auth::id())
                    ->first();

                if (!$caixa) {
                    return redirect()
                        ->route('contasAPagar.index')
                        ->with('error', 'Nenhum caixa aberto encontrado para registrar movimentações.');
                }

                app(CaixaService::class)->inserirMovimentacao($caixa, [
                    'descricao' => 'Pagamento #' . $conta->descricao,
                    'valor' => $valorPago,
                    'valor_total' => $valorPago,
                    'tipo' => 'saida',
                    'movimento_id' => 31,
                    'plano_de_conta_id' => $conta->plano_de_contas_id,
                ]);
            }

            // Agora atualiza a parcela
            $dadosUpdate = [
                'valor_pago' => $valor_pago_total,
                'data_pagamento' => $request->data_pagamento,
            ];

            if ($valor_pago_total == $parcela->valor) {
                $dadosUpdate['status'] = 'pago';
            }

            $parcela->update($dadosUpdate);

            // Atualiza a conta principal
            $conta->update([
                'valor_pago' => $conta->parcelas->sum('valor_pago'),
                'status' => $conta->parcelas->every(fn($p) => $p->status === 'pago') ? 'pago' : $conta->status,
                'data_pagamento' => $conta->parcelas->every(fn($p) => $p->status === 'pago') ? now()->toDateString() : $conta->data_pagamento,
            ]);

            $contasAPagar = $conta;
        } else {
            // Pagamento total direto (sem parcelamento)
            $contasAPagar = \App\Models\ContasAPagar::findOrFail($request->conta_id);

            if ($request->fonte_pagadora == 'caixa') {
                $usuario = Auth::user();
                $empresaSelecionada = session('empresa_id');
                $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;

                $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
                    ->where('status', 'aberto')
                    ->where('empresa_id', $empresa_id)
                    ->where('usuario_id', Auth::id())
                    ->first();

                if (!$caixa) {
                    return redirect()
                        ->route('contasAPagar.index')
                        ->with('error', 'Nenhum caixa aberto encontrado para registrar movimentações.');
                }

                app(CaixaService::class)->inserirMovimentacao($caixa, [
                    'descricao' => 'Pagamento #' . $contasAPagar->descricao,
                    'valor' => $valorPago,
                    'valor_total' => $valorPago,
                    'tipo' => 'saida',
                    'movimento_id' => 31,
                    'plano_de_conta_id' => $contasAPagar->plano_de_contas_id,
                ]);
            }

            // Atualiza conta
            $contasAPagar->update([
                'status' => 'pago',
                'valor_pago' => $valorPago,
                'data_pagamento' => $request->data_pagamento,
            ]);
        }

        return redirect()
            ->route('contasAPagar.index')
            ->with('success', 'Pagamento registrado com sucesso!');
    } catch (\Throwable $e) {
        \Log::error('Erro ao registrar pagamento: ' . $e->getMessage());

        return redirect()
            ->route('contasAPagar.index')
            ->with('error', 'Erro ao registrar o pagamento. Verifique os dados ou tente novamente.');
    }
}



}

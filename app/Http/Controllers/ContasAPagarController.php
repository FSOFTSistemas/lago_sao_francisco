<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\ContaCorrente;
use App\Models\ContasAPagar;
use App\Models\Fornecedor;
use App\Models\ParcelaContasAPagar;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CaixaService;
use App\Services\ContaCorrenteService;

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
            $valorTotal = $conta->parcelas->sum('valor');

            foreach ($conta->parcelas as $parcela) {
                if ($parcela->data_vencimento >= $inicio && $parcela->data_vencimento <= $fim) {
                    $contaClone = clone $conta;
                    $contaClone->id = $parcela->id;
                    $contaClone->descricao .= " - Parcela {$parcela->numero_parcela}/{$totalParcelas}";
                    $contaClone->valor = $parcela->valor;
                    $contaClone->valor_total = $valorTotal;
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
        $numParcelas = (int) $numParcelas;
        // Define o valor total e número de parcelas
        $validatedData['total_parcelas'] = $numParcelas > 1 ? $numParcelas : null;
        $validatedData['numero_parcela'] = null;
        $validatedData['plano_de_contas_id'] = (int) $validatedData['plano_de_contas_id'];

        // Cria a conta principal
        $conta = ContasAPagar::create($validatedData);

        // Se for parcelada, cria as parcelas
        if ($numParcelas > 1) {
            $valorParcela = round($conta->valor / $numParcelas, 2);
            $dataBase = Carbon::parse($validatedData['data_vencimento']);
            $valor_total=$conta->valor;

            for ($i = 1; $i <= $numParcelas; $i++) {
                
                if($i == $numParcelas){
                    dump($numParcelas);
                    $valorParcela = $valor_total;
                }
                ParcelaContasAPagar::create([
                    'contas_a_pagar_id' => $conta->id,
                    'numero_parcela' => $i,
                    'valor' => $valorParcela,
                    'data_vencimento' => $dataBase->copy()->addMonths($i - 1),
                    'status' => 'pendente',
                ]);
                $valor_total -= $valorParcela;
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
            
            // 1. Verifica e registra a movimentação no caixa ANTES
            if ($request->fonte_pagadora === 'caixa') {
            // --- LÓGICA PARA PAGAMENTO COM O CAIXA (EXISTENTE E FUNCIONAL) ---
            $usuario = Auth::user();
            $empresaSelecionada = session('empresa_id');
            $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;
            
            // Procura por um caixa aberto para o usuário na data atual
            $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
                ->where('status', 'aberto')
                ->where('empresa_id', $empresa_id)
                ->where('usuario_id', $usuario->id)
                ->first();

            if (!$caixa) {
                return redirect()
                    ->route('contasAPagar.index')
                    ->with('error', 'Nenhum caixa aberto encontrado para registrar a movimentação.');
            }
            
            try {
                // Tenta inserir a movimentação de saida no caixa
                app(CaixaService::class)->inserirMovimentacao($caixa, [
                    'descricao' => 'Pagamento #' . $conta->descricao . " " . $parcela->numero_parcela . '/' . $conta->total_parcelas,
                    'valor' => $valorPago,
                    'valor_total' => $valorPago,
                    'tipo' => 'saida',
                    'movimento_id' => 31, // ID para "Pagamento de Contas"
                    'plano_de_conta_id' => $conta->plano_de_contas_id,
                ]);
            } catch (\InvalidArgumentException $e) {
                // Captura erro específico de saldo insuficiente do CaixaService
                return redirect()
                    ->route('contasAPagar.index')
                    ->with('error', $e->getMessage()); 
            } catch (\Throwable $e) {
                \Log::error('Erro ao inserir movimentação no caixa: ' . $e->getMessage());

                return redirect()
                    ->route('contasAPagar.index')
                    ->with('error', 'Erro inesperado ao registrar movimentação no caixa. Tente novamente mais tarde.');
            }

        } else if ($request->fonte_pagadora === 'conta_corrente') {
            // --- LÓGICA AJUSTADA PARA PAGAMENTO COM CONTA CORRENTE ---
            // 1. Validação: Verifica se uma conta corrente foi selecionada no request.
            if (empty($request->conta_corrente_id)) {
                return redirect()
                    ->route('contasAPagar.index')
                    ->with('error', 'Por favor, selecione a conta corrente para realizar o pagamento.');
            }

            $usuario = Auth::user();
            $empresaSelecionada = session('empresa_id');
            $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;
            
            try {
                // 2. Chama o serviço de Conta Corrente para registrar o lançamento
                app(ContaCorrenteService::class)->registrarLancamento(
                    // Dados do Lançamento
                    [
                        'banco_id'    => $request->conta_corrente_id, // ID da conta bancária
                        'valor'       => $valorPago,
                        'tipo'        => 'saida', // O tipo é 'saida' porque é um pagamento
                        'descricao'   => 'Pagamento #' . $conta->descricao . " " . $parcela->numero_parcela . '/' . $conta->total_parcelas,
                        // 'data'     => now(), // O service já assume a data atual se não for passada
                    ],
                    // Empresa ID
                    $empresa_id
                );

            } catch (\InvalidArgumentException $e) {
                // 3. Captura exceções do service, como "Saldo insuficiente"
                return redirect()
                    ->route('contasAPagar.index')
                    ->with('error', $e->getMessage()); 
            } catch (\Throwable $e) {
                // 4. Captura qualquer outro erro inesperado
                \Log::error('Erro ao registrar lançamento na conta corrente: ' . $e->getMessage());

                return redirect()
                    ->route('contasAPagar.index')
                    ->with('error', 'Erro inesperado ao registrar o pagamento na conta corrente. Tente novamente mais tarde.');
            }
        }


            // 2. Agora sim, atualiza a parcela
            $dadosUpdate = [
                'valor_pago' => $valor_pago_total,
                'data_pagamento' => $request->data_pagamento,
            ];

            if ($valor_pago_total == $parcela->valor) {
                $dadosUpdate['status'] = 'pago';
            }

            $parcela->update($dadosUpdate);

            // 3. Atualiza a conta principal
            $conta->update([
                'valor_pago' => $conta->parcelas->sum('valor_pago'),
                'status' => $conta->parcelas->every(fn($p) => $p->status === 'pago') ? 'pago' : $conta->status,
                'data_pagamento' => $conta->parcelas->every(fn($p) => $p->status === 'pago') ? now()->toDateString() : $conta->data_pagamento,
            ]);
        }else {
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
            }else  if ($request->fonte_pagadora == 'conta-corrente') {
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

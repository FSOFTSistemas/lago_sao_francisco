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
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

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

        // Adiciona o eager loading de parcelas para otimização
        $query->with('parcelas');


        // 1. Define o intervalo de datas a ser usado
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            // Se o usuário especificou um intervalo, use-o.
            // Usamos startOfDay e endOfDay para garantir que o dia inteiro seja incluído.
            $inicio = \Carbon\Carbon::parse($request->input('data_inicio'))->startOfDay();
            $fim = \Carbon\Carbon::parse($request->input('data_fim'))->endOfDay();
        } else {
            // Se não, use o mês atual como padrão.
            $inicio = \Carbon\Carbon::now()->startOfMonth();
            $fim = \Carbon\Carbon::now()->endOfMonth();
        }

        // 2. Aplica o filtro de data (seja do request ou do mês atual) na consulta
        $query->where(function ($q) use ($inicio, $fim) {
            // Condição 1: Contas sem parcelas, com vencimento no intervalo definido
            $q->whereBetween('data_vencimento', [$inicio, $fim]);
            
            // Condição 2: OU contas que tenham parcelas com vencimento no intervalo definido
            $q->orWhereHas('parcelas', function ($parcelaQuery) use ($inicio, $fim) {
                $parcelaQuery->whereBetween('data_vencimento', [$inicio, $fim]);
            });
        });

        // Aplica outros filtros do formulário
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->input('fornecedor_id'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where(function ($q) use ($status) {
                $q->where('status', $status)
                  ->orWhereHas('parcelas', function ($parcelaQuery) use ($status) {
                      $parcelaQuery->where('status', $status);
                  });
            });
        }

        // Executa a consulta ao banco de dados SEM ORDENAÇÃO
        $contasAPagar = $query->get();
        
        $contasComParcelas = [];

        // Processa os resultados para criar uma lista plana de itens a serem exibidos
        foreach ($contasAPagar as $conta) {
            if ($conta->parcelas->isEmpty()) {
                $conta->conta_id = $conta->id;
                $conta->id = count($contasComParcelas)+1;
                $conta->pode_excluir = $conta->status !== 'pago';
                $conta->conta_descricao = $conta->descricao;
                $conta->parcela_id = null;
                $contasComParcelas[] = $conta;
                $conta->valor_total = $conta->valor;
            } else {
                $temParcelaPaga = $conta->parcelas->contains(fn ($p) => $p->status === 'pago');
                $totalParcelas = $conta->parcelas->count();
                $valorTotal = $conta->parcelas->sum('valor');

                foreach ($conta->parcelas as $parcela) {
                    // Adicionamos à lista apenas as parcelas que vencem no mês atual
                    if (!($parcela->data_vencimento >= $inicio && $parcela->data_vencimento <= $fim)) {
                        continue;
                    }
                    if ($request->filled('status') && $parcela->status !== $request->input('status')) {
                        continue;
                    }
                    
                    $contaClone = clone $conta;
                    $contaClone->id = count($contasComParcelas)+1;
                    $contaClone->conta_id = $conta->id;
                    $contaClone->descricao .= " - Parcela {$parcela->numero_parcela}/{$totalParcelas}";
                    $contaClone->valor = $parcela->valor;
                    $contaClone->valor_total = $valorTotal;
                    $contaClone->data_vencimento = $parcela->data_vencimento;
                    $contaClone->status = $parcela->status;
                    $contaClone->data_pagamento = $parcela->data_pagamento;
                    $contaClone->numero_parcela = $parcela->numero_parcela;
                    $contaClone->total_parcelas = $totalParcelas;
                    $contaClone->parcela_id = $parcela->id;
                    $contaClone->conta_descricao = $conta->descricao;
                    $contaClone->pode_excluir = !$temParcelaPaga;
                    $contaClone->valor_pago = $parcela->valor_pago;
                    $contaClone->forma_pagamento = $parcela->forma_pagamento;
                    
                    $contasComParcelas[] = $contaClone;
                }
            }
        }

        
        $contasComParcelas = collect($contasComParcelas)->sortBy(function ($item) {
            return Carbon::parse($item->data_vencimento);
        })->values()->all();

        // Retorna a view com os dados processados e prontos para exibição
        return view('contasAPagar.index', compact('contasComParcelas', 'planoDeContas', 'fornecedores', 'contas_corrente', 'caixas'));
    }



    public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'valor_pago' => 'numeric|min:0.00',
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
        if(((int) $validatedData['valor_pago']) == ((int) $validatedData['valor'])){
                $validatedData['status'] = 'pago';
        }
        $numParcelas = $request->input('parcelas', 1);
        $numParcelas = (int) $numParcelas;
        // Define o valor total e número de parcelas
        $validatedData['total_parcelas'] = $numParcelas > 1 ? $numParcelas : null;
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


    /**
     * Registra o pagamento de uma conta ou de uma parcela.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pagar(Request $request, $conta_id, $parcela_id = null)
    {
        
        // 1. Validação aprimorada
        $request->validate([
            'data_pagamento'    => 'required|date',
            'valor_pago'        => 'required|numeric|min:0.01',
            'fonte_pagadora'    => 'required|in:caixa,conta_corrente',
            // Exige o ID da conta corrente se a fonte for 'conta_corrente'
            'conta_corrente_id' => 'required_if:fonte_pagadora,conta_corrente|exists:contas_correntes,id',
        ]);
        $request->empresa_id = Auth::user()->empresa_id;

        try {
            // 2. Usar Transação para garantir a integridade dos dados
            // Se algo falhar (ex: saldo insuficiente), todas as operações são revertidas.
            $response = DB::transaction(function () use ($request, $conta_id, $parcela_id) {
                
                $valorPago = $request->valor_pago;
                $conta = null;
                $parcela = null;
                $description = '';
                // 3. Define qual entidade está sendo paga (Parcela ou Conta)
                if ($parcela_id) {
                    
                    // Pagamento de Parcela
                    $parcela = ParcelaContasAPagar::findOrFail($parcela_id);
                    $conta = $parcela->conta;
                    $description = 'Pagamento #' . $conta->descricao . " " . $parcela->numero_parcela . '/' . $conta->total_parcelas;
                } else {
                    // Pagamento de Conta sem parcelamento
                    $conta = ContasAPagar::findOrFail($conta_id);
                    $description = 'Pagamento #' . $conta->descricao;
                }

                // 4. Executa a lógica de pagamento baseada na fonte (sem duplicação)
                if ($request->fonte_pagadora === 'caixa') {
                    $this->handleCaixaPayment($request, $conta, $description, $valorPago);
                } elseif ($request->fonte_pagadora === 'conta_corrente') {
                    $this->handleContaCorrentePayment($request, $conta, $description, $valorPago);
                }

                // 5. Atualiza os modelos após o pagamento ser validado e registrado
                if ($parcela) {
                    // Atualiza a parcela
                    $valor_pago_total = $parcela->valor_pago + $valorPago;
                    $dadosUpdate = [
                        'valor_pago' => $valor_pago_total,
                        'data_pagamento' => $request->data_pagamento,
                        'forma_pagamento' => $parcela->forma_pagamento
                    ];
                    $fonte = $request->fonte_pagadora;
                    $forma = $dadosUpdate['forma_pagamento'];

                    if (!str_contains($forma, $fonte)) {
                        $dadosUpdate['forma_pagamento'] = trim($forma . "\n" . $fonte);
                    }


                    
                    if ($valor_pago_total >= $parcela->valor) {
                        $dadosUpdate['status'] = 'pago';
                    }
                    $parcela->update($dadosUpdate);

                    // Atualiza a conta principal com base em suas parcelas
                    $conta->update([
                        'valor_pago' => $conta->parcelas()->sum('valor_pago'),
                        'status' => $conta->parcelas()->where('status', '!=', 'pago')->doesntExist() ? 'pago' : $conta->status,
                        'data_pagamento' => $conta->parcelas()->where('status', '!=', 'pago')->doesntExist() ? now()->toDateString() : null,
                    ]);
                } else {
                    // Atualiza a conta paga diretamente
                    $conta->update([
                        'status' => 'pago',
                        'valor_pago' => $valorPago,
                        'data_pagamento' => $request->data_pagamento,
                        'forma_pagamento' => $request->fonte_pagadora,
                    ]);
                }
                
                return redirect()
                    ->route('contasAPagar.index')
                    ->with('success', 'Pagamento registrado com sucesso!');
            });

            return $response;

        } catch (InvalidArgumentException $e) {
            // Captura erros de negócio (ex: Saldo insuficiente)
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            // Captura qualquer outro erro inesperado
            
            Log::error('Erro ao registrar pagamento: ' . $e->getMessage() . ' no arquivo ' . $e->getFile() . ' na linha ' . $e->getLine());
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', 'Erro inesperado ao registrar o pagamento. Tente novamente mais tarde.');
        }
    }

    /**
     * Lida com a lógica de pagamento via Caixa.
     */
    private function handleCaixaPayment(Request $request, ContasAPagar $conta, string $description, float $valorPago): void
    {
        $usuario = Auth::user();
        $empresaSelecionada = session('empresa_id');
        $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;

        $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
            ->where('status', 'aberto')
            ->where('empresa_id', $empresa_id)
            ->where('usuario_id', $usuario->id)
            ->first();

        if (!$caixa) {
            // Lança uma exceção que será capturada pelo bloco principal
            throw new InvalidArgumentException('Nenhum caixa aberto encontrado para registrar a movimentação.');
        }

        app(CaixaService::class)->inserirMovimentacao($caixa, [
            'descricao' => $description,
            'valor' => $valorPago,
            'valor_total' => $valorPago,
            'tipo' => 'saida',
            'movimento_id' => 31, // ID para "Pagamento de Contas"
            'plano_de_conta_id' => $conta->plano_de_contas_id,
        ]);
    }

    /**
     * Lida com a lógica de pagamento via Conta Corrente. 
     */
    private function handleContaCorrentePayment(Request $request, ContasAPagar $conta, string $description, float $valorPago): void
    {
        $usuario = Auth::user();
        $empresaSelecionada = session('empresa_id');
        $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : $usuario->empresa_id;
        
        // O ID da conta corrente já foi validado no início do método `pagar`
        $contaCorrenteId = $request->conta_corrente_id;

        app(ContaCorrenteService::class)->registrarLancamento(
            [
                'conta_corrente_id'  => $contaCorrenteId,
                'valor'     => $valorPago,
                'tipo'      => 'saida',
                'descricao' => $description,
                'data'      => $request->data_pagamento // Usa a data de pagamento informada
            ],
            $empresa_id
        );
    }
}




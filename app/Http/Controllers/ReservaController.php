<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Hospede;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use App\Models\PreferenciasHotel;
use App\Models\Produto;
use App\Models\Transacao;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $situacao = $request->input('situacao', 'todos'); // 'todos' por padrão

        $query = Reserva::with(['quarto', 'hospede'])->latest();

        if ($situacao !== 'todos') {
            $query->where('situacao', $situacao);
        }

        $reservas = $query->paginate(10);

        return view('reserva.index', compact('reservas', 'situacao'));
    }



    public function create(Request $request)
    {
        $checkin = $request->data_checkin;
        $checkout = $request->data_checkout;

        $quartos = Quarto::query();

        if ($checkin && $checkout) {
            $ocupados = Reserva::where(function ($query) use ($checkin, $checkout) {
                $query->whereBetween('data_checkin', [$checkin, $checkout])
                    ->orWhereBetween('data_checkout', [$checkin, $checkout])
                    ->orWhere(function ($query) use ($checkin, $checkout) {
                        $query->where('data_checkin', '<=', $checkin)
                            ->where('data_checkout', '>=', $checkout);
                    });
            })->pluck('quarto_id');

            $quartos = $quartos->whereNotIn('id', $ocupados);
        }

        // Buscar quartos agrupados por categoria
        $quartosAgrupados = $quartos->with('categoria')->get()->groupBy('categoria.titulo');
        $categorias = Categoria::where('status', 1)->orderBy('posicao')->get();
        $formasPagamento = FormaPagamento::whereNotIn('descricao', [
            'sympla',
            'boleto-bancário',
            'crediário'
        ])->get();

        // Buscar produtos ativos para o select
        $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();

        $hospedes = Hospede::all();
        $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();

        return view('reserva.create', compact('quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'checkin', 'checkout'));
    }

    public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'quarto_id' => 'required|exists:quartos,id',
            'hospede_id' => 'nullable|exists:hospedes,id',
            'data_checkin' => 'required|date',
            'data_checkout' => 'required|date|after_or_equal:data_checkin',
            'valor_diaria' => 'nullable',
            'valor_total' => 'numeric',
            'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
            'n_adultos' => 'required',
            'n_criancas' => 'required',
        ]);
        
        $preferencia = PreferenciasHotel::first();
        // Lógica para valor_diaria com base em tarifas diárias
        if ($preferencia->valor_diaria === 'tarifario' || empty($validatedData['valor_diaria'])) {
            $quarto = Quarto::with('categoria.tarifa')->find($validatedData['quarto_id']);
            $tarifa = $quarto->categoria->tarifa;

            $checkin = Carbon::parse($validatedData['data_checkin']);
            $checkout = Carbon::parse($validatedData['data_checkout']);

            $periodo = CarbonPeriod::create($checkin, $checkout->subDay());

            $total = 0;
            $quantidadeDias = 0;

            foreach ($periodo as $dia) {
                $campo = match ($dia->dayOfWeek) {
                    0 => 'dom',
                    1 => 'seg',
                    2 => 'ter',
                    3 => 'qua',
                    4 => 'qui',
                    5 => 'sex',
                    6 => 'sab',
                };

                $valorDia = (float) $tarifa->$campo ?? 0;
                $total += $valorDia;
                $quantidadeDias++;
            }

            // Média proporcional da tarifa
            $mediaTarifa = $quantidadeDias > 0 ? $total / $quantidadeDias : 0;
            $validatedData['valor_diaria'] = number_format($mediaTarifa, 2, '.', '');
        } else {
            // Remove máscara se foi preenchido manualmente
            $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);
        }

        $reserva = Reserva::create($validatedData);

        return redirect()->route('reserva.edit', $reserva->id)
                         ->with('success', 'Reserva criada com sucesso!');
    } catch (\Exception $e) {
        return redirect()->back()->withInput()
                         ->with('error', 'Erro ao criar reserva!: ' . $e->getMessage());
    }
}

    public function edit(Reserva $reserva)
    {
        // Buscar quartos agrupados por categoria para edição
        $quartosAgrupados = Quarto::with('categoria')->get()->groupBy('categoria.titulo');
        $categorias = Categoria::where('status', 1)->orderBy('posicao')->get();
        $formasPagamento = FormaPagamento::whereNotIn('descricao', [
            'sympla',
            'boleto-bancário',
            'crediário'
        ])->get();

        // Buscar produtos ativos para o select
        $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();

        $hospedes = Hospede::all();
        $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();

        // Verificar se hoje é o dia do check-in
        $hoje = Carbon::today();
        $dataCheckin = Carbon::parse($reserva->data_checkin);
        $podeHospedar = $hoje->equalTo($dataCheckin) &&
            in_array($reserva->situacao, ['reserva']) &&
            !in_array($reserva->situacao, ['finalizada', 'cancelado']);

        return view('reserva.create', compact('reserva', 'quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'podeHospedar'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        try {
            // Validação básica
            $validatedData = $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'required',
                'valor_total' => 'numeric',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado,finalizada,cancelado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
            ]);

            // Validações específicas de situação
            $situacaoAtual = $reserva->situacao;
            $novaSituacao = $validatedData['situacao'];

            // Regras de validação para mudança de situação
            if ($situacaoAtual === 'hospedado' && $novaSituacao !== 'hospedado') {
                return redirect()->back()->withInput()->with('error', 'Não é possível alterar a situação de uma reserva hospedada manualmente.');
            }

            if (in_array($situacaoAtual, ['finalizada', 'cancelado']) && $novaSituacao !== $situacaoAtual) {
                return redirect()->back()->withInput()->with('error', 'Não é possível alterar a situação de uma reserva finalizada ou cancelada.');
            }

            // Verificar se há pagamentos para mudança de pré-reserva para reserva
            if ($situacaoAtual === 'pre-reserva' && $novaSituacao === 'reserva') {
                $temPagamentos = Transacao::where('reserva_id', $reserva->id)
                    ->where('tipo', 'pagamento')
                    ->where('status', true)
                    ->exists();

                if (!$temPagamentos) {
                    return redirect()->back()->withInput()->with('error', 'Para alterar para "reserva", é necessário ter pelo menos um pagamento registrado.');
                }
            }

            // Remover a máscara do valor_diaria antes de salvar
            $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);

            $reserva->update($validatedData);

            return redirect()->back()->with('success', 'Reserva atualizada com sucesso!');
        } catch (\Exception $e) {
            // Redirecionar de volta com os inputs para que old() funcione
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar reserva!: ' . $e->getMessage());
        }
    }

    public function destroy(Reserva $reserva)
    {
        try {
            $reserva->delete();
            return redirect()->route('reserva.index')->with('success', 'Reserva removida com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover reserva!');
        }
    }

    public function quartosDisponiveis(Request $request)
    {
        $checkin = Carbon::createFromFormat('d/m/Y', $request->checkin);
        $checkout = Carbon::createFromFormat('d/m/Y', $request->checkout);
        $reservaId = $request->reserva_id; // pode ser null

        $quartosIndisponiveis = Reserva::where(function ($query) use ($checkin, $checkout) {
            $query->whereBetween('data_checkin', [$checkin, $checkout->copy()->subDay()])
                ->orWhereBetween('data_checkout', [$checkin->copy()->addDay(), $checkout])
                ->orWhere(function ($query) use ($checkin, $checkout) {
                    $query->where('data_checkin', '<', $checkin)
                        ->where('data_checkout', '>', $checkout);
                });
        })
            ->when($reservaId, function ($query, $reservaId) {
                $query->where('id', '!=', $reservaId);
            })
            ->pluck('quarto_id');

        $quartosDisponiveis = Quarto::with('categoria')
            ->whereNotIn('id', $quartosIndisponiveis)
            ->get()
            ->groupBy('categoria.titulo');

        // Formatar resposta para incluir informações da categoria
        $response = [];
        foreach ($quartosDisponiveis as $categoria => $quartos) {
            $response[] = [
                'categoria' => $categoria,
                'quartos' => $quartos->map(function ($quarto) {
                    return [
                        'id' => $quarto->id,
                        'nome' => $quarto->nome,
                        'categoria_id' => $quarto->categoria_id
                    ];
                })
            ];
        }

        return response()->json($response);
    }

    public function finalizar($id)
    {
        try {
            $reserva = Reserva::findOrFail($id);

            // Verificar se a reserva pode ser finalizada
            if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva já foi finalizada ou cancelada.'
                ], 400);
            }

            // Verificar se o valor foi totalmente recebido
            $transacoes = Transacao::where('reserva_id', $reserva->id)
                ->where('status', true)
                ->get();

            $totalRecebido = $transacoes->where('tipo', 'pagamento')->sum('valor');
            $totalDescontos = $transacoes->where('tipo', 'desconto')->sum('valor');

            // Calcular total da reserva (incluindo produtos)
            $checkin = Carbon::parse($reserva->data_checkin);
            $checkout = Carbon::parse($reserva->data_checkout);
            $numDiarias = $checkout->diffInDays($checkin);
            $totalDiarias = $reserva->valor_diaria * $numDiarias;
            $totalProdutos = $transacoes->where('categoria', 'produtos')->sum('valor');
            $totalGeral = $totalDiarias + $totalProdutos;

            $faltaLancar = $totalGeral - $totalRecebido - $totalDescontos;

            if ($faltaLancar > 0.01) { // Tolerância para diferenças de centavos
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível finalizar a reserva. Ainda há valores pendentes de recebimento.'
                ], 400);
            }

            $reserva->situacao = 'finalizada';
            $reserva->save();

            return response()->json([
                'success' => true,
                'message' => 'Reserva finalizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelar($id)
    {
        try {
            DB::beginTransaction();
            $reserva = Reserva::findOrFail($id);

            // Verificar se a reserva pode ser cancelada
            if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva já foi finalizada ou cancelada.'
                ], 400);
            }

            // Obter todas as transações da reserva
            $transacoes = Transacao::where('reserva_id', $reserva->id)->get();

            // Instanciar o TransacaoController para usar seus métodos privados
            $transacaoController = app(TransacaoController::class);

            foreach ($transacoes as $transacao) {
                $dataTransacao = Carbon::parse($transacao->data_pagamento);
                $hoje = Carbon::today();

                if ($dataTransacao->isSameDay($hoje)) {
                    // Transação do mesmo dia: criar movimentação de cancelamento no FluxoCaixa
                    $transacaoController->cancelarMovimentacaoCaixa($transacao);
                } else {
                    // Transação de dias anteriores: criar ContasAPagar
                    $transacaoController->criarContasAPagar($transacao);
                }
                // Remover a transação após processar seu cancelamento/estorno
                $transacao->delete();
            }

            $reserva->situacao = 'cancelado';
            $reserva->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada com sucesso! Todas as transações foram processadas.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function hospedar($id)
    {
        try {
            $reserva = Reserva::findOrFail($id);

            // Verificar se hoje é o dia do check-in
            $hoje = Carbon::today();
            $dataCheckin = Carbon::parse($reserva->data_checkin);

            if (!$hoje->equalTo($dataCheckin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'O check-in só pode ser realizado na data prevista.'
                ], 400);
            }

            // Verificar se a situação permite hospedagem
            if (!in_array($reserva->situacao, ['reserva'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva não está em situação adequada para hospedagem.'
                ], 400);
            }

            if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva já foi finalizada ou cancelada.'
                ], 400);
            }

            $reserva->situacao = 'hospedado';
            $reserva->hora_checkin = Carbon::now()->format('H:i:s');
            $reserva->save();

            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para ser chamado automaticamente quando um pagamento é adicionado
     * Verifica se deve alterar situação de pré-reserva para reserva
     */
    public function verificarMudancaSituacao($reservaId)
    {
        $reserva = Reserva::find($reservaId);

        if ($reserva && $reserva->situacao === 'pre-reserva') {
            $temPagamentos = Transacao::where('reserva_id', $reservaId)
                ->where('tipo', 'pagamento')
                ->where('status', true)
                ->exists();

            if ($temPagamentos) {
                $reserva->situacao = 'reserva';
                $reserva->save();
            }
        }
    }
    public function show()
    {}
}

<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Hospede;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use App\Models\Funcionario;
use App\Models\LogReserva;
use App\Models\PreferenciasHotel;
use App\Models\Produto;
use App\Models\Transacao;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                $categoria = $quarto->categoria;
                $tarifa = $categoria->tarifa;

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
                $padraoAdultos = $tarifa->padrao_adultos ?? 0;
                $padraoCriancas = $tarifa->padrao_criancas ?? 0;
                $adicionalAdulto = (float) ($tarifa->adicional_adulto ?? 0);
                $adicionalCrianca = (float) ($tarifa->adicional_crianca ?? 0);

                $extrasAdultos = max(0, $validatedData['n_adultos'] - $padraoAdultos);
                $extrasCriancas = max(0, $validatedData['n_criancas'] - $padraoCriancas);

                $valorDiariaBase = (float) $validatedData['valor_diaria'];
                $valorDiariaFinal = $valorDiariaBase + ($extrasAdultos * $adicionalAdulto) + ($extrasCriancas * $adicionalCrianca);

                $validatedData['valor_diaria'] = number_format($valorDiariaFinal, 2, '.', '');
            } else {
                // Remove máscara se foi preenchido manualmente
                $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);
            }

            $reserva = Reserva::create($validatedData);

            LogReserva::registrarCriacao($reserva, Auth::id());

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

        $logs = LogReserva::where('reserva_id', $reserva->id)
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->get();

        // Verificar se hoje é o dia do check-in
        $hoje = Carbon::today();
        $dataCheckin = Carbon::parse($reserva->data_checkin);
        $podeHospedar = $hoje->equalTo($dataCheckin) &&
            in_array($reserva->situacao, ['reserva']) &&
            !in_array($reserva->situacao, ['finalizada', 'cancelado']);

        return view('reserva.create', compact('reserva', 'quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'podeHospedar', 'logs'));
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

            // Guardar dados antigos para o log
            $dadosAntigos = $reserva->toArray();

            // Verificar se o status mudou para registrar no log
            $statusAntigo = $reserva->situacao;

            $reserva->update($validatedData);

            // Registrar log de edição
            LogReserva::registrarEdicao($reserva, Auth::id(), $dadosAntigos);

            // Se o status mudou, registrar log específico de alteração de status
            if ($statusAntigo !== $reserva->situacao) {
                LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);
            }

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

            // Guardar status antigo para o log
            $statusAntigo = $reserva->situacao;

            $reserva->situacao = 'finalizada';
            $reserva->save();

            // Registrar log de alteração de status
            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

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

            // Guardar status antigo para o log
            $statusAntigo = $reserva->situacao;

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

            // Registrar log de alteração de status
            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

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



    public function cancelarComSupervisor(Request $request, $id)
    {
        $request->validate([
            'senha_supervisor' => ['required', 'string', 'min:4'],
        ]);

        $reserva = Reserva::findOrFail($id);

        if (in_array($reserva->situacao, ['finalizada', 'cancelado'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta reserva já foi finalizada ou cancelada.'
            ], 400);
        }

        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();

        $supervisor = null;
        foreach ($funcionarios as $f) {
            $senhaDigitada = (string) $request->input('senha_supervisor');

            // Busca quem tem senha_supervisor
            $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();

            $supervisor = null;
            foreach ($funcionarios as $f) {
                $hashBanco = trim((string) $f->senha_supervisor);
                if ($hashBanco === '') continue;


                if (hash_equals($hashBanco, md5($senhaDigitada))) {
                    $supervisor = $f;
                    break;
                }

                if (str_starts_with($hashBanco, '$2') || str_starts_with($hashBanco, '$argon')) {
                    $hashNorm = preg_replace('/^\$2b\$/', '$2y$', $hashBanco);
                    if (password_verify($senhaDigitada, $hashNorm)) {
                        $supervisor = $f;
                        break;
                    }
                }
            }

            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha de supervisor inválida.'
                ], 403);
            }
        }

        if (!$supervisor) {
            return response()->json(['success' => false, 'message' => 'Senha de supervisor inválida.'], 403);
        }

        try {
            DB::beginTransaction();

            // snapshot antes de alterar (para log)
            $statusAntigo = $reserva->situacao;
            $dadosAntigos = $reserva->toArray();

            // Processa transações da reserva (mesmo dia -> cancelar no caixa; dias anteriores -> contas a pagar)
            $transacoes = Transacao::where('reserva_id', $reserva->id)->get();
            $transacaoController = app(TransacaoController::class);
            $hoje = Carbon::today();

            foreach ($transacoes as $transacao) {
                $data = $transacao->data_pagamento ? Carbon::parse($transacao->data_pagamento) : null;

                if ($data && $data->isSameDay($hoje)) {
                    $transacaoController->cancelarMovimentacaoCaixa($transacao);
                } else {
                    $transacaoController->criarContasAPagar($transacao);
                }

                $transacao->delete();
            }

            // Atualiza reserva
            $reserva->situacao = 'cancelado';
            $reserva->save();

            // Log 1: alteração de status (mantém seu padrão)
            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

            // Log 2: detalhado (executor + supervisor)
            LogReserva::create([
                'reserva_id'   => $reserva->id,
                'usuario_id'   => Auth::id(),
                'tipo'         => 'exclusao',
                'descricao'    => "Exclusão da reserva #{$reserva->id}",
                'dados_antigos' => $dadosAntigos,
                'dados_novos'  => [
                    'acao'            => "Exclusão da reserva #{$reserva->id}",
                    'executante_id'   => Auth::id(),
                    'executante_nome' => Auth::user()->name ?? Auth::user()->nome,
                    'supervisor_id'   => $supervisor->id,
                    'supervisor_nome' => $supervisor->nome ?? $supervisor->name,
                    'data_hora'       => now()->toDateTimeString(),
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada com sucesso! Todas as transações foram processadas.'
            ]);
        } catch (\Throwable $e) {
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

            // Guardar status antigo para o log
            $statusAntigo = $reserva->situacao;

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

            // Registrar log de alteração de status
            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

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
    public function show() {}


    public function marcarNoShowComSupervisor(Request $request, $id)
    {
        $request->validate([
            'senha_supervisor' => ['required', 'string', 'min:1'],
        ]);

        $reserva = Reserva::findOrFail($id);

        // Só permite marcar no-show quando ainda é "reserva"
        if ($reserva->situacao !== 'reserva') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas reservas em situação "reserva" podem ser marcadas como No Show.'
            ], 400);
        }

        // === Verificação simples como na outra tela ===
        $senhaDigitada = (string) $request->input('senha_supervisor');

        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();
        $supervisor = null;

        foreach ($funcionarios as $f) {
            $hashBanco = trim((string) $f->senha_supervisor);
            if ($hashBanco === '') continue;

            // Compare com o mesmo algoritmo da outra tela (ex.: MD5)
            if (hash_equals($hashBanco, md5($senhaDigitada))) { // <-- troque md5 por sha1 se for o caso
                $supervisor = $f;
                break;
            }

            // (Opcional) se alguns já estiverem em bcrypt/argon, aceite também:
            if (preg_match('/^\$(2[aby]|argon)/', $hashBanco)) {
                $hashNorm = preg_replace('/^\$2b\$/', '$2y$', $hashBanco);
                if (password_verify($senhaDigitada, $hashNorm)) {
                    $supervisor = $f;
                    break;
                }
            }
        }

        if (!$supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'Senha de supervisor inválida.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $statusAntigo = $reserva->situacao;
            $dadosAntigos = $reserva->toArray();

            // Atualiza status para noshow
            $reserva->situacao = 'noshow';
            $reserva->save();

            // Log 1: mantém o padrão que você já usa
            LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);

            // Log 2: detalhado (executor + supervisor)
            LogReserva::create([
                'reserva_id'    => $reserva->id,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'status_alterado',
                'descricao'     => "No Show da reserva #{$reserva->id}",
                'dados_antigos' => $dadosAntigos,
                'dados_novos'   => [
                    'acao'            => "No Show da reserva #{$reserva->id}",
                    'executante_id'   => Auth::id(),
                    'executante_nome' => Auth::user()->name ?? Auth::user()->nome,
                    'supervisor_id'   => $supervisor->id,
                    'supervisor_nome' => $supervisor->nome ?? $supervisor->name,
                    'data_hora'       => now()->toDateTimeString(),
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva marcada como No Show com sucesso!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar No Show: ' . $e->getMessage()
            ], 500);
        }
    }
}

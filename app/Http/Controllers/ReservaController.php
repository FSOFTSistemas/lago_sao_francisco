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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Mail\VoucherReservaEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    private $canaisVenda = ['WhatsApp', 'Instagram', 'Telefone', 'Indicação', 'Balcão', 'Facebook', 'Email', 'Outros'];

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
        $canaisVenda = $this->canaisVenda;
        $vendedores = Funcionario::all();

        return view('reserva.create', compact('quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'checkin', 'checkout', 'canaisVenda', 'vendedores'));
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
                'valor_total' => 'nullable', // Mudamos para nullable pois será calculado aqui
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
                'observacoes' => 'nullable|string',
                'placa_veiculo' => 'nullable|string|max:10',
                'canal_venda' => 'nullable|string|in:' . implode(',', $this->canaisVenda),
                'vendedor_id' => 'nullable|exists:funcionarios,id',
                'hospedes_secundarios' => 'nullable|array',
                'hospedes_secundarios.*' => 'exists:hospedes,id',
            ]);

            $preferencia = PreferenciasHotel::first();
            
            // --- 1. Lógica para definir o Valor da Diária ---
            if ($preferencia->valor_diaria === 'tarifario' || empty($validatedData['valor_diaria'])) {
                $quarto = Quarto::with('categoria.tarifa')->find($validatedData['quarto_id']);
                $categoria = $quarto->categoria;
                $tarifa = $categoria->tarifa;

                $checkin = \Carbon\Carbon::parse($validatedData['data_checkin']);
                $checkout = \Carbon\Carbon::parse($validatedData['data_checkout']);

                $periodo = \Carbon\CarbonPeriod::create($checkin, $checkout->subDay());

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
                $validatedData['valor_diaria'] = number_format($mediaTarifa, 2, '.', ''); // Formato banco (ponto)
                
                // Cálculo de extras (adultos/crianças)
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
                // Se foi manual, apenas remove a máscara (Ex: 1.200,50 -> 1200.50)
                $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);
            }

            // --- 2. Lógica para calcular o Valor Total (NOVO) ---
            $dtCheckin = \Carbon\Carbon::parse($validatedData['data_checkin']);
            $dtCheckout = \Carbon\Carbon::parse($validatedData['data_checkout']);
            
            // Diferença em dias
            $dias = $dtCheckin->diffInDays($dtCheckout);
            
            // Garante pelo menos 1 dia se as datas forem iguais ou checkin > checkout (embora a validação usually impeça checkin > checkout)
            if ($dias < 1) $dias = 1;

            // Multiplicação
            $validatedData['valor_total'] = (float)$validatedData['valor_diaria'] * $dias;
            // ----------------------------------------------------
            // Cria a reserva com os dados validados e calculados
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
        $vendedores = Funcionario::all();

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
            

            $canaisVenda = $this->canaisVenda;
        return view('reserva.create', compact('reserva', 'quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'podeHospedar', 'logs', 'canaisVenda', 'vendedores'));
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
                'valor_total' => 'nullable', // Alterado para nullable pois calculamos no backend
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado,finalizada,cancelado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
                'observacoes' => 'nullable|string',
                'placa_veiculo' => 'nullable|string|max:10',
                'canal_venda' => 'nullable|string|in:' . implode(',', $this->canaisVenda),
                'vendedor_id' => 'nullable|exists:funcionarios,id',
                'hospedes_secundarios' => 'nullable|array',
                'hospedes_secundarios.*' => 'exists:hospedes,id',
            ]);

            // Validações específicas de situação
            $situacaoAtual = $reserva->situacao;
            $novaSituacao = $validatedData['situacao'];

            if ($situacaoAtual === 'hospedado' && $novaSituacao !== 'hospedado') {
                return redirect()->back()->withInput()->with('error', 'Não é possível alterar a situação de uma reserva hospedada manualmente.');
            }

            if (in_array($situacaoAtual, ['finalizada', 'cancelado']) && $novaSituacao !== $situacaoAtual) {
                return redirect()->back()->withInput()->with('error', 'Não é possível alterar a situação de uma reserva finalizada ou cancelada.');
            }

            if ($situacaoAtual === 'pre-reserva' && $novaSituacao === 'reserva') {
                $temPagamentos = Transacao::where('reserva_id', $reserva->id)
                    ->where('tipo', 'pagamento')
                    ->where('status', true)
                    ->exists();

                if (!$temPagamentos) {
                    return redirect()->back()->withInput()->with('error', 'Para alterar para "reserva", é necessário ter pelo menos um pagamento registrado.');
                }
            }

            // Remover a máscara do valor_diaria antes de salvar (Ex: 1.200,00 -> 1200.00)
            $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);

            // --- Lógica para calcular o Valor Total na Edição (NOVO) ---
            $dtCheckin = \Carbon\Carbon::parse($validatedData['data_checkin']);
            $dtCheckout = \Carbon\Carbon::parse($validatedData['data_checkout']);
            
            // Diferença em dias
            $dias = $dtCheckin->diffInDays($dtCheckout);
            
            // Garante pelo menos 1 dia
            if ($dias < 1) $dias = 1;

            // Recalcula o valor total com base na diária atualizada e datas
            $validatedData['valor_total'] = (float)$validatedData['valor_diaria'] * $dias;
            // -----------------------------------------------------------

            // Guardar dados antigos para o log
            $dadosAntigos = $reserva->toArray();
            $statusAntigo = $reserva->situacao;

            // Atualizar reserva com os dados validados e calculados
            $reserva->update($validatedData);

            // Registrar log de edição
            LogReserva::registrarEdicao($reserva, Auth::id(), $dadosAntigos);

            // Se o status mudou, registrar log específico de alteração de status
            if ($statusAntigo !== $reserva->situacao) {
                LogReserva::registrarAlteracaoStatus($reserva, Auth::id(), $statusAntigo);
            }

            return redirect()->back()->with('success', 'Reserva atualizada com sucesso!');
        } catch (\Exception $e) {
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
                        'categoria_id' => $quarto->categoria_id,
                        'ocupantes' => $quarto->categoria->ocupantes
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

    public function emitirFNRH(Reserva $reserva)
    {
        try {
            // Carregar os relacionamentos necessários
            $reserva->load('hospede', 'quarto');

            // Pegar o hóspede principal. Se não houver, falhar graciosamente.
            $hospede = $reserva->hospede;
            if (!$hospede) {
                return redirect()->back()->with('error', 'Reserva sem hóspede principal definido.');
            }

            // Calcular n° de acompanhantes
            // O total de hóspedes (adultos + crianças) menos o hóspede principal (1)
            $n_acompanhantes = max(0, ($reserva->n_adultos + $reserva->n_criancas) - 1);

            $data = [
                'reserva' => $reserva,
                'hospede' => $hospede,
                'quarto' => $reserva->quarto,
                'data_entrada' => Carbon::parse($reserva->data_checkin)->format('d/m/Y'),
                'data_saida' => Carbon::parse($reserva->data_checkout)->format('d/m/Y'),
                
                // --- ALTERAÇÃO AQUI ---
                'hora_entrada' => $reserva->hora_checkin ? Carbon::parse($reserva->hora_checkin)->format('H:i') : '',
                'hora_saida' => '', // Deixa em branco
                // --- FIM DA ALTERAÇÃO ---

                'n_acompanhantes' => $n_acompanhantes,
            ];

            // Carregar a view do PDF
            $pdf = Pdf::loadView('reserva.fnrh', $data);

            // Definir o nome do arquivo
            $fileName = 'FNRH_' . str_replace(' ', '_', $hospede->nome) . '_' . $reserva->id . '.pdf';

            // Retornar o PDF no navegador (stream)
            return $pdf->stream($fileName);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao gerar FNRH: ' . $e->getMessage());
        }
    }

    public function relatorioPorCanal(Request $request)
    {
        $query = Reserva::query();

        // Define datas padrão (mês atual) se não forem fornecidas
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Filtra por data de CRIAÇÃO da reserva
        $query->whereBetween('created_at', [$dataInicio, $dataFim]);

        $dadosRelatorio = $query
            ->select('canal_venda', DB::raw('count(*) as total'))
            ->groupBy('canal_venda')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                // Agrupar 'null' ou 'vazio' em 'Não Preenchido'
                $item->canal_venda = $item->canal_venda ?: 'Não Preenchido';
                return $item;
            })
            ->groupBy('canal_venda') // Reagrupar caso 'Não Preenchido' já exista
            ->map(function ($group) {
                return $group->sum('total'); // Soma os totais
            });

        return view('reserva.relatorio_canal', [
            'dadosRelatorio' => $dadosRelatorio,
            'data_inicio'    => $dataInicio,
            'data_fim'       => $dataFim,
        ]);
    }

    public function enviarVoucherPorEmail(Reserva $reserva)
    {
        try {
           
            // Carregar o hóspede para pegar o e-mail
            $reserva->load('hospede');

            if (!$reserva->hospede || !$reserva->hospede->email) {
                return redirect()->back()->with('error', 'Hóspede não possui e-mail cadastrado.');
            }
            // Dispara o e-mail usando a classe Mailable
            Mail::to($reserva->hospede->email)->send(new VoucherReservaEmail($reserva));

            return redirect()->back()->with('success', 'Voucher enviado por e-mail com sucesso!');

        } catch (\Exception $e) {
            dd($e);
            // Logar o erro para depuração
            Log::error('Erro ao enviar voucher por e-mail: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao enviar e-mail. Verifique as configurações.');
        }
    }

    public function cafeDaManha(Request $request)
    {
        // Padrão: Hoje
        $dataInicio = $request->input('data_inicio', Carbon::today()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::today()->format('Y-m-d'));

        $reservas = $this->buscarReservasParaCafe($dataInicio, $dataFim);

        return view('reserva.relatorio.cafe', compact('reservas', 'dataInicio', 'dataFim'));
    }

    // Geração do PDF
    public function cafeDaManhaPdf(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::today()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::today()->format('Y-m-d'));

        $reservas = $this->buscarReservasParaCafe($dataInicio, $dataFim);

        $pdf = Pdf::loadView('reserva.relatorio.cafe_pdf', [
            'reservas' => $reservas,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ]);

        return $pdf->stream('lista_cafe_manha.pdf');
    }

    // Método auxiliar privado para reaproveitar a query
    private function buscarReservasParaCafe($inicio, $fim)
    {
        // 1. Removemos 'hospedes_secundarios' do with(), pois não é uma relação
        $reservas = Reserva::with(['quarto', 'hospede'])
            ->whereIn('situacao', ['reserva', 'hospedado'])
            ->where(function ($query) use ($inicio, $fim) {
                $query->where('data_checkin', '<=', $fim)
                      ->where('data_checkout', '>=', $inicio);
            })
            ->orderBy('data_checkin')
            ->get();

        // 2. Coletamos todos os IDs de hóspedes secundários de todas as reservas encontradas
        $todosIdsSecundarios = [];
        foreach ($reservas as $reserva) {
            // Garante que é array (caso o cast não esteja configurado no model)
            $secundarios = $reserva->hospedes_secundarios;
            if (is_string($secundarios)) {
                $secundarios = json_decode($secundarios, true);
            }
            
            if (is_array($secundarios)) {
                $todosIdsSecundarios = array_merge($todosIdsSecundarios, $secundarios);
            }
        }
        
        // Remove duplicados
        $todosIdsSecundarios = array_unique($todosIdsSecundarios);

        // 3. Buscamos os nomes desses hóspedes no banco (uma única query rápida)
        // Criamos um mapa: [ID => ObjetoHospede]
        $mapaHospedes = Hospede::whereIn('id', $todosIdsSecundarios)->get()->keyBy('id');

        // 4. "Anexamos" os objetos reais de volta em cada reserva
        foreach ($reservas as $reserva) {
            $listaObjetos = collect([]);
            
            $secundarios = $reserva->hospedes_secundarios;
            if (is_string($secundarios)) $secundarios = json_decode($secundarios, true);

            if (is_array($secundarios)) {
                foreach ($secundarios as $id) {
                    if (isset($mapaHospedes[$id])) {
                        $listaObjetos->push($mapaHospedes[$id]);
                    }
                }
            }
            
            // Criamos um atributo virtual 'lista_secundarios' para usar na View
            $reserva->setRelation('lista_secundarios', $listaObjetos);
        }

        return $reservas;
    }



    // --- LÓGICA CENTRALIZADA DE CÁLCULO (Privada) ---
    private function calcularDadosVendas($dataInicio, $dataFim)
    {
        $vendedores = Funcionario::all();

        $relatorio = $vendedores->map(function ($vendedor) use ($dataInicio, $dataFim) {
            
            // 1. Soma Reservas
            $totalReservas = Reserva::where('vendedor_id', $vendedor->id)
                ->whereBetween('created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59'])
                ->where('situacao', '!=', 'cancelado') 
                ->sum('valor_total');

            // 2. Soma Day Uses
            $totalDayUse = 0;
            if (class_exists('App\Models\DayUse')) {
                $totalDayUse = \App\Models\DayUse::where('vendedor_id', $vendedor->id)
                    ->whereBetween('created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59'])
                    ->sum('total');
            }

            return [
                'nome' => $vendedor->nome,
                'total_reserva' => $totalReservas,
                'total_dayuse' => $totalDayUse,
                'total_geral' => $totalReservas + $totalDayUse
            ];
        });

        // Filtra > 0 e ordena
        return $relatorio->filter(function ($item) {
            return $item['total_geral'] > 0;
        })->sortByDesc('total_geral');
    }

    // --- TELA WEB ---
    public function relatorioVendas(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Usa a função privada para pegar os dados
        $relatorio = $this->calcularDadosVendas($dataInicio, $dataFim);

        return view('relatorios.vendas', compact('relatorio', 'dataInicio', 'dataFim'));
    }

    // --- GERAÇÃO DE PDF ---
    public function relatorioVendasPdf(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Reusa a mesma lógica de cálculo
        $relatorio = $this->calcularDadosVendas($dataInicio, $dataFim);

        // Gera o PDF
        $pdf = Pdf::loadView('relatorios.vendas_pdf', [
            'relatorio' => $relatorio,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim
        ]);

        return $pdf->stream('relatorio_vendas.pdf');
    }
}

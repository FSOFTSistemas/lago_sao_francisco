<?php

namespace App\Http\Controllers;

use App\Models\Adicional;
use App\Models\Aluguel;
use App\Models\AluguelPagamento;
use App\Models\BuffetEscolha;
use App\Models\Caixa;
use App\Models\Cardapio;
use App\Models\Cliente;
use App\Models\ContasAReceber;
use App\Models\Espaco;
use App\Models\FormaPagamento;
use App\Models\Movimento;
use App\Models\PacoteEvento;
use App\Models\PlanoDeConta;
use App\Services\CaixaService;
use App\Services\ContasService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AluguelController extends Controller
{
    protected $caixaService;

    public function __construct(CaixaService $caixaService)
    {
        $this->caixaService = $caixaService;
    }

    public function index()
    {
        $aluguel = Aluguel::with(['cliente', 'espaco'])
            ->where('tipo', '!=', 'bloqueio')
            ->latest()
            ->paginate(15);

        return view('aluguel.index', compact('aluguel'));
    }

    public static function getClienteBloqueado(?int $empresaId = null): Cliente
    {
        $empresaId = $empresaId ?: (session('empresa_id') ?: Auth::user()?->empresa_id ?: 1);

        return Cliente::firstOrCreate(
            [
                'nome_razao_social' => 'Bloqueado',
                'empresa_id' => $empresaId,
            ],
            [
                'tipo' => 'PJ',
            ]
        );
    }

    public function create()
    {
        $empresaId = session('empresa_id') ?: Auth::user()?->empresa_id;
        $usuarioId = Auth::id();
        $caixa = ($empresaId && $usuarioId)
            ? Caixa::abertoHojePara($empresaId, $usuarioId)->first()
            : null;

        if (! $caixa) {
            return redirect()->route('fluxoCaixa.index')
                ->with('sweet_error', 'Você precisa estar com o caixa do dia aberto para agendar um evento.');
        }

        $clientes = Cliente::all();
        $clienteBloqueado = self::getClienteBloqueado($empresaId);
        $espacos = Espaco::all();
        $formasPagamento = FormaPagamento::all();
        $adicionais = Adicional::all();
        $cardapios = Cardapio::all();
        $adicionaisSelecionados = collect();
        $pacotesEvento = PacoteEvento::orderBy('nome')->orderBy('ano')->get()->groupBy('categoria');
        $pacotesEventoSelecionados = collect();

        return view('aluguel.create', compact(
            'clientes',
            'clienteBloqueado',
            'espacos',
            'formasPagamento',
            'adicionais',
            'cardapios',
            'adicionaisSelecionados',
            'pacotesEvento',
            'pacotesEventoSelecionados'
        ));
    }

    public function bloquearData(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'espaco_id' => 'required',
            'observacoes' => 'nullable|string',
        ]);

        $empresaId = session('empresa_id') ?: Auth::user()?->empresa_id ?: 1;
        $usuarioId = Auth::id();

        $cliente = self::getClienteBloqueado($empresaId);
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;

        $espacosIds = [];
        if ($request->espaco_id === 'todos' || $request->input('espaco_bloqueio_opcao') === 'todos') {
            $espacosIds = Espaco::pluck('id')->toArray();
        } else {
            $espaco = Espaco::findOrFail($request->espaco_id);
            $espacosIds = [$espaco->id];
        }

        // Verificar conflitos
        $conflito = Aluguel::whereIn('espaco_id', $espacosIds)
            ->where('status', '!=', 'cancelado')
            ->where(function ($query) use ($dataInicio, $dataFim) {
                $query->where('data_inicio', '<=', $dataFim)
                    ->where('data_fim', '>=', $dataInicio);
            })
            ->with('espaco')
            ->first();

        $isJson = ($request->is('eventos/bloquear-data*') || $request->routeIs('aluguel.bloquear-data') || $request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->isJson());

        if ($conflito) {
            $nomeEspaco = $conflito->espaco->nome ?? 'selecionado';
            $msgConflito = "O espaço {$nomeEspaco} já possui um agendamento ou bloqueio no período de ".Carbon::parse($dataInicio)->format('d/m/Y').' a '.Carbon::parse($dataFim)->format('d/m/Y').'.';

            if ($isJson) {
                return response()->json(['success' => false, 'message' => $msgConflito], 422);
            }

            return redirect()->back()->withInput()->with('error', $msgConflito);
        }

        DB::beginTransaction();
        try {
            foreach ($espacosIds as $espId) {
                Aluguel::create([
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'espaco_id' => $espId,
                    'cliente_id' => $cliente->id,
                    'tipo' => 'bloqueio',
                    'status' => 'pago',
                    'subtotal' => 0,
                    'total' => 0,
                    'acrescimo' => 0,
                    'desconto' => 0,
                    'observacoes' => $request->observacoes ?: 'Data bloqueada',
                    'empresa_id' => $empresaId,
                ]);
            }

            DB::commit();

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => count($espacosIds) > 1 ? 'Datas bloqueadas para todos os espaços com sucesso!' : 'Data bloqueada com sucesso!',
                ]);
            }

            return redirect()->route('aluguel.index')->with('success', count($espacosIds) > 1 ? 'Datas bloqueadas para todos os espaços com sucesso!' : 'Data bloqueada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($isJson) {
                return response()->json(['success' => false, 'message' => 'Erro ao criar bloqueio: '.$e->getMessage()], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Erro ao criar bloqueio: '.$e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if ($request->input('situacao_aluguel') === 'bloqueio' || $request->input('tipo') === 'bloqueio') {
            return $this->bloquearData($request);
        }

        try {
            $empresaId = session('empresa_id') ?: Auth::user()?->empresa_id;
            $usuarioId = Auth::id();
            $caixa = ($empresaId && $usuarioId)
                ? Caixa::abertoHojePara($empresaId, $usuarioId)->first()
                : null;

            if (! $caixa) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Você precisa estar com o caixa do dia aberto para agendar um evento.');
            }

            $validated = $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'cliente_id' => 'required|exists:clientes,id',
                'espaco_id' => 'required|exists:espacos,id',
                'forma_pagamento_id' => 'nullable|exists:forma_pagamentos,id',
                'observacoes' => 'nullable|string',
                'subtotal' => 'nullable|numeric',
                'total' => 'nullable|numeric',
                'acrescimo' => 'nullable|numeric',
                'desconto' => 'nullable|numeric',
                'parcelas' => 'nullable|integer',
                'vencimento' => 'nullable|date',
                'contrato' => 'nullable|string',
                'status' => 'nullable|string',
                'numero_pessoas_buffet' => 'nullable|integer|min:1',
                'cardapio_id' => 'nullable|exists:cardapios,id',
                'tipo' => 'string|required',
                'cerimonial_responsavel' => 'nullable|string|max:255',
                // Campos do buffet vindos do JavaScript
                'buffet_categorias_escolhidas' => 'nullable|string',
                'buffet_opcao_escolhida' => 'nullable|integer',
                // Campos de pagamento
                'pagamentos_json' => 'nullable|string',
            ]);

            $validated['empresa_id'] = $empresaId;

            $pagamentosJson = $request->filled('pagamentos_json')
                ? json_decode($request->pagamentos_json, true) ?? []
                : [];
            $totalPago = (float) collect($pagamentosJson)->sum('valor');
            $totalAluguel = (float) ($validated['total'] ?? 0);
            $validated['status'] = ($totalAluguel > 0 && $totalPago + 0.01 >= $totalAluguel)
                ? 'pago'
                : 'pendente';

            // Usar transação para garantir consistência
            DB::beginTransaction();

            try {
                // Criação do aluguel
                $aluguel = Aluguel::create($validated);

                // Relacionar itens adicionais
                $this->salvarAdicionais($aluguel, $request);

                // Relacionar pacotes de evento (Ilhas Adicionais / Refeição Staff)
                $this->salvarPacotesEvento($aluguel, $request);

                // Salvar escolhas do buffet se existirem
                if ($request->filled('buffet_categorias_escolhidas') || $request->filled('buffet_opcao_escolhida')) {
                    $this->salvarEscolhasBuffet($aluguel, $request);
                }

                // Salvar pagamentos se existirem
                if ($request->filled('pagamentos_json')) {
                    $this->salvarPagamentos($aluguel, $request);
                }

                foreach ($aluguel->pagamentos as $pagamento) {
                    $forma = strtolower($pagamento->formaPagamento->descricao ?? '');

                    if (str_contains($forma, 'crediário')) {
                        $this->criarContasAReceber(
                            $aluguel,
                            $pagamento->valor,
                            $pagamento->forma_pagamento_id,
                            $request->parcelas ?? 1
                        );
                    }
                }

                // ✅ Criar fluxo de caixa com os pagamentos
                $this->salvarFluxosDePagamento($aluguel, $caixa);

                DB::commit();

                return redirect()->route('aluguel.create')->with('success', 'Aluguel criado com sucesso!');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar Aluguel: '.$e->getMessage());
        }
    }

    public function edit(Aluguel $aluguel)
    {
        $aluguel->load([
            'cliente',
            'espaco',
            'formaPagamento',
            'cardapio.secoes.categorias.itens',
            'cardapio.opcoes.categorias.itens',
            'buffetEscolhas.categoria',
            'buffetEscolhas.item',
            'buffetEscolhas.opcaoRefeicao',
            'pagamentos.formaPagamento',
        ]);

        // Dados de apoio para os selects
        $clientes = Cliente::all();
        $espacos = Espaco::all();
        $cardapios = Cardapio::all();
        $formasPagamento = FormaPagamento::all();
        $adicionais = Adicional::all();

        // Itens das categorias que foram selecionados (Buffet)
        $itensSelecionados = BuffetEscolha::where('aluguel_id', $aluguel->id)
            ->where('tipo', 'categoria_item')
            ->pluck('item_id')
            ->toArray();

        // Opção de refeição escolhida (Buffet)
        $opcaoSelecionada = BuffetEscolha::where('aluguel_id', $aluguel->id)
            ->where('tipo', 'opcao_refeicao')
            ->value('opcao_refeicao_id');

        // Adicionais já escolhidos para o aluguel (com quantidade, observação e valor total)
        $adicionaisSelecionados = $aluguel->adicionaisAluguel()->with('adicional')->get();

        // Pacotes de evento (Ilhas Adicionais / Refeição Staff)
        $pacotesEvento = PacoteEvento::orderBy('nome')->orderBy('ano')->get()->groupBy('categoria');
        $pacotesEventoSelecionados = $aluguel->pacoteEventoAluguel()->get();

        // Estado inicial dos switches: liga se já existe alguma escolha salva daquela categoria
        $pacotesEventoIdsSelecionados = $pacotesEventoSelecionados->pluck('pacote_evento_id');
        $ilhasAdicionaisAtivo = PacoteEvento::whereIn('id', $pacotesEventoIdsSelecionados)
            ->where('categoria', 'ilha_adicional')->exists();
        $refeicaoStaffAtivo = PacoteEvento::whereIn('id', $pacotesEventoIdsSelecionados)
            ->where('categoria', 'refeicao_staff')->exists();

        $clienteBloqueado = self::getClienteBloqueado($aluguel->empresa_id);

        return view('aluguel.create', compact(
            'aluguel',
            'espacos',
            'formasPagamento',
            'clientes',
            'clienteBloqueado',
            'cardapios',
            'adicionais',
            'itensSelecionados',
            'opcaoSelecionada',
            'adicionaisSelecionados',
            'pacotesEvento',
            'pacotesEventoSelecionados',
            'ilhasAdicionaisAtivo',
            'refeicaoStaffAtivo'
        ));
    }

    public function update(Request $request, Aluguel $aluguel)
    {
        if ($aluguel->tipo === 'bloqueio' || $request->input('situacao_aluguel') === 'bloqueio' || $request->input('tipo') === 'bloqueio') {
            $validated = $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'espaco_id' => 'required|exists:espacos,id',
                'observacoes' => 'nullable|string',
            ]);

            $conflito = Aluguel::where('espaco_id', $validated['espaco_id'])
                ->where('id', '!=', $aluguel->id)
                ->where('status', '!=', 'cancelado')
                ->where(function ($query) use ($validated) {
                    $query->where('data_inicio', '<=', $validated['data_fim'])
                        ->where('data_fim', '>=', $validated['data_inicio']);
                })->first();

            if ($conflito) {
                return redirect()->back()->withInput()->with('error', 'O espaço selecionado já possui um agendamento ou bloqueio neste período.');
            }

            $aluguel->update([
                'data_inicio' => $validated['data_inicio'],
                'data_fim' => $validated['data_fim'],
                'espaco_id' => $validated['espaco_id'],
                'observacoes' => $validated['observacoes'] ?: 'Data bloqueada',
                'tipo' => 'bloqueio',
            ]);

            return redirect()->route('aluguel.index')->with('success', 'Bloqueio atualizado com sucesso!');
        }

        try {
            $validated = $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'cliente_id' => 'required|exists:clientes,id',
                'espaco_id' => 'required|exists:espacos,id',
                'forma_pagamento_id' => 'nullable|exists:forma_pagamentos,id',
                'observacoes' => 'nullable|string',
                'subtotal' => 'nullable|numeric',
                'total' => 'nullable|numeric',
                'acrescimo' => 'nullable|numeric',
                'desconto' => 'nullable|numeric',
                'parcelas' => 'nullable|integer',
                'vencimento' => 'nullable|date',
                'contrato' => 'nullable|string',
                'status' => 'nullable|string',
                'tipo' => 'string|required',
                'cerimonial_responsavel' => 'nullable|string|max:255',
                'numero_pessoas_buffet' => 'nullable|integer|min:1',
                'cardapio_id' => 'nullable|exists:cardapios,id',
                // Campos do buffet vindos do JavaScript
                'buffet_categorias_escolhidas' => 'nullable|string',
                'buffet_opcao_escolhida' => 'nullable|integer',
                // Campos de pagamento
                'pagamentos_json' => 'nullable|string',
            ]);

            $validated['empresa_id'] = Auth::user()->empresa_id;

            $pagamentosJson = $request->filled('pagamentos_json')
                ? json_decode($request->pagamentos_json, true) ?? []
                : [];
            $totalPago = (float) collect($pagamentosJson)->sum('valor');
            $totalAluguel = (float) ($validated['total'] ?? 0);
            if (! isset($validated['status']) || in_array($validated['status'], ['pendente', 'pago'], true)) {
                $validated['status'] = ($totalAluguel > 0 && $totalPago + 0.01 >= $totalAluguel)
                    ? 'pago'
                    : 'pendente';
            }

            // Usar transação para garantir consistência
            DB::beginTransaction();

            try {
                $aluguel->update($validated);

                // Atualizar itens adicionais
                $this->salvarAdicionais($aluguel, $request);

                // Atualizar pacotes de evento (Ilhas Adicionais / Refeição Staff)
                $this->salvarPacotesEvento($aluguel, $request);

                // Remover escolhas antigas do buffet
                BuffetEscolha::where('aluguel_id', $aluguel->id)->delete();

                // Salvar novas escolhas do buffet se existirem
                if ($request->filled('buffet_categorias_escolhidas') || $request->filled('buffet_opcao_escolhida')) {
                    $this->salvarEscolhasBuffet($aluguel, $request);
                }

                // Remover pagamentos antigos e salvar novos
                AluguelPagamento::where('aluguel_id', $aluguel->id)->delete();
                if ($request->filled('pagamentos_json')) {
                    $this->salvarPagamentos($aluguel, $request);
                }

                // Atualizar a relação many-to-many (caso esteja usando também)
                if ($request->filled('buffet_itens')) {
                    $aluguel->buffetItens()->sync($request->input('buffet_itens', []));
                }

                DB::commit();

                return redirect()->route('aluguel.index')->with('success', 'Aluguel atualizado com sucesso!');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar Aluguel: '.$e->getMessage());
        }
    }

    public function destroy(Aluguel $aluguel)
    {
        try {
            // Usar transação para garantir que todas as relações sejam removidas
            DB::beginTransaction();

            // Remover escolhas do buffet
            BuffetEscolha::where('aluguel_id', $aluguel->id)->delete();

            // Remover pagamentos
            AluguelPagamento::where('aluguel_id', $aluguel->id)->delete();

            // Remover outras relações se necessário
            if (method_exists($aluguel, 'aluguelCategoriaItems')) {
                $aluguel->aluguelCategoriaItems()->delete();
            }

            $isBloqueio = $aluguel->tipo === 'bloqueio';
            $aluguel->delete();

            DB::commit();

            $isJson = request()->expectsJson() || request()->ajax() || request()->wantsJson() || request()->isJson();

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => $isBloqueio ? 'Bloqueio removido com sucesso!' : 'Aluguel excluído com sucesso!',
                ]);
            }

            return redirect()->route('aluguel.index')->with('success', $isBloqueio ? 'Bloqueio excluído com sucesso!' : 'Aluguel excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();

            $isJson = request()->expectsJson() || request()->ajax() || request()->wantsJson() || request()->isJson();

            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao deletar: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao deletar Aluguel: '.$e->getMessage());
        }
    }

    /**
     * Salva as escolhas do buffet vindas do JavaScript
     */
    private function salvarEscolhasBuffet(Aluguel $aluguel, Request $request)
    {
        // Decodificar as categorias escolhidas (JSON)
        $categoriasEscolhidas = json_decode($request->buffet_categorias_escolhidas, true) ?? [];

        // Salvar itens das categorias escolhidas
        foreach ($categoriasEscolhidas as $categoriaId => $itensIds) {
            foreach ($itensIds as $itemId) {
                BuffetEscolha::create([
                    'aluguel_id' => $aluguel->id,
                    'tipo' => 'categoria_item',
                    'categoria_id' => $categoriaId,
                    'item_id' => $itemId,
                    'opcao_refeicao_id' => null,
                ]);
            }
        }

        // Salvar opção de refeição escolhida
        if ($request->filled('buffet_opcao_escolhida')) {
            BuffetEscolha::create([
                'aluguel_id' => $aluguel->id,
                'tipo' => 'opcao_refeicao',
                'categoria_id' => null,
                'item_id' => null,
                'opcao_refeicao_id' => $request->buffet_opcao_escolhida,
            ]);
        }
    }

    /**
     * Salva os pagamentos vindos do JavaScript
     */
    private function salvarPagamentos(Aluguel $aluguel, Request $request)
    {
        $pagamentosJson = json_decode($request->pagamentos_json, true) ?? [];

        foreach ($pagamentosJson as $pagamento) {
            AluguelPagamento::create([
                'aluguel_id' => $aluguel->id,
                'forma_pagamento_id' => $pagamento['forma_pagamento_id'],
                'valor' => $pagamento['valor'],
                'observacoes' => null,
            ]);
        }
    }

    /**
     * Método para buscar dados do cardápio via AJAX
     */
    public function getCardapioData($cardapioId)
    {
        try {
            $cardapio = Cardapio::with([
                'secoes.categorias.itens',
                'opcoes.categorias.itens',
            ])->findOrFail($cardapioId);

            return response()->json([
                'secoes' => $cardapio->secoes,
                'opcoes' => $cardapio->opcoes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cardápio não encontrado'], 404);
        }
    }

    private function calcularValorAluguel($data_inicio, $data_fim, $valor_semana, $valor_fim, $capela, $tipo_evento)
    {
        $inicio = Carbon::parse($data_inicio);
        $fim = Carbon::parse($data_fim);
        $periodo = CarbonPeriod::create($inicio, $fim);
        $total = 0;

        if ($capela && $tipo_evento == 'casamento') {
            foreach ($periodo as $data) {
                $total += $valor_fim;
            }
        } elseif ($capela && $tipo_evento == 'batizado') {
            foreach ($periodo as $data) {
                $total += $valor_semana;
            }
        } else {
            // Demais tipos de evento (inclusive em capela): mesma regra dos espaços
            // sem capela, tarifa de semana (seg-qui) e tarifa de fim de semana (sex-dom).
            foreach ($periodo as $data) {
                $total += in_array($data->dayOfWeek, [1, 2, 3, 4]) ? $valor_semana : $valor_fim;
            }
        }

        return $total;
    }

    public function calcularValor(Request $request)
    {
        $espaco = Espaco::findOrFail($request->espaco_id);

        $total = $this->calcularValorAluguel(
            $request->data_inicio,
            $request->data_fim,
            $espaco->valor_semana,
            $espaco->valor_fim,
            $espaco->capela,
            $request->tipo_evento
        );

        return response()->json(['total' => $total]);
    }

    /**
     * Salva os adicionais escolhidos no aluguel
     */
    private function salvarAdicionais(Aluguel $aluguel, Request $request)
    {
        // Limpa os anteriores
        DB::table('adicionais_aluguel')->where('aluguel_id', $aluguel->id)->delete();

        if ($request->has('adicionais')) {
            foreach ($request->adicionais as $adicionalId => $dados) {
                $quantidade = intval($dados['quantidade'] ?? 0);
                $observacao = $dados['observacao'] ?? '';

                if ($quantidade > 0) {
                    $adicional = Adicional::find($adicionalId);
                    $valorTotal = $quantidade * $adicional->valor;

                    DB::table('adicionais_aluguel')->insert([
                        'aluguel_id' => $aluguel->id,
                        'adicional_id' => $adicionalId,
                        'quantidade' => $quantidade,
                        'valor_total' => $valorTotal,
                        'observacao' => $observacao,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Salva os pacotes de evento selecionados (Ilhas Adicionais / Refeição Staff).
     * Cada linha do request já traz o pacote_evento_id do ano escolhido pelo usuário,
     * então o valor unitário é sempre o cadastrado naquele registro específico.
     */
    private function salvarPacotesEvento(Aluguel $aluguel, Request $request)
    {
        DB::table('pacote_evento_aluguel')->where('aluguel_id', $aluguel->id)->delete();

        if ($request->has('pacotes_evento')) {
            foreach ($request->pacotes_evento as $dados) {
                $quantidade = intval($dados['quantidade'] ?? 0);
                $pacoteEventoId = $dados['pacote_evento_id'] ?? null;
                $observacao = $dados['observacao'] ?? '';

                if ($quantidade > 0 && $pacoteEventoId) {
                    $pacote = PacoteEvento::find($pacoteEventoId);

                    if ($pacote) {
                        $valorTotal = $quantidade * $pacote->valor;

                        DB::table('pacote_evento_aluguel')->insert([
                            'aluguel_id' => $aluguel->id,
                            'pacote_evento_id' => $pacoteEventoId,
                            'quantidade' => $quantidade,
                            'valor_total' => $valorTotal,
                            'observacao' => $observacao,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    private function salvarFluxosDePagamento(Aluguel $aluguel, Caixa $caixa)
    {
        foreach ($aluguel->pagamentos as $pagamento) {
            $formaPagamento = $pagamento->formaPagamento;

            // ignora se for crediário
            $descricaoForma = strtolower($formaPagamento->descricao ?? '');
            if (str_contains($descricaoForma, 'crediário')) {
                continue;
            }

            $tipoMov = $formaPagamento->movimentoDescricao('venda');

            $movimentoId = Movimento::where('descricao', $tipoMov)->value('id');

            if (! $movimentoId) {
                Log::warning("Movimentação de caixa não registrada: nenhum Movimento encontrado para '{$tipoMov}' (aluguel #{$aluguel->id}, forma de pagamento '{$formaPagamento->descricao}').");

                continue; // pula se não encontrar o movimento
            }

            $planoContaId = PlanoDeConta::idPorDescricao('Aluguel de Espaços', $aluguel->empresa_id, 'receita');

            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => 'Aluguel #'.$aluguel->id,
                'valor' => $pagamento->valor,
                'valor_total' => $pagamento->valor,
                'tipo' => 'entrada',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => $planoContaId,
            ]);
        }
    }

    private function criarContasAReceber(Aluguel $aluguel, float $valorTotal, int $formaPagamentoId, int $qtdParcelas)
    {
        $clienteId = $aluguel->cliente_id;
        $empresaId = $aluguel->empresa_id;
        $descricaoBase = "Aluguel #{$aluguel->id}";
        $vencimento = Carbon::now(); // ou a data desejada
        $grupoId = ($qtdParcelas > 1) ? mt_rand(100000, 999999999) : null;
        $valorParcela = round($valorTotal / $qtdParcelas, 2);

        for ($i = 1; $i <= $qtdParcelas; $i++) {
            ContasAReceber::create([
                'descricao' => $qtdParcelas > 1 ? "{$descricaoBase} | {$i}/{$qtdParcelas}" : $descricaoBase,
                'valor' => $valorParcela,
                'data_vencimento' => $vencimento,
                'status' => 'pendente',
                'cliente_id' => $clienteId,
                'empresa_id' => $empresaId,
                'grupo_id' => $grupoId,
                'plano_de_contas_id' => PlanoDeConta::idPorDescricao('Aluguel de Espaços', $empresaId, 'receita'),
                'venda_id' => null,
            ]);

            $vencimento = ContasService::proximoMes($vencimento);
        }
    }
}

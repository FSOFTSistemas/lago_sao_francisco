<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Models\Cliente;
use App\Models\Espaco;
use App\Models\FormaPagamento;
use App\Models\Adicional;
use App\Models\Cardapio;
use App\Models\BuffetEscolha;
use App\Models\AluguelPagamento;
use App\Models\Caixa;
use App\Models\ContasAReceber;
use App\Models\FluxoCaixa;
use App\Models\Movimento;
use App\Services\CaixaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AluguelController extends Controller
{
    protected $caixaService;

    public function __construct(CaixaService $caixaService)
    {
        $this->caixaService = $caixaService;
    }

    public function index()
    {
        $aluguel = Aluguel::with(['cliente', 'espaco'])->latest()->paginate(15);
        return view('aluguel.index', compact('aluguel'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $espacos = Espaco::all();
        $formasPagamento = FormaPagamento::all();
        $adicionais = Adicional::all();
        $cardapios = Cardapio::all();
        $adicionaisSelecionados = collect();


        return view('aluguel.create', compact(
            'clientes',
            'espacos',
            'formasPagamento',
            'adicionais',
            'cardapios',
            'adicionaisSelecionados'
        ));
    }

    public function store(Request $request)
    {
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
                'numero_pessoas_buffet' => 'nullable|integer|min:1',
                'cardapio_id' => 'nullable|exists:cardapios,id',
                'tipo' => 'string|required',
                // Campos do buffet vindos do JavaScript
                'buffet_categorias_escolhidas' => 'nullable|string',
                'buffet_opcao_escolhida' => 'nullable|integer',
                // Campos de pagamento
                'pagamentos_json' => 'nullable|string',
            ]);

            $validated['empresa_id'] = Auth::user()->empresa_id;

            // Usar transação para garantir consistência
            DB::beginTransaction();

            try {
                // Criação do aluguel
                $aluguel = Aluguel::create($validated);

                // Relacionar itens adicionais
                $this->salvarAdicionais($aluguel, $request);


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

                    if (str_contains($forma, 'crediário') && $request->filled('parcelas')) {
                        $this->criarContasAReceber(
                            $aluguel,
                            $pagamento->valor,
                            $pagamento->forma_pagamento_id,
                            $request->parcelas
                        );
                    }
                }


                // ✅ Criar fluxo de caixa com os pagamentos
                $this->salvarFluxosDePagamento($aluguel);

                DB::commit();

                return redirect()->route('aluguel.create')->with('success', 'Aluguel criado com sucesso!');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar Aluguel: ' . $e->getMessage());
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

        return view('aluguel.create', compact(
            'aluguel',
            'espacos',
            'formasPagamento',
            'clientes',
            'cardapios',
            'adicionais',
            'itensSelecionados',
            'opcaoSelecionada',
            'adicionaisSelecionados'
        ));
    }



    public function update(Request $request, Aluguel $aluguel)
    {
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
                'numero_pessoas_buffet' => 'nullable|integer|min:1',
                'cardapio_id' => 'nullable|exists:cardapios,id',
                // Campos do buffet vindos do JavaScript
                'buffet_categorias_escolhidas' => 'nullable|string',
                'buffet_opcao_escolhida' => 'nullable|integer',
                // Campos de pagamento
                'pagamentos_json' => 'nullable|string',
            ]);

            $validated['empresa_id'] = Auth::user()->empresa_id;

            // Usar transação para garantir consistência
            DB::beginTransaction();

            try {
                $aluguel->update($validated);

                // Atualizar itens adicionais
                $this->salvarAdicionais($aluguel, $request);

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
            return redirect()->back()->with('error', 'Erro ao atualizar Aluguel: ' . $e->getMessage());
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

            $aluguel->delete();

            DB::commit();

            return redirect()->route('aluguel.index')->with('success', 'Aluguel excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erro ao deletar Aluguel: ' . $e->getMessage());
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
                'opcoes.categorias.itens'
            ])->findOrFail($cardapioId);

            return response()->json([
                'secoes' => $cardapio->secoes,
                'opcoes' => $cardapio->opcoes
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cardápio não encontrado'], 404);
        }
    }

    private function calcularValorAluguel($data_inicio, $data_fim, $valor_semana, $valor_fim, $capela, $tipo_evento)
    {
        $inicio = \Carbon\Carbon::parse($data_inicio);
        $fim = \Carbon\Carbon::parse($data_fim);
        $periodo = \Carbon\CarbonPeriod::create($inicio, $fim);
        $total = 0;

        if ($capela) {
            if ($tipo_evento == 'casamento') {
                foreach ($periodo as $data) {
                    $total += $valor_fim;
                }
            } else  if ($tipo_evento == 'batizado') {
                foreach ($periodo as $data) {
                    $total += $valor_semana;
                }
            }
        } else {
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

    private function salvarFluxosDePagamento(Aluguel $aluguel)
    {
        $empresaId = Auth::user()->empresa_id;

        // Busca o caixa aberto da empresa no dia
        $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
            ->where('status', 'aberto')
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', Auth::id())
            ->first();

        if (!$caixa) {
            session()->flash('error', 'Nenhum caixa aberto encontrado para registrar movimentações.');
            return;
        }

        foreach ($aluguel->pagamentos as $pagamento) {
            $formaPagamento = $pagamento->formaPagamento;

            // ignora se for crediário
            $descricaoForma = strtolower($formaPagamento->descricao ?? '');
            if (str_contains($descricaoForma, 'crediário')) {
                continue;
            }

            $slug = strtolower(str_replace(' ', '-', $formaPagamento->slug ?? $formaPagamento->descricao ?? ''));
            $tipoMov = 'venda-' . $slug;

            $movimentoId = Movimento::where('descricao', $tipoMov)->value('id');

            if (!$movimentoId) {
                continue; // pula se não encontrar o movimento
            }

            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => 'Aluguel #' . $aluguel->id,
                'valor' => $pagamento->valor,
                'valor_total' => $pagamento->valor,
                'tipo' => 'entrada',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => 1, // ou o plano de conta padrão
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
                'plano_de_contas_id' => 1, // ou qualquer plano default
                'venda_id' => null,
            ]);

            $vencimento = \App\Services\ContasService::proximoMes($vencimento);
        }
    }
}

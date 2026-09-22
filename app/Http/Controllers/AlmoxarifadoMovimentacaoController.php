<?php

namespace App\Http\Controllers;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use App\Models\AlmoxarifadoMovimentacao;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AlmoxarifadoMovimentacaoController extends Controller
{
    /**
     * Retorna o ID da empresa ativa na sessão ou do usuário logado.
     */
    private function getEmpresaId(): int
    {
        return (int) (session('empresa_id') ?: Auth::user()?->empresa_id ?: 1);
    }

    /**
     * Lista as movimentações do almoxarifado com filtros.
     */
    public function index(Request $request): View|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        $busca = trim((string) $request->input('busca', ''));
        $itemId = $request->input('item_id');
        $tipo = $request->input('tipo');
        $setor = trim((string) $request->input('setor', ''));
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        $query = AlmoxarifadoMovimentacao::query()
            ->where('empresa_id', $empresaId)
            ->with(['item.categoria', 'usuario'])
            ->when($busca !== '', function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->whereHas('item', function ($itemQuery) use ($busca) {
                        $itemQuery->where('nome', 'like', "%{$busca}%");
                    })
                    ->orWhere('fornecedor', 'like', "%{$busca}%")
                    ->orWhere('recebido_por', 'like', "%{$busca}%")
                    ->orWhere('retirado_por', 'like', "%{$busca}%")
                    ->orWhere('setor', 'like', "%{$busca}%")
                    ->orWhere('numero_documento', 'like', "%{$busca}%")
                    ->orWhere('observacao', 'like', "%{$busca}%");
                });
            })
            ->when(!empty($itemId), function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->when(!empty($tipo) && in_array($tipo, ['entrada', 'saida', 'ajuste']), function ($q) use ($tipo) {
                $q->where('tipo', $tipo);
            })
            ->when($setor !== '', function ($q) use ($setor) {
                $q->where('setor', 'like', "%{$setor}%");
            })
            ->when(!empty($dataInicio), function ($q) use ($dataInicio) {
                $q->whereDate('data_movimentacao', '>=', $dataInicio);
            })
            ->when(!empty($dataFim), function ($q) use ($dataFim) {
                $q->whereDate('data_movimentacao', '<=', $dataFim);
            })
            ->orderByDesc('data_movimentacao')
            ->orderByDesc('id');

        if ($request->wantsJson()) {
            return response()->json($query->get());
        }

        $movimentacoes = $query->paginate(25)->withQueryString();

        // Totais para cards informativos
        $totaisQuery = AlmoxarifadoMovimentacao::where('empresa_id', $empresaId)
            ->when(!empty($dataInicio), fn ($q) => $q->whereDate('data_movimentacao', '>=', $dataInicio))
            ->when(!empty($dataFim), fn ($q) => $q->whereDate('data_movimentacao', '<=', $dataFim));

        $totalEntradas = (clone $totaisQuery)->entradas()->count();
        $totalSaidas = (clone $totaisQuery)->saidas()->count();
        $totalAjustes = (clone $totaisQuery)->ajustes()->count();

        // Lista de itens ativos para seleção nos filtros e modais
        $itens = AlmoxarifadoItem::where('empresa_id', $empresaId)
            ->ativos()
            ->orderBy('nome')
            ->get();

        $categorias = AlmoxarifadoCategoria::ativos()->orderBy('nome')->get();

        // Lista de setores já cadastrados para autocompletar
        $setoresExistentes = AlmoxarifadoMovimentacao::where('empresa_id', $empresaId)
            ->whereNotNull('setor')
            ->where('setor', '!=', '')
            ->distinct()
            ->pluck('setor')
            ->sort()
            ->values();

        return view('almoxarifado.movimentacoes.index', compact(
            'movimentacoes',
            'itens',
            'categorias',
            'setoresExistentes',
            'busca',
            'itemId',
            'tipo',
            'setor',
            'dataInicio',
            'dataFim',
            'totalEntradas',
            'totalSaidas',
            'totalAjustes'
        ));
    }

    /**
     * Registra uma nova movimentação física (entrada, saída ou ajuste).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        $validated = $request->validate([
            'item_id' => [
                'required',
                'integer',
                Rule::exists('almoxarifado_itens', 'id')->where('empresa_id', $empresaId),
            ],
            'tipo' => [
                'required',
                'string',
                Rule::in(['entrada', 'saida', 'ajuste']),
            ],
            'quantidade' => [
                'nullable',
                'numeric',
                'min:0.001',
            ],
            'novo_estoque' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'tipo_ajuste' => [
                'nullable',
                'string',
                Rule::in(['entrada', 'saida', 'acrescimo', 'reducao', 'balanco']),
            ],
            'data_movimentacao' => [
                'nullable',
                'date',
            ],
            'fornecedor' => [
                'nullable',
                'string',
                'max:255',
            ],
            'recebido_por' => [
                'nullable',
                'string',
                'max:255',
            ],
            'numero_documento' => [
                'nullable',
                'string',
                'max:100',
            ],
            'retirado_por' => [
                'nullable',
                'string',
                'max:255',
            ],
            'setor' => [
                'nullable',
                'string',
                'max:255',
            ],
            'motivo_ajuste' => [
                'nullable',
                'string',
                'max:255',
            ],
            'observacao' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'item_id.required'         => 'Selecione o item do almoxarifado.',
            'item_id.exists'           => 'Item não encontrado nesta empresa.',
            'tipo.required'            => 'Informe o tipo de movimentação.',
            'tipo.in'                  => 'Tipo de movimentação inválido.',
            'quantidade.min'           => 'A quantidade deve ser maior que zero.',
            'novo_estoque.min'         => 'O novo saldo de estoque não pode ser negativo.',
            'data_movimentacao.date'   => 'Data de movimentação inválida.',
        ]);

        $tipo = $validated['tipo'];
        $dataMovimentacao = !empty($validated['data_movimentacao'])
            ? Carbon::parse($validated['data_movimentacao'])
            : now();
        $userId = Auth::id();

        $movimentacao = DB::transaction(function () use ($empresaId, $validated, $tipo, $dataMovimentacao, $userId, $request) {
            /** @var AlmoxarifadoItem $item */
            $item = AlmoxarifadoItem::where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->findOrFail($validated['item_id']);

            $saldoAnterior = (float) $item->estoque_atual;
            $saldoPosterior = $saldoAnterior;
            $quantidade = (float) ($validated['quantidade'] ?? 0);

            if ($tipo === 'entrada') {
                if ($quantidade <= 0) {
                    throw ValidationException::withMessages([
                        'quantidade' => 'Informe uma quantidade válida para a entrada.',
                    ]);
                }
                $saldoPosterior = round($saldoAnterior + $quantidade, 2);
            } elseif ($tipo === 'saida') {
                if ($quantidade <= 0) {
                    throw ValidationException::withMessages([
                        'quantidade' => 'Informe uma quantidade válida para a saída.',
                    ]);
                }
                if ($saldoAnterior < $quantidade) {
                    throw ValidationException::withMessages([
                        'quantidade' => "Estoque insuficiente para esta saída. Saldo disponível: {$item->estoque_formatado} {$item->unidade_medida}.",
                    ]);
                }
                $saldoPosterior = round($saldoAnterior - $quantidade, 2);
            } elseif ($tipo === 'ajuste') {
                // Modo 1: Informando o novo saldo apurado na contagem física
                if ($request->filled('novo_estoque')) {
                    $novoEstoque = round((float) $request->input('novo_estoque'), 2);
                    $quantidade = abs(round($novoEstoque - $saldoAnterior, 2));
                    $saldoPosterior = $novoEstoque;
                } else {
                    // Modo 2: Informando a quantidade a ajustar com tipo de ajuste (acréscimo ou redução)
                    if ($quantidade <= 0) {
                        throw ValidationException::withMessages([
                            'quantidade' => 'Informe a quantidade do ajuste ou o novo saldo apurado.',
                        ]);
                    }

                    $tipoAjuste = $request->input('tipo_ajuste', 'reducao');
                    if (in_array($tipoAjuste, ['acrescimo', 'entrada'])) {
                        $saldoPosterior = round($saldoAnterior + $quantidade, 2);
                    } else {
                        if ($saldoAnterior < $quantidade) {
                            throw ValidationException::withMessages([
                                'quantidade' => "O ajuste deixaria o estoque negativo. Saldo disponível: {$item->estoque_formatado} {$item->unidade_medida}.",
                            ]);
                        }
                        $saldoPosterior = round($saldoAnterior - $quantidade, 2);
                    }
                }
            }

            // Atualiza o saldo físico no cadastro do item
            $item->update(['estoque_atual' => $saldoPosterior]);

            // Cria o registro auditável no histórico de movimentações
            return AlmoxarifadoMovimentacao::create([
                'empresa_id'        => $empresaId,
                'item_id'           => $item->id,
                'tipo'              => $tipo,
                'quantidade'        => $quantidade,
                'saldo_anterior'    => $saldoAnterior,
                'saldo_posterior'   => $saldoPosterior,
                'data_movimentacao' => $dataMovimentacao,
                'user_id'           => $userId,
                'fornecedor'        => $validated['fornecedor'] ?? null,
                'recebido_por'      => $validated['recebido_por'] ?? null,
                'numero_documento'  => $validated['numero_documento'] ?? null,
                'retirado_por'      => $validated['retirado_por'] ?? null,
                'setor'             => $validated['setor'] ?? null,
                'motivo_ajuste'     => $validated['motivo_ajuste'] ?? null,
                'observacao'        => $validated['observacao'] ?? null,
            ]);
        });

        $mensagens = [
            'entrada' => 'Entrada de estoque registrada com sucesso!',
            'saida'   => 'Saída de estoque registrada com sucesso!',
            'ajuste'  => 'Ajuste de estoque concluído com sucesso!',
        ];

        $mensagemSucesso = $mensagens[$tipo] ?? 'Movimentação registrada com sucesso!';

        if ($request->wantsJson()) {
            return response()->json([
                'message'      => $mensagemSucesso,
                'movimentacao' => $movimentacao->load(['item.categoria', 'usuario']),
            ], 201);
        }

        return redirect()
            ->route('almoxarifado.movimentacoes.index')
            ->with('success', $mensagemSucesso);
    }

    /**
     * Exibe os detalhes de uma movimentação específica.
     */
    public function show(Request $request, AlmoxarifadoMovimentacao $movimentacao): View|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($movimentacao->empresa_id !== $empresaId) {
            abort(403, 'Acesso não autorizado para esta movimentação.');
        }

        $movimentacao->load(['item.categoria', 'usuario']);

        if ($request->wantsJson()) {
            return response()->json($movimentacao);
        }

        return view('almoxarifado.movimentacoes.show', compact('movimentacao'));
    }

    /**
     * Cancela/estorna uma movimentação de estoque de forma segura, recalculando o saldo físico.
     */
    public function destroy(Request $request, AlmoxarifadoMovimentacao $movimentacao): RedirectResponse|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($movimentacao->empresa_id !== $empresaId) {
            abort(403, 'Acesso não autorizado para esta movimentação.');
        }

        DB::transaction(function () use ($movimentacao, $empresaId) {
            /** @var AlmoxarifadoItem $item */
            $item = AlmoxarifadoItem::where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->findOrFail($movimentacao->item_id);

            $saldoAtual = (float) $item->estoque_atual;
            $qtd = (float) $movimentacao->quantidade;

            if ($movimentacao->tipo === 'entrada') {
                // Estornar entrada significa retirar o que entrou
                if ($saldoAtual < $qtd) {
                    throw ValidationException::withMessages([
                        'error' => "Não é possível estornar esta entrada pois parte dos itens já foi consumida. Saldo atual: {$item->estoque_formatado} {$item->unidade_medida}.",
                    ]);
                }
                $item->update(['estoque_atual' => round($saldoAtual - $qtd, 2)]);
            } elseif ($movimentacao->tipo === 'saida') {
                // Estornar saída significa devolver ao estoque o que saiu
                $item->update(['estoque_atual' => round($saldoAtual + $qtd, 2)]);
            } elseif ($movimentacao->tipo === 'ajuste') {
                // Estornar ajuste significa restaurar o saldo anterior ao ajuste
                $item->update(['estoque_atual' => (float) $movimentacao->saldo_anterior]);
            }

            $movimentacao->delete();
        });

        $msg = 'Movimentação estornada com sucesso! O saldo de estoque foi restaurado.';

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg]);
        }

        return redirect()
            ->route('almoxarifado.movimentacoes.index')
            ->with('success', $msg);
    }

    /**
     * Retorna o saldo físico e unidade de um item específico (útil para consultas AJAX nos formulários).
     */
    public function saldo(AlmoxarifadoItem $item): JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($item->empresa_id !== $empresaId) {
            return response()->json(['error' => 'Item não encontrado.'], 404);
        }

        return response()->json([
            'id'                => $item->id,
            'nome'              => $item->nome,
            'unidade_medida'    => $item->unidade_medida,
            'estoque_atual'     => (float) $item->estoque_atual,
            'estoque_formatado' => $item->estoque_formatado,
            'estoque_minimo'    => (float) $item->estoque_minimo,
            'is_estoque_baixo'  => $item->is_estoque_baixo,
        ]);
    }

    /**
     * Retorna o histórico de movimentações (Kardex) de um item específico em JSON.
     */
    public function historicoItem(Request $request, AlmoxarifadoItem $item): JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($item->empresa_id !== $empresaId) {
            return response()->json(['error' => 'Item não encontrado.'], 404);
        }

        $movimentacoes = AlmoxarifadoMovimentacao::where('empresa_id', $empresaId)
            ->where('item_id', $item->id)
            ->with('usuario')
            ->orderByDesc('data_movimentacao')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'item'          => $item,
            'movimentacoes' => $movimentacoes,
        ]);
    }
}

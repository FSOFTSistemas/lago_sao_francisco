<?php

namespace App\Http\Controllers;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlmoxarifadoItemController extends Controller
{
    /**
     * Retorna o ID da empresa ativa na sessão ou do usuário logado.
     */
    private function getEmpresaId(): int
    {
        return (int) (session('empresa_id') ?: Auth::user()?->empresa_id ?: 1);
    }

    /**
     * Lista os itens de almoxarifado da empresa com filtros.
     */
    public function index(Request $request): View|JsonResponse
    {
        $empresaId = $this->getEmpresaId();
        $busca = trim((string) $request->input('busca', ''));
        $categoriaId = $request->input('categoria_id');
        $status = $request->input('status');
        $estoqueBaixo = $request->boolean('estoque_baixo');

        $itens = AlmoxarifadoItem::query()
            ->where('empresa_id', $empresaId)
            ->with('categoria')
            ->withCount('movimentacoes')
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%");
            })
            ->when(!empty($categoriaId), function ($query) use ($categoriaId) {
                $query->where('categoria_id', $categoriaId);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('ativo', (bool) $status);
            })
            ->when($estoqueBaixo, function ($query) {
                $query->estoqueBaixo();
            })
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($itens);
        }

        $categorias = AlmoxarifadoCategoria::ativos()->orderBy('nome')->get();

        return view('almoxarifado.itens.index', compact(
            'itens',
            'categorias',
            'busca',
            'categoriaId',
            'status',
            'estoqueBaixo'
        ));
    }

    /**
     * Cadastra um novo item no almoxarifado da empresa.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('almoxarifado_itens', 'nome')->where('empresa_id', $empresaId),
            ],
            'categoria_id' => [
                'required',
                'integer',
                'exists:almoxarifado_categorias,id',
            ],
            'unidade_medida' => [
                'required',
                'string',
                'max:20',
            ],
            'estoque_atual' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'estoque_minimo' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'ativo' => [
                'nullable',
                'boolean',
            ],
        ], [
            'nome.required' => 'O nome do item é obrigatório.',
            'nome.min' => 'O nome do item deve ter pelo menos 2 caracteres.',
            'nome.max' => 'O nome do item não pode ultrapassar 255 caracteres.',
            'nome.unique' => 'Já existe um item cadastrado com esse nome nesta empresa.',
            'categoria_id.required' => 'Selecione a categoria do item.',
            'categoria_id.exists' => 'A categoria selecionada é inválida.',
            'unidade_medida.required' => 'Informe a unidade de medida (ex: UN, CX, PCT, L, KG).',
            'unidade_medida.max' => 'A unidade de medida não pode ultrapassar 20 caracteres.',
            'estoque_atual.numeric' => 'O saldo inicial de estoque deve ser um número válido.',
            'estoque_atual.min' => 'O saldo inicial não pode ser negativo.',
            'estoque_minimo.numeric' => 'O estoque mínimo deve ser um número válido.',
            'estoque_minimo.min' => 'O estoque mínimo não pode ser negativo.',
        ]);

        $item = AlmoxarifadoItem::create([
            'empresa_id'     => $empresaId,
            'categoria_id'   => $validated['categoria_id'],
            'nome'           => $validated['nome'],
            'unidade_medida' => strtoupper(trim($validated['unidade_medida'])),
            'estoque_atual'  => $validated['estoque_atual'] ?? 0,
            'estoque_minimo' => $validated['estoque_minimo'] ?? 0,
            'ativo'          => $request->boolean('ativo', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Item cadastrado com sucesso!',
                'item'    => $item->load('categoria'),
            ], 201);
        }

        return redirect()
            ->route('almoxarifado.itens.index')
            ->with('success', 'Item cadastrado com sucesso!');
    }

    /**
     * Retorna os detalhes de um item do almoxarifado.
     */
    public function show(Request $request, AlmoxarifadoItem $item): View|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($item->empresa_id !== $empresaId) {
            abort(403, 'Acesso negado para o item desta empresa.');
        }

        $item->load(['categoria', 'movimentacoes.usuario']);

        if ($request->wantsJson()) {
            return response()->json($item);
        }

        return view('almoxarifado.itens.show', compact('item'));
    }

    /**
     * Atualiza os dados cadastrais do item (sem alterar o saldo de estoque diretamente).
     */
    public function update(Request $request, AlmoxarifadoItem $item): RedirectResponse|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($item->empresa_id !== $empresaId) {
            abort(403, 'Acesso negado para o item desta empresa.');
        }

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('almoxarifado_itens', 'nome')
                    ->where('empresa_id', $empresaId)
                    ->ignore($item->id),
            ],
            'categoria_id' => [
                'required',
                'integer',
                'exists:almoxarifado_categorias,id',
            ],
            'unidade_medida' => [
                'required',
                'string',
                'max:20',
            ],
            'estoque_minimo' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'ativo' => [
                'nullable',
                'boolean',
            ],
        ], [
            'nome.required' => 'O nome do item é obrigatório.',
            'nome.min' => 'O nome do item deve ter pelo menos 2 caracteres.',
            'nome.max' => 'O nome do item não pode ultrapassar 255 caracteres.',
            'nome.unique' => 'Já existe um item cadastrado com esse nome nesta empresa.',
            'categoria_id.required' => 'Selecione a categoria do item.',
            'categoria_id.exists' => 'A categoria selecionada é inválida.',
            'unidade_medida.required' => 'Informe a unidade de medida.',
            'estoque_minimo.numeric' => 'O estoque mínimo deve ser um número válido.',
            'estoque_minimo.min' => 'O estoque mínimo não pode ser negativo.',
        ]);

        $item->update([
            'categoria_id'   => $validated['categoria_id'],
            'nome'           => $validated['nome'],
            'unidade_medida' => strtoupper(trim($validated['unidade_medida'])),
            'estoque_minimo' => $validated['estoque_minimo'] ?? 0,
            'ativo'          => $request->boolean('ativo'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Item atualizado com sucesso!',
                'item'    => $item->fresh()->load('categoria'),
            ]);
        }

        return redirect()
            ->route('almoxarifado.itens.index')
            ->with('success', 'Item atualizado com sucesso!');
    }

    /**
     * Exclui o item apenas se não houver movimentações vinculadas.
     */
    public function destroy(Request $request, AlmoxarifadoItem $item): RedirectResponse|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if ($item->empresa_id !== $empresaId) {
            abort(403, 'Acesso negado para o item desta empresa.');
        }

        if ($item->movimentacoes()->exists()) {
            $mensagem = 'Este item possui movimentações no histórico do estoque. Para preservar o histórico, inative-o em vez de excluí-lo.';

            if ($request->wantsJson()) {
                return response()->json(['error' => $mensagem], 422);
            }

            return redirect()
                ->route('almoxarifado.itens.index')
                ->with('error', $mensagem);
        }

        $item->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Item excluído com sucesso!']);
        }

        return redirect()
            ->route('almoxarifado.itens.index')
            ->with('success', 'Item excluído com sucesso!');
    }

    /**
     * Endpoint de busca rápida de itens ativos para seleção (autocomplete).
     */
    public function search(Request $request): JsonResponse
    {
        $empresaId = $this->getEmpresaId();
        $termo = trim((string) $request->input('q', ''));

        $itens = AlmoxarifadoItem::query()
            ->where('empresa_id', $empresaId)
            ->ativos()
            ->when($termo !== '', function ($query) use ($termo) {
                $query->where('nome', 'like', "%{$termo}%");
            })
            ->with('categoria:id,nome')
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'empresa_id', 'categoria_id', 'nome', 'unidade_medida', 'estoque_atual', 'estoque_minimo']);

        return response()->json($itens);
    }
}

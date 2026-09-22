<?php

namespace App\Http\Controllers;

use App\Models\AlmoxarifadoCategoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlmoxarifadoCategoriaController extends Controller
{
    /**
     * Exibe a listagem de categorias do almoxarifado.
     */
    public function index(Request $request): View
    {
        $busca = trim((string) $request->input('busca', ''));

        $categorias = AlmoxarifadoCategoria::query()
            ->withCount('itens')
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%");
            })
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        return view('almoxarifado.categorias.index', compact('categorias', 'busca'));
    }

    /**
     * Salva uma nova categoria no banco de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('almoxarifado_categorias', 'nome'),
            ],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'O nome da categoria é obrigatório.',
            'nome.min'      => 'O nome da categoria deve ter pelo menos 2 caracteres.',
            'nome.max'      => 'O nome da categoria não pode ultrapassar 255 caracteres.',
            'nome.unique'   => 'Já existe uma categoria cadastrada com esse nome.',
        ]);

        $validated['ativo'] = $request->boolean('ativo', true);

        AlmoxarifadoCategoria::create($validated);

        return redirect()
            ->route('almoxarifado.categorias.index')
            ->with('success', 'Categoria cadastrada com sucesso!');
    }

    /**
     * Atualiza os dados da categoria especificada.
     */
    public function update(Request $request, AlmoxarifadoCategoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('almoxarifado_categorias', 'nome')->ignore($categoria->id),
            ],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'O nome da categoria é obrigatório.',
            'nome.min'      => 'O nome da categoria deve ter pelo menos 2 caracteres.',
            'nome.max'      => 'O nome da categoria não pode ultrapassar 255 caracteres.',
            'nome.unique'   => 'Já existe uma categoria cadastrada com esse nome.',
        ]);

        $validated['ativo'] = $request->boolean('ativo');

        $categoria->update($validated);

        return redirect()
            ->route('almoxarifado.categorias.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Exclui a categoria do banco de dados, protegendo contra exclusão com itens vinculados.
     */
    public function destroy(AlmoxarifadoCategoria $categoria): RedirectResponse
    {
        if ($categoria->itens()->exists()) {
            return redirect()
                ->route('almoxarifado.categorias.index')
                ->with('error', 'Esta categoria possui itens vinculados no estoque do almoxarifado. Inative-a ou altere os itens antes de excluí-la.');
        }

        $categoria->delete();

        return redirect()
            ->route('almoxarifado.categorias.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use App\Models\Movimento;
use Illuminate\Http\Request;

class FormaPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formaPagamento = FormaPagamento::all();

        return view('preferencias.formaPagamento', compact('formaPagamento'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->slugJaUtilizado($this->slug($value))) {
                        $fail('Já existe uma forma de pagamento equivalente a "'.$value.'" (mesmo nome ignorando espaços/maiúsculas). Escolha uma descrição diferente para não misturar os relatórios de caixa.');
                    }
                },
            ],
        ]);

        $formaPagamento = FormaPagamento::create($validated);

        $this->sincronizarMovimentos($this->slug($formaPagamento->descricao));

        return redirect()->route('formaPagamento.index')->with('success', 'Registro criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $formaPagamento = FormaPagamento::findOrFail($id);

        $validated = $request->validate([
            'descricao' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($formaPagamento) {
                    if ($this->slugJaUtilizado($this->slug($value), excetoId: $formaPagamento->id)) {
                        $fail('Já existe uma forma de pagamento equivalente a "'.$value.'" (mesmo nome ignorando espaços/maiúsculas). Escolha uma descrição diferente para não misturar os relatórios de caixa.');
                    }
                },
            ],
        ]);

        $formaPagamento->update($validated);

        $this->sincronizarMovimentos($this->slug($formaPagamento->descricao));

        return redirect()->route('formaPagamento.index')->with('success', 'Registro atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $formaPagamento = FormaPagamento::find($id);
            $formaPagamento->delete();

            return redirect()->route('formaPagamento.index')->with('success', 'Registro excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir: '.$e->getMessage());
        }
    }

    /**
     * Gera o mesmo slug usado em TransacaoController/AluguelController/DayUse* para
     * casar a forma de pagamento com os Movimentos de caixa (venda-, recebimento-, cancelamento-).
     */
    private function slug(string $descricao): string
    {
        return strtolower(str_replace([' ', '_'], '-', trim($descricao)));
    }

    private function slugJaUtilizado(string $slug, ?int $excetoId = null): bool
    {
        return FormaPagamento::when($excetoId, fn ($q) => $q->where('id', '!=', $excetoId))
            ->get()
            ->contains(fn ($forma) => $this->slug($forma->descricao) === $slug);
    }

    /**
     * Garante que existam os Movimentos correspondentes à forma de pagamento, para que
     * ela seja automaticamente refletida no fluxo/resumo de caixa assim que for usada
     * (sem depender de alguém lembrar de cadastrar o Movimento manualmente).
     */
    private function sincronizarMovimentos(string $slug): void
    {
        foreach (['venda', 'recebimento', 'cancelamento'] as $prefixo) {
            Movimento::firstOrCreate(['descricao' => "{$prefixo}-{$slug}"]);
        }
    }
}

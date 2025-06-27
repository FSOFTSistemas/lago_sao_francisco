<?php

namespace App\Http\Controllers;

use App\Models\ContasAPagar;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContasAPagarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $planoDeContas = PlanoDeConta::all();
        $empresas = Empresa::all();
        $contasAPagar = ContasAPagar::all();
        $fornecedores = Fornecedor::all();
        return view('contasAPagar.index', compact('contasAPagar', 'planoDeContas', 'empresas', 'fornecedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'descricao' => 'required|string|max:255',
                'valor' => 'required|numeric|min:0.01',
                'valor_pago' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value > $request->valor) {
                            $fail('O valor pago não pode ser maior que o valor da conta.');
                        }
                    }
                ],
                'data_vencimento' => [
                    'required',
                    'date',
                    'after_or_equal:today' // ou 'after:yesterday' dependendo do requisito
                ],
                'data_pagamento' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && $request->data_vencimento && $value < $request->data_vencimento) {
                            $fail('A data de pagamento não pode ser anterior à data de vencimento.');
                        }
                    }
                ],
                'status' => 'required|in:pendente,finalizado',
                'plano_de_contas_id' => [
                    'nullable',
                    'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        // Verifica se o plano de contas pertence à mesma empresa do usuário
                        if ($value && !PlanoDeConta::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)
                            ->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    }
                ],
                'fornecedor_id' => [
                    'nullable',
                    'exists:fornecedors,id',
                    function ($attribute, $value, $fail) {
                        // Verifica se o fornecedor pertence à mesma empresa do usuário
                        if ($value && !Fornecedor::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)
                            ->exists()) {
                            $fail('O fornecedor selecionado não pertence à sua empresa.');
                        }
                    }
                ],
            ], [
                'descricao.required' => 'A descrição da conta é obrigatória.',
                'valor.required' => 'O valor da conta é obrigatório.',
                'valor.min' => 'O valor da conta deve ser pelo menos R$ 0,01.',
                'data_vencimento.required' => 'A data de vencimento é obrigatória.',
                'data_vencimento.after_or_equal' => 'A data de vencimento não pode ser no passado.',
                'status.required' => 'O status da conta é obrigatório.',
            ]);

            $validatedData['empresa_id'] = Auth::user()->empresa_id;
            
            ContasAPagar::create($validatedData);
            
            return redirect()
                ->route('contasAPagar.index')
                ->with('success', 'Conta a pagar cadastrada com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', 'Erro ao cadastrar conta a pagar: ' . $e->getMessage());
        }
    }
    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, ContasAPagar $contasAPagar)
    {
        try {
            $validatedData = $request->validate([
                'descricao' => 'required|string|max:255',
                'valor' => [
                    'required',
                    'numeric',
                    'min:0.01',
                    function ($attribute, $value, $fail) use ($contasAPagar) {
                        // Impede a redução do valor se já houver pagamento
                        if ($contasAPagar->valor_pago > 0 && $value < $contasAPagar->valor) {
                            $fail('Não é possível reduzir o valor quando já existe um pagamento registrado.');
                        }
                    }
                ],
                'valor_pago' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request, $contasAPagar) {
                        $value = $value ?? 0;
                        // Validação cruzada com o valor total
                        if ($value > $request->valor) {
                            $fail('O valor pago não pode ser maior que o valor da conta.');
                        }
                        // Validação para status finalizado
                        if ($request->status == 'finalizado' && $value < $request->valor) {
                            $fail('Para finalizar a conta, o valor pago deve ser igual ao valor total.');
                        }
                    }
                ],
                'data_vencimento' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($contasAPagar) {
                        // Não permite alterar data de vencimento se já foi pago
                        if ($contasAPagar->valor_pago > 0 && $value != $contasAPagar->data_vencimento) {
                            $fail('Não é possível alterar a data de vencimento de uma conta já paga.');
                        }
                    }
                ],
                'data_pagamento' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && $request->data_vencimento && $value < $request->data_vencimento) {
                            $fail('A data de pagamento não pode ser anterior à data de vencimento.');
                        }
                    }
                ],
                'status' => [
                    'required',
                    'in:pendente,finalizado',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value == 'finalizado' && (empty($request->valor_pago) || $request->valor_pago < $request->valor)) {
                            $fail('Para finalizar a conta, o valor pago deve ser igual ao valor total.');
                        }
                    }
                ],
                'plano_de_contas_id' => [
                    'nullable',
                    'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !PlanoDeConta::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)
                            ->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    }
                ],
                'fornecedor_id' => [
                    'nullable',
                    'exists:fornecedors,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !Fornecedor::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)
                            ->exists()) {
                            $fail('O fornecedor selecionado não pertence à sua empresa.');
                        }
                    }
                ],
            ], [
                'descricao.required' => 'A descrição da conta é obrigatória.',
                'valor.required' => 'O valor da conta é obrigatório.',
                'valor.min' => 'O valor da conta deve ser pelo menos R$ 0,01.',
                'data_vencimento.required' => 'A data de vencimento é obrigatória.',
                'status.required' => 'O status da conta é obrigatório.',
                'status.in' => 'O status deve ser "pendente" ou "finalizado".',
            ]);

            // Atualiza os dados
            $contasAPagar->update($validatedData);

            return redirect()
                ->route('contasAPagar.index')
                ->with('success', 'Conta a pagar atualizada com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', 'Erro ao atualizar conta a pagar: ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContasAPagar $contasAPagar)
    {
        $contasAPagar = ContasAPagar::findOrFail($contasAPagar->id);
        $contasAPagar->delete();
        return redirect()->route('contasAPagar.index')->with('success', 'Conta a pagar excluída com sucesso!');
    }
}

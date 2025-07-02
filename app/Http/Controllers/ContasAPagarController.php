<?php

namespace App\Http\Controllers;

use App\Models\ContasAPagar;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\ParcelaContasAPagar;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContasAPagarController extends Controller
{
 public function index(Request $request)
{
    $planoDeContas = PlanoDeConta::all();
    $empresas = Empresa::all();
    $fornecedores = Fornecedor::all();

    $query = ContasAPagar::where('empresa_id', Auth::user()->empresa_id);

    // Aplica o filtro por status (na conta principal)
    if ($request->filled('status')) {
        $query->where('status', $request->input('status'));
    }

    if ($request->filled('fornecedor_id')) {
        $query->where('fornecedor_id', $request->input('fornecedor_id'));
    }

    // Obtem contas com parcelas carregadas
    $contasAPagar = $query->with('parcelas')->get();

    $contasComParcelas = [];

    // Pega intervalo de datas do filtro ou padrão para o mês atual
    $inicio = $request->input('data_inicio') ?? Carbon::now()->startOfYear()->toDateString();
    $fim = $request->input('data_fim') ?? Carbon::now()->endOfYear()->toDateString();

    foreach ($contasAPagar as $conta) {
        if ($conta->parcelas->isEmpty()) {
            // Conta sem parcelamento → filtra pelo vencimento da conta
            if (
                $conta->data_vencimento >= $inicio &&
                $conta->data_vencimento <= $fim
            ) {
                $contasComParcelas[] = $conta;
            }
        } else {
            // Conta com parcelas → adiciona somente as parcelas no período
            foreach ($conta->parcelas as $parcela) {
                if (
                    $parcela->data_vencimento >= $inicio &&
                    $parcela->data_vencimento <= $fim
                ) {
                    $contaClone = clone $conta;
                    $contaClone->descricao .= " - Parcela {$parcela->numero_parcela}/{$conta->parcelas->count()}";
                    $contaClone->valor = $parcela->valor;
                    $contaClone->data_vencimento = $parcela->data_vencimento;
                    $contaClone->status = $parcela->status;
                    $contasComParcelas[] = $contaClone;
                }
            }
        }
    }

    return view('contasAPagar.index', compact('contasComParcelas', 'planoDeContas', 'empresas', 'fornecedores'));
}

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'descricao' => 'required|string|max:255',
                'valor' => 'required|numeric|min:0.01',
                'data_vencimento' => 'required|date|after_or_equal:today',
                'status' => 'required|in:pendente,finalizado',
                'plano_de_contas_id' => [
                    'nullable',
                    'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !PlanoDeConta::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    }
                ],
                'fornecedor_id' => [
                    'nullable',
                    'exists:fornecedors,id',
                ],
                'parcelas' => 'nullable|integer|min:1'
            ]);

            $validatedData['empresa_id'] = Auth::user()->empresa_id;

            $conta = ContasAPagar::create($validatedData);

            $numParcelas = $request->input('parcelas', 1);
            $valorParcela = round($conta->valor / $numParcelas, 2);
            $dataBase = Carbon::parse($validatedData['data_vencimento']);
            
            for ($i = 1; $i <= $numParcelas; $i++) {
                ParcelaContasAPagar::create([
                    'contas_a_pagar_id' => $conta->id,
                    'numero_parcela' => $i,
                    'valor' => $valorParcela,
                    'data_vencimento' => $dataBase->copy()->addMonths($i - 1),
                    'status' => 'pendente'
                ]);
            }

            return redirect()
                ->route('contasAPagar.index')
                ->with('success', 'Conta a pagar cadastrada com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', 'Erro ao cadastrar conta: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ContasAPagar $contasAPagar)
    {
        try {
            $validatedData = $request->validate([
                'descricao' => 'required|string|max:255',
                'valor' => [
                    'required', 'numeric', 'min:0.01',
                    function ($attribute, $value, $fail) use ($contasAPagar) {
                        if ($contasAPagar->valor_pago > 0 && $value < $contasAPagar->valor) {
                            $fail('Não é possível reduzir o valor quando já existe um pagamento registrado.');
                        }
                    }
                ],
                'data_vencimento' => 'required|date',
                'status' => [
                    'required', 'in:pendente,finalizado',
                ],
                'plano_de_contas_id' => [
                    'nullable', 'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !PlanoDeConta::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    }
                ],
                'fornecedor_id' => [
                    'nullable', 'exists:fornecedors,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !Fornecedor::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                            $fail('O fornecedor selecionado não pertence à sua empresa.');
                        }
                    }
                ],
            ]);

            $contasAPagar->update($validatedData);

            return redirect()
                ->route('contasAPagar.index')
                ->with('success', 'Conta a pagar atualizada com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->route('contasAPagar.index')
                ->with('error', 'Erro ao atualizar conta: ' . $e->getMessage());
        }
    }

    public function destroy(ContasAPagar $contasAPagar)
    {
        $contasAPagar->delete();
        return redirect()->route('contasAPagar.index')->with('success', 'Conta a pagar excluída com sucesso!');
    }

    public function pagar(Request $request, ContasAPagar $contasAPagar)
{
    $request->validate([
        'data_pagamento' => 'required|date',
        'valor_pago' => 'required|numeric|min:0.01',
        'forma_pagamento' => 'required|string',
    ]);

    $contasAPagar->update([
        'status' => 'Pago',
        'valor_pago' => $request->valor_pago,
        'data_pagamento' => $request->data_pagamento,
    ]);

    return redirect()->route('contasAPagar.index')->with('success', 'Pagamento registrado com sucesso!');
}

}

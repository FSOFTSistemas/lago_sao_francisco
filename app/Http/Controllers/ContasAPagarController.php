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

    // Filtro por mês (YYYY-MM)
    if ($request->has('mes') && $request->mes) {
        try {
            $mes = \Carbon\Carbon::createFromFormat('Y-m', $request->mes);
            $inicioMes = $mes->copy()->startOfMonth()->toDateString();
            $fimMes = $mes->copy()->endOfMonth()->toDateString();

            // Filtra contas cujo vencimento está dentro do mês selecionado
            $query->whereBetween('data_vencimento', [$inicioMes, $fimMes]);
        } catch (\Exception $e) {
            // Se formato inválido, ignora filtro
        }
    } else {
        // Sem filtro, busca todas as contas da empresa
        $query->orderBy('data_vencimento', 'asc');
    }

    $contasAPagar = $query->get();

    // Se houver parcelamento (supondo que você adicionou colunas no modelo para isso),
    // define propriedades auxiliares para a view:
    foreach ($contasAPagar as $conta) {
        // Se você tem campos como 'numero_parcela' e 'total_parcelas' na tabela:
        $conta->numero_parcela = $conta->numero_parcela ?? 1;
        $conta->total_parcelas = $conta->total_parcelas ?? 1;
    }

    return view('contasAPagar.index', compact('contasAPagar', 'planoDeContas', 'empresas', 'fornecedores'));
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
                    function ($attribute, $value, $fail) {
                        if ($value && !Fornecedor::where('id', $value)
                            ->where('empresa_id', Auth::user()->empresa_id)->exists()) {
                            $fail('O fornecedor selecionado não pertence à sua empresa.');
                        }
                    }
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
}

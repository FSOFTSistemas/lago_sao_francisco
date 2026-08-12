<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PlanoDeConta;
use App\services\PlanoDeContasService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanoDeContaController extends Controller
{
    protected $planoDeContasService;

    public function __construct(PlanoDeContasService $planoDeContasService)
    {
        $this->planoDeContasService = $planoDeContasService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $empresas = Empresa::all();
        $planoDeContas = PlanoDeConta::all();
        return view('planoDeConta.index', compact('planoDeContas', 'empresas', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|in:receita,despesa',
                'plano_de_conta_pai' => 'nullable|exists:plano_de_contas,id',

            ]);
            $request['empresa_id'] = Auth::user()->empresa_id;
            PlanoDeConta::create($request->all());
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta criado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|in:receita,despesa',
                'plano_de_conta_pai' => 'nullable|exists:plano_de_contas,id',
            ]);
            $planoDeConta = PlanoDeConta::find($id);
            $planoDeConta->update([
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'plano_de_conta_pai' => $request->plano_de_conta_pai,
            ]);
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $planoDeConta = PlanoDeConta::find($id);
            $planoDeConta->delete();
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta deletado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao deletar Plano de Conta');
        }
    }

    public function relatorio(Request $request)
    {
        try {
            $request->validate([
                'data_inicio' => 'nullable|date',
                'data_fim'    => 'nullable|date|after_or_equal:data_inicio',
            ]);
            
            // Pega as datas do formulário
            $dataInicio = $request->input('data_inicio');
            $dataFim    = $request->input('data_fim');

            // ===================================================================
            // LÓGICA PARA O FILTRO PADRÃO DO MÊS ATUAL
            // ===================================================================
            // Se nenhuma data de início foi enviada, define o padrão
            if (empty($dataInicio)) {
                $hoje = Carbon::now();
                $dataInicio = $hoje->startOfMonth()->format('Y-m-d');
                $dataFim = $hoje->endOfMonth()->format('Y-m-d');
            }
            // ===================================================================

            // Escopo por empresa: Master enxerga tudo se nenhuma empresa estiver selecionada,
            // demais usuários ficam sempre restritos à própria empresa (mesmo padrão usado em
            // FluxoCaixaController e ContasAPagarController::handleCaixaPayment).
            $usuario = Auth::user();
            $empresaSelecionada = session('empresa_id');
            $empresaId = $usuario->hasRole('Master') ? $empresaSelecionada : $usuario->empresa_id;

            // Chama o service para pegar os dados reais do banco, já com o filtro
            $arvoreContas = $this->planoDeContasService->gerarRelatorioHierarquico($empresaId, $dataInicio, $dataFim);

            // Agrupa por tipo todas as contas raiz (sem pai) de despesa e de receita.
            // No plano de contas não existe um único nó guarda-chuva "Despesa"/"Receita":
            // cada categoria (Despesas Administrativas, Despesas Operacionais, Impostos e
            // Taxas, Salários e Benefícios, Receitas Operacionais...) é sua própria raiz. Uma
            // versão anterior buscava só a raiz cujo nome fosse exatamente "despesa"/"receita",
            // o que escondia qualquer outra categoria raiz do mesmo tipo.
            $despesasRaizes = collect($arvoreContas)->filter(
                fn ($node) => $node['model']->tipo === 'despesa'
            )->values();

            $receitasRaizes = collect($arvoreContas)->filter(
                fn ($node) => $node['model']->tipo === 'receita'
            )->values();

            $despesas = [
                'model' => null,
                'filhos' => $despesasRaizes->all(),
                'total_cumulativo' => $despesasRaizes->sum('total_cumulativo'),
            ];

            $receitas = [
                'model' => null,
                'filhos' => $receitasRaizes->all(),
                'total_cumulativo' => $receitasRaizes->sum('total_cumulativo'),
            ];

            // Envia os dados para a view
            return view('relatorios.plano_de_contas', compact('receitas', 'despesas', 'dataInicio', 'dataFim'));
            
        } catch (\Exception $e) {
            dd($e); 
        }
    }
}

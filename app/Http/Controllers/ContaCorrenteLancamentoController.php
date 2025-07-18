<?php

namespace App\Http\Controllers;
use App\Models\ContaCorrente;
use App\Models\ContaCorrenteLancamento;
use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Models\ContasAPagar;
use App\Models\Fornecedor;
use App\Models\PlanoDeConta;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ContaCorrenteLancamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
{
    $usuario = Auth::user();
    $empresaSelecionada = session('empresa_id');

    $empresa_id = $usuario->hasRole('Master') && $empresaSelecionada
        ? $empresaSelecionada
        : $usuario->empresa_id;

    // Datas padrão: mês atual
    $inicio = $request->filled('data_inicio') 
        ? \Carbon\Carbon::parse($request->input('data_inicio'))->startOfDay() 
        : \Carbon\Carbon::now()->startOfMonth();

    $fim = $request->filled('data_fim') 
        ? \Carbon\Carbon::parse($request->input('data_fim'))->endOfDay() 
        : \Carbon\Carbon::now()->endOfMonth();

    // Contas disponíveis
    $contasCorrente = ContaCorrente::orderBy('titular')->get();
    $contaSelecionadaId = $request->input('conta_id') ?? $contasCorrente->first()->id ?? null;

    // Filtros extras
    $status = $request->input('status');
    $tipo = $request->input('tipo');

    // Buscar lançamentos da conta selecionada
    $lancamentos = [];
    if ($contaSelecionadaId) {
        $query = ContaCorrenteLancamento::where('conta_corrente_id', $contaSelecionadaId)
            ->whereBetween('data', [$inicio, $fim]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $lancamentos = $query->orderBy('data', 'desc')->get();
    }

    return view('lancamentos.index', [
        'contasCorrente' => $contasCorrente,
        'lancamentos' => $lancamentos,
        'contaSelecionadaId' => $contaSelecionadaId,
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('conta_corrente_lancamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saida',
                'status' => 'required|in:pendente,concluído',
                'conta_corrente_id' => 'required|exists:bancos,id',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            ContaCorrenteLancamento::create($request->all());
            return redirect()->route('conta_corrente_lancamentos.index')->with('success', 'Lançamento cadastrado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('conta_corrente_lancamentos.index')->with('error', 'Erro ao cadastrar lançamento!'. $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
        return view('conta_corrente_lancamentos.show', compact('contaCorrenteLancamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
        return view('conta_corrente_lancamentos.edit', compact('contaCorrenteLancamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        try {
            $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saida',
                'status' => 'required|in:pendente,concluído',
                'conta_corrente_id' => 'required|exists:bancos,id',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $contaCorrenteLancamento->update($request->all());
            return redirect()->route('conta_corrente_lancamentos.index')->with('success', 'Lançamento atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('conta_corrente_lancamentos.index')->with('error', 'Erro ao atualizar lançamento!'. $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
        $contaCorrenteLancamento->delete();
        return redirect()->route('conta_corrente_lancamentos.index')->with('success', 'Lançamento excluído com sucesso!');
    }
}

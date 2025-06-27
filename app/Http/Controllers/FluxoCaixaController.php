<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\FluxoCaixa;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use App\Services\CaixaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FluxoCaixaController extends Controller
{
    protected $caixaService;

    public function __construct(CaixaService $caixaService)
    {
        $this->caixaService = $caixaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = FluxoCaixa::with('movimento');

    $empresaSelecionada = session('empresa_id');
    $usuario = Auth::user();

    // Filtro de empresa: se não for Master, sempre aplica o filtro
    if ($usuario->hasRole('Master')) {
        if ($empresaSelecionada) {
            $query->where('empresa_id', $empresaSelecionada);
        }
        // senão mostra tudo
    } else {
        $query->where('empresa_id', $usuario->empresa_id);
    }

    // Filtro por tipo (entrada/saida)
    if ($request->filled('tipo')) {
        $query->where('tipo', $request->tipo);
    }

    // Filtro por data ou intervalo de datas
    if ($request->filled('data_inicio') && $request->filled('data_fim')) {
        $query->whereBetween('data', [$request->data_inicio, $request->data_fim]);
    } else {
        // Exibir do dia atual por padrão
        $hoje = \Carbon\Carbon::today();
        $query->whereDate('data', $hoje);
    }

    $fluxoCaixas = $query->orderBy('data', 'desc')->get();
    $users = $usuario;
    $movimento = Movimento::all();
    $empresa = Empresa::all();
    $caixa = Caixa::all();
    $planoDeContas = PlanoDeConta::all();

    // Totais por forma de pagamento
$totaisPorMovimento = FluxoCaixa::selectRaw('movimento_id, SUM(valor) as total')
    ->with('movimento')
    ->when(!Auth::user()->hasRole('Master'), function ($q) {
        $q->where('empresa_id', Auth::user()->empresa_id);
    })
    ->when($request->filled('tipo'), function ($q) use ($request) {
        $q->where('tipo', $request->tipo);
    })
    ->when($request->filled('data_inicio') && $request->filled('data_fim'), function ($q) use ($request) {
        $q->whereBetween('data', [$request->data_inicio, $request->data_fim]);
    }, function ($q) {
        $q->whereDate('data', \Carbon\Carbon::today());
    })
    ->groupBy('movimento_id')
    ->get();

// Total geral
$totalGeral = $totaisPorMovimento->sum('total');

    return view('fluxoCaixa.index', compact('fluxoCaixas', 'movimento', 'empresa', 'planoDeContas', 'users', 'caixa', 'totalGeral', 'totaisPorMovimento'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fluxoCaixa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'data' => 'required|date',
            'tipo' => 'required|in:entrada,saida,abertura,fechamento',
            'movimento_id' => 'required|exists:movimentos,id',
            'caixa_id' => 'required|exists:caixas,id',
            'empresa_id' => 'required|exists:empresas,id',
            'valor_total' => 'required|numeric|min:0',
            'plano_de_conta_id' => 'nullable|exists:plano_de_contas,id'
        ]);

        try {
            // Buscar o Caixa para passar para o serviço
            $caixa = Caixa::findOrFail($request->caixa_id);

            // Chama o serviço para inserir movimentação (com validação de saldo)
            $this->caixaService->inserirMovimentacao($caixa, $request->all());

            return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa cadastrado com sucesso!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('sweet_error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(FluxoCaixa $fluxoCaixa)
    {
        $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
        return view('fluxoCaixa.show', compact('fluxoCaixa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FluxoCaixa $fluxoCaixa)
    {
        $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
        return view('fluxoCaixa.edit', compact('fluxoCaixa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FluxoCaixa $fluxoCaixa)
    {
        try {
            $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saida,abertura,fechamento',
                'movimento_id' => 'required|exists:movimentos,id',
                'caixa_id' => 'required|exists:caixas,id',
                'empresa_id' => 'required|exists:empresas,id',
                'valor_total' => 'required|numeric',
                'plano_de_conta_id' => 'nullable|exists:plano_de_contas,id'
            ]);
            $request['usuario_id'] = Auth::user()->id;
            $fluxoCaixa->update($request->all());
            return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('fluxoCaixa.index')->with('error', 'Erro ao atualizar fluxo de caixa!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FluxoCaixa $fluxoCaixa)
    {
        $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
        $fluxoCaixa->delete();
        return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa excluído com sucesso!');
    }
}

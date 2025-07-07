<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\FluxoCaixa;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use App\Services\CaixaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FluxoCaixaController extends Controller
{
    protected $caixaService;

    public function __construct(CaixaService $caixaService)
    {
        $this->caixaService = $caixaService;
    }

    public function abrir(Request $request, $id)
    {
        $request->validate([
            'valor_inicial' => 'required|numeric|min:0',
        ]);

        $caixa = Caixa::findOrFail($id);

        if ($caixa->status === 'aberto') {
            return back()->with('error', 'O caixa já está aberto.');
        }

        $this->caixaService->abrirCaixa($caixa, $request->valor_inicial);

        return back()->with('success', 'Caixa aberto com sucesso.');
    }

    public function fechar(Request $request, $id)
    {
        try {
            $request->merge([
                'valor_final' => str_replace(',', '.', $request->valor_final),
            ]);

            $request->validate([
                'valor_final' => 'required|numeric|min:0',
            ]);

            $caixa = Caixa::findOrFail($id);

            if ($caixa->status === 'fechado') {
                return back()->with('error', 'O caixa já está fechado.');
            }

            $this->caixaService->fecharCaixa($caixa, $request->valor_final);

            return back()->with('success', 'Caixa fechado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao fechar caixa', $e);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $empresaSelecionada = session('empresa_id');

        // -----------------------------
        // FLUXO DE CAIXA
        // -----------------------------
        $fluxoQuery = FluxoCaixa::with('movimento');

        if ($usuario->hasRole('Master')) {
            if ($empresaSelecionada) {
                $fluxoQuery->where('empresa_id', $empresaSelecionada);
            }
        } elseif ($usuario->hasRole('financeiro')) {
            // Pode ver todos os fluxos da empresa dele
            $fluxoQuery->where('empresa_id', $usuario->empresa_id);
        } else {
            // Só pode ver os próprios fluxos
            $fluxoQuery->where('empresa_id', $usuario->empresa_id)
                ->where('usuario_id', $usuario->id);
        }



        // Filtros opcionais de tipo e data e caixa
        if ($request->filled('tipo')) {
            $fluxoQuery->where('tipo', $request->tipo);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $fluxoQuery->whereBetween('data', [$request->data_inicio, $request->data_fim]);
        } else {
            $fluxoQuery->whereDate('data', \Carbon\Carbon::today());
        }

        if ($request->filled('caixa_id')) {
            $fluxoQuery->where('caixa_id', $request->caixa_id);
        }

        $fluxoCaixas = $fluxoQuery->orderBy('data', 'desc')->orderBy('id', 'desc')->get();

        // -----------------------------
        // TOTALIZADOR
        // -----------------------------
        $totaisPorMovimento = FluxoCaixa::selectRaw('movimento_id, SUM(valor) as total')
            ->with('movimento')
            ->when(!$usuario->hasRole('Master'), function ($q) use ($usuario) {
                $q->where('empresa_id', $usuario->empresa_id);
            })
            ->when($request->filled('tipo'), function ($q) use ($request) {
                $q->where('tipo', $request->tipo);
            })
            ->when($request->filled('data_inicio') && $request->filled('data_fim'), function ($q) use ($request) {
                $q->whereBetween('data', [$request->data_inicio, $request->data_fim]);
            }, function ($q) {
                $q->whereDate('data', \Carbon\Carbon::today());
            })
            ->when($request->filled('caixa_id'), function ($q) use ($request) {
                $q->where('caixa_id', $request->caixa_id);
            })
            ->groupBy('movimento_id')
            ->get();

        $totalGeral = $totaisPorMovimento
    ->filter(function ($item) {
        return optional($item->movimento)->descricao !== 'venda-sympla';
    })
    ->sum('total');


        // -----------------------------
        // CAIXAS
        // -----------------------------
        $caixaQuery = Caixa::query();

        if ($usuario->hasRole('Master')) {
            if ($empresaSelecionada) {
                $caixaQuery->where('empresa_id', $empresaSelecionada);
            }
        } elseif ($usuario->hasRole('financeiro')) {
            $caixaQuery->where('empresa_id', $usuario->empresa_id);
        } else {
            $caixaQuery->where('empresa_id', $usuario->empresa_id)
                ->where('usuario_id', $usuario->id);
        }

        $caixas = $caixaQuery->get();

        // -----------------------------
        // DEMAIS DADOS
        // -----------------------------
        $empresas = Empresa::all();
        $planoDeContas = PlanoDeConta::all();
        $movimento = Movimento::all();

        return view('fluxoCaixa.index', compact(
            'fluxoCaixas',
            'movimento',
            'empresas',
            'planoDeContas',
            'usuario',
            'caixas',
            'totalGeral',
            'totaisPorMovimento'
        ));
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

    public function exportResumoPDF(Request $request)
    {
        $query = FluxoCaixa::with('movimento');

        // Filtros
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $dataInicio = Carbon::parse($request->data_inicio)->startOfDay();
            $dataFim = Carbon::parse($request->data_fim)->endOfDay();
            $query->whereBetween('data', [$dataInicio, $dataFim]);
        } else {
            $dataInicio = Carbon::today()->startOfDay();
            $dataFim = Carbon::today()->endOfDay();
            $query->whereDate('data', Carbon::today());
        }

        if ($request->filled('movimento_id')) {
            $query->where('movimento_id', $request->movimento_id);
        }

        if ($request->filled('caixa_id')) {
            $query->where('caixa_id', $request->caixa_id);
        }

        if (Auth::user()->hasRole('master')) {
            if ($request->filled('empresa_id')) {
                $query->where('empresa_id', $request->empresa_id);
            }
        } else {
            $query->where('empresa_id', session('empresa_id') ?? Auth::user()->empresa_id);
        }

        $caixaSelecionado = null;
        if ($request->filled('caixa_id')) {
            $caixaSelecionado = Caixa::find($request->caixa_id);
        }

        $fluxos = $query->get();

        // Agrupa e soma os valores por movimento
        $resumo = $fluxos->groupBy('movimento.descricao')->map(function ($grupo) {
            return $grupo->sum('valor');
        });

        $empresa = Empresa::find($request->empresa_id) ?? Auth::user()->empresa;
        $periodo = $dataInicio->format('d/m/Y') . ' a ' . $dataFim->format('d/m/Y');
        $dataEmissao = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('fluxoCaixa.pdf_resumo', compact(
            'resumo',
            'empresa',
            'periodo',
            'dataEmissao',
            'caixaSelecionado'
        ));

        return $pdf->stream('Resumo_Fluxo_Caixa.pdf');
    }
}

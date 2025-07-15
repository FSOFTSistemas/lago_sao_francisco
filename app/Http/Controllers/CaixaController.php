<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\FluxoCaixa;
use App\Services\CaixaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class CaixaController extends Controller
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
    public function index()
    {
        $usuario = Auth::user();
        $empresas = Empresa::all();
        $empresaSelecionada = session('empresa_id'); // <-- Empresa escolhida no seletor

        if (Auth::user()->hasRole('Master')) {
            if ($empresaSelecionada) {
                $caixas = Caixa::where('empresa_id', $empresaSelecionada)->get();
            } else {
                $caixas = Caixa::all();
            }
        } else {
            $caixas = Caixa::where('empresa_id', Auth::user()->empresa_id)->get();
        }

        return view('caixa.index', compact('caixas', 'empresas', 'usuario'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([

                'descricao' => 'required|string',
                'valor_inicial' => 'nullable|numeric',
                'valor_final' => 'nullable|numeric',
                'data_abertura' => 'required|date',
                'data_fechamento' => 'nullable|date',
                'status' => 'required|in:aberto,fechado',
                'observacoes' => 'nullable|string',
            ]);
            $request['empresa_id'] = Auth::user()->empresa_id;
            $request['usuario_abertura_id'] = Auth::user()->id;
            $request['usuario_fechamento_id'] = Auth::user()->id;
            Caixa::create($request->all());
            return redirect()->route('caixa.index')->with('success', 'Caixa cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('caixa.index')->with('error', 'Erro ao cadastrar caixa!');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Caixa $caixa)
    {
        try {
            $caixa = Caixa::findOrfail($caixa->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor_inicial' => 'nullable|numeric',
                'valor_final' => 'nullable|numeric',
                'data_abertura' => 'required|date',
                'data_fechamento' => 'nullable|date',
                'status' => 'required|in:aberto,fechado',
                'observacoes' => 'nullable|string',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $caixa->update($request->all());
            return redirect()->route('caixa.index')->with('success', 'Caixa atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('caixa.index')->with('error', 'Erro ao atualizar caixa!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Caixa $caixa)
    {
        try {
            $caixa = Caixa::findOrfail($caixa->id);
            $caixa->delete();
            return redirect()->route('caixa.index')->with('success', 'Caixa deletado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('caixa.index')->with('error', 'Erro ao deletar caixa!');
        }
    }

    public function getResumoFechamento(Caixa $caixa)
    {
        $inicio = Carbon::parse($caixa->data_abertura)->startOfDay();
        $fim = Carbon::parse($caixa->data_abertura)->endOfDay();

        $fluxos = FluxoCaixa::with('movimento')
            ->where('caixa_id', $caixa->id)
            ->whereBetween('created_at', [$inicio, $fim])
            ->whereDoesntHave('movimento', function ($query) {
                $query->whereIn('descricao', ['abertura de caixa', 'fechamento de caixa']);
            })
            ->get();


        // Cálculo de saldo inclui todos os tipos (entrada, saida, cancelamento) e soma o fundo de caixa
        $saldo = $fluxos
            ->filter(
                fn($fluxo) =>
                optional($fluxo->movimento)->descricao !== 'fechamento de caixa'
            )
            ->reduce(function ($total, $fluxo) {
                $valor = $fluxo->valor;
                $descricao = optional($fluxo->movimento)->descricao;

                return Str::startsWith($descricao, 'cancelamento-')
                    ? $total - $valor
                    : $total + $valor;
            }, 0) + $caixa->valor_inicial;





        // Agrupamento por forma de pagamento
        $formasPagamento = $fluxos->filter(function ($fluxo) {
            return $fluxo->movimento && str_contains($fluxo->movimento->descricao, '-');
        })->groupBy(function ($fluxo) {
            $descricao = $fluxo->movimento->descricao;

            if (Str::startsWith($descricao, 'cancelamento-')) {
                return 'cancelamento';
            }

            $partes = explode('-', $descricao);
            return $partes[1] ?? 'outro';
        })->map(function ($items) {
            return $items->sum('valor');
        });


        return [
            'saldo' => $saldo,
            'formas' => $formasPagamento,
        ];
    }
}

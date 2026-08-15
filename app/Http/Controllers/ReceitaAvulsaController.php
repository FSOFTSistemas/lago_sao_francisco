<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\FluxoCaixa;
use App\Models\Log as LogSistema;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use App\Services\CaixaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceitaAvulsaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $empresaSelecionada = $this->empresaSelecionada($request);
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', now()->endOfMonth()->toDateString());

        $empresas = $usuario->hasRole('Master')
            ? Empresa::orderBy('nome_fantasia')->get()
            : Empresa::where('id', $usuario->empresa_id)->get();

        $caixas = Caixa::query()
            ->when($empresaSelecionada, fn ($query) => $query->where('empresa_id', $empresaSelecionada))
            ->orderByDesc('data_abertura')
            ->orderByDesc('id')
            ->get();

        $planosDeConta = PlanoDeConta::query()
            ->with('empresa')
            ->where('tipo', 'receita')
            ->orderBy('descricao')
            ->get();

        $movimentos = Movimento::where('descricao', 'like', 'venda-%')
            ->orderBy('descricao')
            ->get();

        $receitas = FluxoCaixa::with(['caixa', 'movimento', 'planoDeConta', 'daEmpresa'])
            ->where('tipo', 'entrada')
            ->where('descricao', 'like', 'Receita avulsa:%')
            ->when($empresaSelecionada, fn ($query) => $query->where('empresa_id', $empresaSelecionada))
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->get();

        return view('receitasAvulsas.index', compact(
            'caixas',
            'dataFim',
            'dataInicio',
            'empresaSelecionada',
            'empresas',
            'movimentos',
            'planosDeConta',
            'receitas',
            'usuario'
        ));
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();
        $empresaId = $usuario->hasRole('Master')
            ? (int) $request->input('empresa_id')
            : (int) $usuario->empresa_id;

        $request->merge(['empresa_id' => $empresaId]);

        $dados = $request->validate([
            'empresa_id' => ['required', 'exists:empresas,id'],
            'caixa_id' => [
                'required',
                'exists:caixas,id',
                function ($attribute, $value, $fail) use ($empresaId) {
                    if (! Caixa::where('id', $value)->where('empresa_id', $empresaId)->exists()) {
                        $fail('O caixa selecionado não pertence à empresa informada.');
                    }
                },
            ],
            'plano_de_conta_id' => [
                'required',
                'exists:plano_de_contas,id',
                function ($attribute, $value, $fail) {
                    if (! PlanoDeConta::where('id', $value)->where('tipo', 'receita')->exists()) {
                        $fail('Selecione um plano de contas de receita.');
                    }
                },
            ],
            'movimento_id' => [
                'required',
                'exists:movimentos,id',
                function ($attribute, $value, $fail) {
                    if (! Movimento::where('id', $value)->where('descricao', 'like', 'venda-%')->exists()) {
                        $fail('Selecione um movimento de venda/receita.');
                    }
                },
            ],
            'data' => ['required', 'date'],
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
        ]);

        $caixa = Caixa::findOrFail($dados['caixa_id']);

        $fluxo = app(CaixaService::class)->inserirMovimentacao($caixa, [
            'descricao' => 'Receita avulsa: '.$dados['descricao'],
            'valor' => $dados['valor'],
            'valor_total' => $dados['valor'],
            'tipo' => 'entrada',
            'data' => Carbon::parse($dados['data'])->toDateString(),
            'movimento_id' => $dados['movimento_id'],
            'plano_de_conta_id' => $dados['plano_de_conta_id'],
        ]);

        LogSistema::create([
            'tipo_acao' => 'Criou',
            'descricao' => 'Receita avulsa lançada no fluxo de caixa #'.$fluxo->id
                .' | Empresa: '.$caixa->empresa_id
                .' | Caixa: '.$caixa->id
                .' | Data: '.$fluxo->data
                .' | Valor: R$ '.number_format((float) $fluxo->valor, 2, ',', '.')
                .' | Plano de conta ID: '.$fluxo->plano_de_conta_id
                .' | Movimento ID: '.$fluxo->movimento_id
                .' | Descrição: '.$fluxo->descricao,
            'usuario_id' => Auth::id(),
            'data_hora' => now(),
        ]);

        return redirect()
            ->route('receitas-avulsas.index', [
                'empresa_id' => $empresaId,
                'data_inicio' => Carbon::parse($dados['data'])->startOfMonth()->toDateString(),
                'data_fim' => Carbon::parse($dados['data'])->endOfMonth()->toDateString(),
            ])
            ->with('success', 'Receita avulsa lançada com sucesso.');
    }

    private function empresaSelecionada(Request $request): ?int
    {
        $usuario = Auth::user();

        if (! $usuario->hasRole('Master')) {
            return $usuario->empresa_id;
        }

        return $request->filled('empresa_id')
            ? (int) $request->input('empresa_id')
            : (session('empresa_id') ? (int) session('empresa_id') : null);
    }
}

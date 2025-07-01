<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\ContasAReceber;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FluxoCaixa;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use App\Models\Venda;
use App\Services\CaixaService;
use App\Services\ContasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContasAReceberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContasAReceber::query();

        // Filtrar por intervalo de datas
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_vencimento', [
                $request->input('data_inicio'),
                $request->input('data_fim')
            ]);
        } else {
            $query->whereBetween('data_vencimento', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        }

        // Filtrar por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Obter dados e carregar com relacionamentos
        $contasAReceber = $query->with('cliente')->orderByRaw('MONTH(data_vencimento)')->get();


        $user = Auth::user();
        $clientes = Cliente::all();
        $empresas = Empresa::all();
        $planoDeContas = PlanoDeConta::all();
        $vendas = Venda::all();
        return view('contasAReceber.index', compact('contasAReceber', 'clientes', 'empresas', 'planoDeContas', 'vendas', 'user'));
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
                'valor_recebido' => 'nullable|numeric',
                'data_vencimento' => 'required|date',
                'data_recebimento' => 'nullable|date',
                'status' => 'required|string',
                'venda_id' => 'nullable|exists:vendas,id',
                'parcela' => 'nullable|integer|min:1',
                'cliente_id' => 'required|exists:clientes,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
            ]);

            $dadosBase = $request->all();
            $dadosBase['empresa_id'] = Auth::user()->empresa_id;

            $vencimento = $dadosBase['data_vencimento'];
            $qtdParcelas = $request->parcela ?? 1;

            // Divide o valor pelas parcelas
            $valorTotal = $request->valor;
            $valorParcela = round($valorTotal / $qtdParcelas, 2); // arredondamento simples

            if ($qtdParcelas > 1) {
                $descricaoOriginal = $dadosBase['descricao'];

                for ($i = 1; $i <= $qtdParcelas; $i++) {
                    $dadosParcela = $dadosBase;
                    $dadosParcela['data_vencimento'] = $vencimento;
                    $dadosParcela['valor'] = $valorParcela;
                    $dadosParcela['descricao'] = "{$descricaoOriginal} | {$i}/{$qtdParcelas}";

                    ContasAReceber::create($dadosParcela);

                    $vencimento = ContasService::proximoMes($vencimento);
                }
            } else {
                ContasAReceber::create($dadosBase);
            }

            return redirect()->route('contasAReceber.index')->with('success', 'Conta a receber criada com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar conta a receber: ' . $e->getMessage());
        }
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContasAReceber $contasAReceber)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'valor_recebido' => 'nullable|numeric',
                'data_vencimento' => 'required|date',
                'data_pagamento' => 'nullable|date',
                'status' => 'required|string',
                'venda_id' => 'nullable|exists:vendas,id',
                'parcela' => 'nullable|integer',
                'cliente_id' => 'required|exists:clientes,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
            ]);

            $contasAReceber->update($request->all());
            return redirect()->route('contasAReceber.index')->with('success', 'Conta a receber atualizada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContasAReceber $contasAReceber)
    {
        try {
            $contasAReceber->delete();
            return redirect()->route('contasAReceber.index')->with('success', 'Conta a receber deletada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar conta a receber');
        }
    }

    public function receber(Request $request)
    {
        $data = $request->validate([
            'pagamento_id' => ['required', 'numeric'],
            'forma_pagamento' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $usuario = Auth::user();
            $empresaId = $usuario->empresa_id;

            // Buscar o caixa aberto do dia
            $caixa = Caixa::whereDate('data_abertura', now())
                ->where('status', 'aberto')
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$caixa) {
                throw new \Exception("Nenhum caixa aberto encontrado para registrar o recebimento.");
            }

            // Atualiza status da conta
            $pagamento = ContasAReceber::findOrFail($data['pagamento_id']);

            $valorAtual = $pagamento->valor_recebido ?? 0;
            $novoValor = $pagamento->valor;
            $valorFinal = $valorAtual + $novoValor;

            $pagamento->update([
                'data_recebimento' => now(),
                'valor_recebido' => $valorFinal,
                'status' => 'finalizado',
            ]);

            // Encontra o movimento com base na forma de pagamento
            $slug = 'recebimento-' . strtolower(str_replace(' ', '-', $data['forma_pagamento']));
            $movimentoId = Movimento::where('descricao', $slug)->value('id');

            if (!$movimentoId) {
                throw new \Exception("Movimento '{$slug}' não encontrado.");
            }

            // Usa o CaixaService para inserir o fluxo
            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => $pagamento->descricao,
                'valor' => $pagamento->valor,
                'tipo' => 'entrada',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => $pagamento->plano_de_contas_id ?? 1,
            ]);

        });
        
        // Dados para o PDF
        $pagamento = ContasAReceber::findOrFail($data['pagamento_id']);
    $pdfData = [
        'cliente' => $pagamento->cliente->nome_razao_social ?? 'Cliente não informado',
        'descricao' => $pagamento->descricao,
        'valor' => number_format($pagamento->valor, 2, ',', '.'),
        'forma_pagamento' => $data['forma_pagamento'],
        'data_pagamento' => now()->format('d/m/Y'),
        'data_vencimento' => \Carbon\Carbon::parse($pagamento->data_vencimento)->format('d/m/Y'),
        'parcela' => $this->extrairParcela($pagamento->descricao),
    ];

    // Gera PDF em memória
    $pdf = Pdf::loadView('contasAReceber.recibo', $pdfData);

    // Define nome e caminho para salvar o PDF
    $fileName = 'recibos/recibo_' . now()->format('Ymd_His') . '.pdf';

    // Salva o PDF no disco storage/app/public/recibos (precisa ter link simbólico 'storage')
    Storage::disk('public')->put($fileName, $pdf->output());

    // Redireciona para o index com link para o recibo na sessão (como HTML)
    return redirect()->route('contasAReceber.index')->with('success', 
        'Pagamento efetuado com sucesso! <a href="' . asset('storage/' . $fileName) . '" target="_blank" rel="noopener noreferrer">Ver recibo</a>'
    );
    }

    private function extrairParcela($descricao)
{
    if (preg_match('/\b(\d+\/\d+)\b/', $descricao, $matches)) {
        return $matches[1];
    }
    return 'Única';
}

}

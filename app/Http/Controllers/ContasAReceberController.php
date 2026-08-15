<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\ContaPagamentos;
use App\Models\ContasAReceber;
use App\Models\Empresa;
use App\Models\FormaPagamento;
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
        $user = Auth::user();
        $empresaSelecionada = session('empresa_id');
        $empresaId = $user->hasRole('Master') ? $empresaSelecionada : $user->empresa_id;

        $query = ContasAReceber::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId));

        // Filtrar por intervalo de datas
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_vencimento', [
                $request->input('data_inicio'),
                $request->input('data_fim'),
            ]);
        } else {
            $query->whereBetween('data_vencimento', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
            ]);
        }

        // Filtrar por status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Obter dados e carregar com relacionamentos
        $contasAReceber = $query->with('cliente')->orderByRaw('MONTH(data_vencimento)')->get();

        $clientes = Cliente::all();
        $empresas = Empresa::all();
        $planoDeContas = PlanoDeConta::query()
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where(function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId)
                        ->orWhereNull('empresa_id');
                });
            })
            ->orderBy('descricao')
            ->get();
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
                'status' => 'required|in:pendente,recebido,atrasado',
                'venda_id' => 'nullable|exists:vendas,id',
                'parcela' => 'nullable|integer|min:1',
                'cliente_id' => 'required|exists:clientes,id',
                'plano_de_contas_id' => [
                    'nullable',
                    'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        if (! $value) {
                            return;
                        }

                        $usuario = Auth::user();
                        $empresaId = $usuario->hasRole('Master') && session('empresa_id')
                            ? session('empresa_id')
                            : $usuario->empresa_id;

                        if (! PlanoDeConta::where('id', $value)
                            ->where(function ($query) use ($empresaId) {
                                $query->where('empresa_id', $empresaId)
                                    ->orWhereNull('empresa_id');
                            })->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    },
                ],
            ]);

            $dadosBase = $request->all();
            $dadosBase['empresa_id'] = Auth::user()->empresa_id;

            $vencimento = $dadosBase['data_vencimento'];
            $qtdParcelas = $request->parcela ?? 1;

            // Divide o valor pelas parcelas
            $valorTotal = $request->valor;
            $valorRecebido = (float) ($request->valor_recebido ?? 0);

            if ($valorRecebido > (float) $valorTotal) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'O valor recebido não pode ser maior que o valor da conta.');
            }

            if ($dadosBase['status'] === 'recebido' && $valorRecebido <= 0) {
                $dadosBase['valor_recebido'] = $valorTotal;
                $valorRecebido = (float) $valorTotal;
            }

            if ($valorRecebido >= (float) $valorTotal) {
                $dadosBase['status'] = 'recebido';
            }

            if ($dadosBase['status'] === 'recebido' && empty($dadosBase['data_recebimento'])) {
                $dadosBase['data_recebimento'] = now()->toDateString();
            }

            $valorParcela = round($valorTotal / $qtdParcelas, 2); // arredondamento simples

            // Gera um grupo_id único se for parcelado
            $grupoId = $qtdParcelas > 1 ? mt_rand(100000, 999999999) : null;

            if ($qtdParcelas > 1) {
                $descricaoOriginal = $dadosBase['descricao'];

                for ($i = 1; $i <= $qtdParcelas; $i++) {
                    $dadosParcela = $dadosBase;
                    $dadosParcela['data_vencimento'] = $vencimento;
                    $dadosParcela['valor'] = $valorParcela;
                    $dadosParcela['valor_recebido'] = 0;
                    $dadosParcela['data_recebimento'] = null;
                    $dadosParcela['status'] = 'pendente';
                    $dadosParcela['descricao'] = "{$descricaoOriginal} | {$i}/{$qtdParcelas}";
                    $dadosParcela['grupo_id'] = $grupoId;

                    ContasAReceber::create($dadosParcela);

                    $vencimento = ContasService::proximoMes($vencimento);
                }
            } else {
                $dadosBase['grupo_id'] = null; // explícito, se quiser
                ContasAReceber::create($dadosBase);
            }

            return redirect()->route('contasAReceber.index')->with('success', 'Conta a receber criada com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar conta a receber: '.$e->getMessage());
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
                'data_recebimento' => 'nullable|date',
                'status' => 'required|in:pendente,recebido,atrasado',
                'venda_id' => 'nullable|exists:vendas,id',
                'parcela' => 'nullable|integer',
                'cliente_id' => 'required|exists:clientes,id',
                'plano_de_contas_id' => [
                    'nullable',
                    'exists:plano_de_contas,id',
                    function ($attribute, $value, $fail) {
                        if (! $value) {
                            return;
                        }

                        $usuario = Auth::user();
                        $empresaId = $usuario->hasRole('Master') && session('empresa_id')
                            ? session('empresa_id')
                            : $usuario->empresa_id;

                        if (! PlanoDeConta::where('id', $value)
                            ->where(function ($query) use ($empresaId) {
                                $query->where('empresa_id', $empresaId)
                                    ->orWhereNull('empresa_id');
                            })->exists()) {
                            $fail('O plano de contas selecionado não pertence à sua empresa.');
                        }
                    },
                ],
            ]);

            $dados = $request->only([
                'descricao',
                'valor',
                'valor_recebido',
                'data_vencimento',
                'data_recebimento',
                'status',
                'venda_id',
                'parcela',
                'cliente_id',
                'plano_de_contas_id',
            ]);

            $valorRecebido = (float) ($dados['valor_recebido'] ?? 0);
            $valor = (float) $dados['valor'];

            if ($valorRecebido > $valor) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'O valor recebido não pode ser maior que o valor da conta.');
            }

            if ($dados['status'] === 'recebido' && $valorRecebido <= 0) {
                $dados['valor_recebido'] = $valor;
                $valorRecebido = $valor;
            }

            if ($valorRecebido >= $valor) {
                $dados['status'] = 'recebido';
            }

            if ($dados['status'] === 'recebido' && empty($dados['data_recebimento'])) {
                $dados['data_recebimento'] = now()->toDateString();
            }

            $contasAReceber->update($dados);

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
            if ($this->grupoPossuiRecebimentos($contasAReceber->id)) {
                return redirect()->back()->with('error', 'Não é possível excluir uma conta que já recebeu valores.');
            } else {
                if ($contasAReceber->grupo_id) {
                    // Exclui todas as contas do mesmo grupo
                    ContasAReceber::where('grupo_id', $contasAReceber->grupo_id)->delete();
                    $mensagem = 'Todas as parcelas do grupo foram deletadas com sucesso.';
                } else {
                    // Exclui apenas a conta individual
                    $contasAReceber->delete();
                    $mensagem = 'Conta a receber deletada com sucesso.';
                }

                return redirect()->route('contasAReceber.index')->with('success', $mensagem);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao deletar conta a receber: '.$e->getMessage());
        }
    }

    public function receber(Request $request)
    {
        $data = $request->validate([
            'pagamento_id' => ['required', 'numeric'],
            'forma_pagamento' => ['required', 'string'],
            'valor_pago' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($data) {
            $usuario = Auth::user();
            $empresaId = $usuario->empresa_id;

            $caixa = Caixa::whereDate('data_abertura', now())
                ->where('status', 'aberto')
                ->where('empresa_id', $empresaId)
                ->first();

            if (! $caixa) {
                throw new \Exception('Nenhum caixa aberto encontrado para registrar o recebimento.');
            }

            $pagamento = ContasAReceber::findOrFail($data['pagamento_id']);

            // Calcula novo valor recebido acumulado
            $valorAtual = $pagamento->valor_recebido ?? 0;
            $valorFinal = $valorAtual + $data['valor_pago'];
            $valorRestante = $pagamento->valor - $valorAtual;

            if ($valorFinal > $pagamento->valor) {
                throw new \Exception("O valor recebido ({$data['valor_pago']}) não pode ser maior que o valor restante ({$valorRestante}).");
            }

            // Atualiza o pagamento
            $pagamento->update([
                'valor_recebido' => $valorFinal,
                'data_recebimento' => now(),
                'status' => $valorFinal >= $pagamento->valor ? 'recebido' : 'pendente',
            ]);

            // Cria registro em ContaPagamentos
            ContaPagamentos::create([
                'conta_id' => $pagamento->id,
                'cliente_id' => $pagamento->cliente_id,
                'usuario_id' => $usuario->id,
                'valor_recebido' => $data['valor_pago'],
                'data_recebimento' => now(),
            ]);

            // Movimento
            $slug = FormaPagamento::descricaoMovimento('recebimento', $data['forma_pagamento']);
            $movimentoId = Movimento::where('descricao', $slug)->value('id');

            if (! $movimentoId) {
                throw new \Exception("Movimento '{$slug}' não encontrado.");
            }

            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => $pagamento->descricao,
                'valor' => $data['valor_pago'],
                'tipo' => 'entrada',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => $pagamento->plano_de_contas_id
                    ?? PlanoDeConta::idPorDescricao('Receita', $empresaId, 'receita'),
            ]);
        });

        // PDF (atualizado para refletir valor pago)
        $pagamento = ContasAReceber::findOrFail($data['pagamento_id']);
        $pdfData = [
            'cliente' => $pagamento->cliente->nome_razao_social ?? 'Cliente não informado',
            'descricao' => $pagamento->descricao,
            'valor' => number_format($data['valor_pago'], 2, ',', '.'),
            'forma_pagamento' => $data['forma_pagamento'],
            'data_pagamento' => now()->format('d/m/Y'),
            'data_vencimento' => Carbon::parse($pagamento->data_vencimento)->format('d/m/Y'),
            'parcela' => $this->extrairParcela($pagamento->descricao),
        ];

        $pdf = Pdf::loadView('contasAReceber.recibo', $pdfData);
        $fileName = 'recibos/recibo_'.now()->format('Ymd_His').'.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        return redirect()->back()->with(
            'success',
            'Pagamento efetuado com sucesso! <a href="'.asset('storage/'.$fileName).'" target="_blank" rel="noopener noreferrer">Ver recibo</a>'
        );
    }

    private function extrairParcela($descricao)
    {
        if (preg_match('/\b(\d+\/\d+)\b/', $descricao, $matches)) {
            return $matches[1];
        }

        return 'Única';
    }

    public function grupoPossuiRecebimentos(int $contaId): bool
    {
        $conta = ContasAReceber::findOrFail($contaId);

        // Se não houver grupo, verifica só a própria conta
        if (is_null($conta->grupo_id)) {
            return ContaPagamentos::where('conta_id', $conta->id)->exists();
        }

        // Busca todas as contas do grupo e verifica se alguma tem pagamento
        return ContaPagamentos::whereIn('conta_id', function ($query) use ($conta) {
            $query->select('id')
                ->from('contas_a_receber')
                ->where('grupo_id', $conta->grupo_id);
        })->exists();
    }
}

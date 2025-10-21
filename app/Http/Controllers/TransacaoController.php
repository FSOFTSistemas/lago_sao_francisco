<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use App\Models\Reserva;
use App\Models\Caixa;
use App\Models\ContasAPagar;
use App\Models\LogReserva;
use App\Models\Movimento;
use App\Services\CaixaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TransacaoController extends Controller
{
    protected $caixaService;

    public function __construct(CaixaService $caixaService)
    {
        $this->caixaService = $caixaService;
    }

    public function index()
    {
        $transacoes = Transacao::with(['formaPagamento', 'reserva'])
            ->latest()
            ->paginate(10);
        
        return view('transacoes.index', compact('transacoes'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string|max:255',
                'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
                'categoria' => 'required|in:hospedagem,alimentos,servicos,produtos',
                'data_pagamento' => 'required|date',
                'tipo' => 'required|in:pagamento,desconto',
                'valor' => 'required|numeric|min:0',
                'observacoes' => 'nullable|string',
                'reserva_id' => 'required|exists:reservas,id',
                'comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // 2MB Max
            ]);

            $comprovantePath = null;
        if ($request->hasFile('comprovante')) { 
           
            $comprovantePath = $request->file('comprovante')->store('comprovantes', 'public'); 
        }

            DB::beginTransaction();

            // Criar a transação
            $transacao = Transacao::create([
                'descricao' => $request->descricao,
                'status' => true,
                'forma_pagamento_id' => $request->forma_pagamento_id,
                'categoria' => $request->categoria,
                'data_pagamento' => $request->data_pagamento,
                'data_vencimento' => $request->data_vencimento,
                'tipo' => $request->tipo,
                'valor' => $request->valor,
                'observacoes' => $request->observacoes,
                'reserva_id' => $request->reserva_id,
                'comprovante_path' => $comprovantePath,
            ]);
            LogReserva::registrarPagamentoAdicionado($request->reserva_id, Auth::id(), $transacao);

            // Integrar com FluxoCaixa via CaixaService
            $this->criarMovimentacaoCaixa($transacao);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transação criada com sucesso!',
                'transacao' => $transacao->load('formaPagamento')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($comprovantePath) && Storage::disk('public')->exists($comprovantePath)) { 
            Storage::disk('public')->delete($comprovantePath); 
        }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar transação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Transacao $transacao)
    {
        $transacao->load(['formaPagamento', 'reserva']);
        return view('transacao.show', compact('transacao'));
    }

    public function update(Request $request, Transacao $transacao)
    {
        try {
            $request->validate([
                'descricao' => 'required|string|max:255',
                'valor' => 'required|numeric|min:0',
                'observacoes' => 'nullable|string',
            ]);

            $transacao->update($request->only(['descricao', 'valor', 'observacoes']));

            return response()->json([
                'success' => true,
                'message' => 'Transação atualizada com sucesso!',
                'transacao' => $transacao
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar transação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
{
    try {
        DB::beginTransaction();
        $transacao = Transacao::findOrFail($id);

        // Verificar se a transação é do mesmo dia ou de dias anteriores
        $dataTransacao = Carbon::parse($transacao->data_pagamento);
        $hoje = Carbon::today();

        if ($dataTransacao->isSameDay($hoje)) {
            // Transação do mesmo dia: criar movimentação de cancelamento no FluxoCaixa
            $this->cancelarMovimentacaoCaixa($transacao);
        } else {
            // Transação de dias anteriores: criar ContasAPagar
            $this->criarContasAPagar($transacao);
        }
        LogReserva::registrarPagamentoRemovido($transacao->reserva->id, Auth::id(), $transacao);

        // 5. APAGAR O ARQUIVO DO DISCO QUANDO A TRANSAÇÃO FOR REMOVIDA
        if ($transacao->comprovante_path) { 
            Storage::disk('public')->delete($transacao->comprovante_path); 
        }

        // Remover a transação
        $transacao->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Transação removida com sucesso!'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Erro ao remover transação: ' . $e->getMessage()
        ], 500);
    }
}


    public function getByReserva($reservaId)
    {
        try {
            $transacoes = Transacao::where('reserva_id', $reservaId)
                ->where('status', true)
                ->with('formaPagamento')
                ->orderBy('data_pagamento', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'transacoes' => $transacoes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar transações: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getResumoByReserva($reservaId)
    {
        try {
            $reserva = Reserva::findOrFail($reservaId);
            
            // Calcular número de diárias
            $checkin = Carbon::parse($reserva->data_checkin);
            $checkout = Carbon::parse($reserva->data_checkout);
            $numDiarias = $checkin->diffInDays($checkout);
            
            // Buscar transações da reserva
            $transacoes = Transacao::where('reserva_id', $reservaId)
                ->where('status', true)
                ->get();
            
            // Calcular totais por categoria
            $totalDiarias = $reserva->valor_diaria * $numDiarias;
            $totalProdutos = $transacoes->where('categoria', 'produtos')->sum('valor');
            
            // Calcular total geral
            $totalGeral = $totalDiarias + $totalProdutos;
            
            // Calcular total recebido (pagamentos)
            $totalRecebido = $transacoes->where('tipo', 'pagamento')->sum('valor');
            
            // Calcular total de descontos
            $totalDescontos = $transacoes->where('tipo', 'desconto')->sum('valor');
            
            // Calcular falta lançar
            $faltaLancar = $totalGeral - $totalRecebido - $totalDescontos;

            $resumo = [
                'num_diarias' => $numDiarias,
                'valor_diaria' => $reserva->valor_diaria,
                'total_diarias' => $totalDiarias,
                'total_produtos' => $totalProdutos,
                'total_geral' => $totalGeral,
                'total_recebido' => $totalRecebido,
                'total_descontos' => $totalDescontos,
                'falta_lancar' => $faltaLancar,
            ];

            return response()->json([
                'success' => true,
                'resumo' => $resumo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular resumo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar movimentação no caixa para a transação
     * Baseado no padrão do AluguelController
     */
    private function criarMovimentacaoCaixa(Transacao $transacao)
    {
        try {
            $empresaId = Auth::user()->empresa_id;

            // Busca o caixa aberto da empresa no dia (seguindo o padrão do AluguelController)
            $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
                ->where('status', 'aberto')
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', Auth::id())
                ->first();

            if (!$caixa) {
                session()->flash('error', 'Nenhum caixa aberto encontrado para registrar movimentações.');
                return;
            }

            $formaPagamento = $transacao->formaPagamento;

            // Ignora se for crediário (seguindo o padrão do AluguelController)
            $descricaoForma = strtolower($formaPagamento->descricao ?? '');
            if (str_contains($descricaoForma, 'crediário')) {
                return;
            }

            // Criar slug da forma de pagamento (seguindo o padrão do AluguelController)
            $slug = strtolower(str_replace(' ', '-', $formaPagamento->slug ?? $formaPagamento->descricao ?? ''));
            
            // Determinar o tipo de movimento baseado na categoria da transação
            $tipoMov = match($transacao->categoria) {
                'hospedagem' => 'venda-' . $slug,
                'produtos' => 'venda-' . $slug,
                default => 'venda-' . $slug
            };

            // Buscar o movimento_id baseado na descrição (seguindo o padrão do AluguelController)
            $movimentoId = Movimento::where('descricao', $tipoMov)->value('id');

            if (!$movimentoId) {
                // Se não encontrar o movimento específico, tenta um movimento genérico
                $movimentoId = Movimento::where('descricao', 'venda-' . $slug)->value('id');
                
                if (!$movimentoId) {
                    return; // pula se não encontrar o movimento
                }
            }

            // Usar o CaixaService para inserir a movimentação (seguindo o padrão do AluguelController)
            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => 'Reserva #' . $transacao->reserva_id . ' - ' . $transacao->descricao,
                'valor' => $transacao->valor,
                'valor_total' => $transacao->valor,
                'tipo' => $transacao->tipo === 'pagamento' ? 'entrada' : 'saida',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => 44, // 44-> Hospedagem
            ]);

        } catch (\Exception $e) {
            // Log do erro mas não interrompe o fluxo (seguindo o padrão do AluguelController)
            Log::error('Erro ao criar movimentação no caixa: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar movimentação no caixa (criar movimentação de cancelamento)
     */
    public function cancelarMovimentacaoCaixa(Transacao $transacao)
    {
        try {
            $empresaId = Auth::user()->empresa_id;

            // Busca o caixa aberto da empresa no dia
            $caixa = Caixa::whereDate('data_abertura', now()->toDateString())
                ->where('status', 'aberto')
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', Auth::id())
                ->first();

            if (!$caixa) {
                session()->flash('error', 'Nenhum caixa aberto encontrado para registrar cancelamento.');
                return;
            }

            $formaPagamento = $transacao->formaPagamento;

            // Ignora se for crediário
            $descricaoForma = strtolower($formaPagamento->descricao ?? '');
            if (str_contains($descricaoForma, 'crediário')) {
                return;
            }

            // Criar slug da forma de pagamento
            $slug = strtolower(str_replace(' ', '-', $formaPagamento->slug ?? $formaPagamento->descricao ?? ''));
            
            // Buscar movimento de cancelamento específico ou genérico
            $tipoMovimentoCancelamento = 'cancelamento-' . $slug;
            $movimentoId = Movimento::where('descricao', $tipoMovimentoCancelamento)->value('id');
            if (!$movimentoId) {
                $movimentoId = Movimento::where('descricao', 'cancelamento')->value('id');
            }
            if (!$movimentoId) {
                Log::error("Movimento de cancelamento não encontrado para: {$tipoMovimentoCancelamento} ou cancelamento");
                return;
            }

            // Inserir movimentação de cancelamento via CaixaService
            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => 'CANCELAMENTO: ' . $transacao->descricao . ' - Reserva #' . $transacao->reserva_id,
                'valor' => $transacao->valor,
                'valor_total' => $transacao->valor,
                'tipo' => 'cancelamento',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => 49, // 49-> Serviços Cancelados
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar movimentação no caixa: ' . $e->getMessage());
        }
    }

    /**
     * Criar ContasAPagar para transações de dias anteriores
     */
    public function criarContasAPagar(Transacao $transacao)
    {
        try {
            ContasAPagar::create([
                'descricao' => 'ESTORNO: ' . $transacao->descricao . ' - Reserva #' . $transacao->reserva_id,
                'valor' => $transacao->valor,
                'data_vencimento' => Carbon::today(), // Data de vencimento é o dia atual
                'plano_de_contas_id' => 1, // Plano de conta padrão
                'status' => 'pendente', // Status padrão
                'empresa_id' => Auth::user()->empresa_id ?? 1,
                'observacoes' => 'Estorno de transação cancelada em ' . Carbon::now()->format('d/m/Y H:i:s') . 
                               '. Transação original de ' . Carbon::parse($transacao->data_pagamento)->format('d/m/Y'),
            ]);

            Log::info("ContasAPagar criada para estorno da transação ID: {$transacao->id}");

        } catch (\Exception $e) {
            Log::error('Erro ao criar ContasAPagar: ' . $e->getMessage());
            throw new \Exception('Erro ao criar conta a pagar para estorno: ' . $e->getMessage());
        }
    }
}

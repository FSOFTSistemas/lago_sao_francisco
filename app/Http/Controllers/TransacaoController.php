<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use App\Models\Reserva;
use App\Models\Caixa;
use App\Models\Movimento;
use App\Services\CaixaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        
        return view('transacao.index', compact('transacoes'));
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
            ]);

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
            ]);

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
            // Criar movimentação de cancelamento no caixa
            $this->cancelarMovimentacaoCaixa($transacao);

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
            $numDiarias = $checkout->diffInDays($checkin);
            
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
                'plano_de_conta_id' => 1, // ou o plano de conta padrão
            ]);

        } catch (\Exception $e) {
            // Log do erro mas não interrompe o fluxo (seguindo o padrão do AluguelController)
            Log::error('Erro ao criar movimentação no caixa: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar movimentação no caixa (criar movimentação de cancelamento)
     */
    private function cancelarMovimentacaoCaixa(Transacao $transacao)
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
            
            // Tipo de movimento para cancelamento
            $tipoMov = 'cancelamento-' . $slug;

            // Buscar o movimento_id para cancelamento
            $movimentoId = Movimento::where('descricao', $tipoMov)->value('id');

            if (!$movimentoId) {
                // Se não encontrar movimento específico de cancelamento, usar movimento genérico
                $movimentoId = Movimento::where('descricao', 'cancelamento')->value('id');
                
                if (!$movimentoId) {
                    return; // pula se não encontrar o movimento
                }
            }

            // Usar o CaixaService para inserir a movimentação de cancelamento
            app(CaixaService::class)->inserirMovimentacao($caixa, [
                'descricao' => 'CANCELAMENTO - Reserva #' . $transacao->reserva_id . ' - ' . $transacao->descricao,
                'valor' => $transacao->valor,
                'valor_total' => $transacao->valor,
                'tipo' => 'cancelamento',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => 1, // ou o plano de conta padrão
            ]);

        } catch (\Exception $e) {
            // Log do erro mas não interrompe o fluxo
            Log::error('Erro ao cancelar movimentação no caixa: ' . $e->getMessage());
        }
    }
}

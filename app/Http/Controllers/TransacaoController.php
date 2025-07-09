<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use App\Models\Reserva;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransacaoController extends Controller
{
    public function index()
    {
        $transacoes = Transacao::with(['reserva', 'formaPagamento'])->latest()->paginate(10);
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
                'data_vencimento' => 'nullable|date',
                'tipo' => 'required|in:pagamento,desconto',
                'valor' => 'required|numeric|min:0',
                'observacoes' => 'nullable|string',
                'reserva_id' => 'required|exists:reservas,id',
            ]);

            $transacao = Transacao::create([
                'descricao' => $request->descricao,
                'status' => true, // Por padrão, transações são criadas como ativas
                'forma_pagamento_id' => $request->forma_pagamento_id,
                'categoria' => $request->categoria,
                'data_pagamento' => $request->data_pagamento,
                'data_vencimento' => $request->data_vencimento,
                'tipo' => $request->tipo,
                'valor' => $request->valor,
                'observacoes' => $request->observacoes,
                'reserva_id' => $request->reserva_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transação criada com sucesso!',
                'transacao' => $transacao->load(['formaPagamento'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar transação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Transacao $transacao)
    {
        try {
            $request->validate([
                'descricao' => 'required|string|max:255',
                'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
                'categoria' => 'required|in:hospedagem,alimentos,servicos,produtos',
                'data_pagamento' => 'required|date',
                'data_vencimento' => 'nullable|date',
                'tipo' => 'required|in:pagamento,desconto',
                'valor' => 'required|numeric|min:0',
                'observacoes' => 'nullable|string',
                'status' => 'boolean',
            ]);

            $transacao->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Transação atualizada com sucesso!',
                'transacao' => $transacao->load(['formaPagamento'])
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
            $transacao = Transacao::findOrFail($id);
            $transacao->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transação removida com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover transação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByReserva($reservaId)
    {
        try {
            $transacoes = Transacao::with(['formaPagamento'])
                ->where('reserva_id', $reservaId)
                ->where('status', true)
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

    public function getResumoReserva($reservaId)
    {
        try {
            $reserva = Reserva::with(['quarto', 'transacoes.formaPagamento'])->findOrFail($reservaId);
            
            // Calcular número de diárias
            $checkin = Carbon::parse($reserva->data_checkin);
            $checkout = Carbon::parse($reserva->data_checkout);
            $numDiarias = $checkin->diffInDays($checkout);
            
            // Calcular totais por categoria
            $totalDiarias = $reserva->valor_diaria * $numDiarias;
            $totalProdutos = $reserva->transacoes->where('categoria', 'produtos')->where('status', true)->sum('valor');
            $totalServicos = $reserva->transacoes->where('categoria', 'servicos')->where('status', true)->sum('valor');
            $totalAlimentos = $reserva->transacoes->where('categoria', 'alimentos')->where('status', true)->sum('valor');
            
            // Total geral
            $totalGeral = $totalDiarias + $totalProdutos + $totalServicos + $totalAlimentos;
            
            // Total recebido (apenas pagamentos, não descontos)
            $totalRecebido = $reserva->transacoes
                ->where('tipo', 'pagamento')
                ->where('status', true)
                ->sum('valor');
            
            // Total de descontos
            $totalDescontos = $reserva->transacoes
                ->where('tipo', 'desconto')
                ->where('status', true)
                ->sum('valor');
            
            // Falta lançar
            $faltaLancar = $totalGeral - $totalRecebido - $totalDescontos;

            return response()->json([
                'success' => true,
                'resumo' => [
                    'num_diarias' => $numDiarias,
                    'valor_diaria' => $reserva->valor_diaria,
                    'total_diarias' => $totalDiarias,
                    'total_produtos' => $totalProdutos,
                    'total_servicos' => $totalServicos,
                    'total_alimentos' => $totalAlimentos,
                    'total_geral' => $totalGeral,
                    'total_recebido' => $totalRecebido,
                    'total_descontos' => $totalDescontos,
                    'falta_lancar' => $faltaLancar
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular resumo: ' . $e->getMessage()
            ], 500);
        }
    }
}

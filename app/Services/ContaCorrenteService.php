<?php

namespace App\Services;

use App\Models\ContaCorrente;
use App\Models\ContaCorrenteLancamento;
use App\Enums\LancamentoTipo; // **NOVO:** Recomendado usar um Enum para tipos
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use InvalidArgumentException;
use Throwable;

class ContaCorrenteService
{
    /**
     * Registra um lançamento na conta corrente, atualizando o saldo.
     *
     * @param array $dados
     * @param int|null $empresaId 
     * @return ContaCorrenteLancamento
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function registrarLancamento(array $dados, int $empresaId): ContaCorrenteLancamento
    {   

        // **MELHORIA:** Validação inicial dos dados de entrada
        if (empty($dados['conta_corrente_id']) || empty($dados['tipo']) || !isset($dados['valor'])) {
            throw new InvalidArgumentException('Os campos conta_corrente_id, tipo e valor são obrigatórios.');
        }

        if (!is_numeric($dados['valor']) || $dados['valor'] <= 0) {
            throw new InvalidArgumentException('O valor do lançamento deve ser um número positivo.');
        }


        $tipo = $dados['tipo'];
        if ($tipo !== 'saida' && $tipo !== 'entrada') { // Ou LancamentoTipo::SAIDA->value
            throw new InvalidArgumentException("Tipo de lançamento inválido: '{$tipo}'. Use 'entrada' ou 'saida'.");
        }

        DB::beginTransaction();

        try {
            
            $conta = ContaCorrente::lockForUpdate()->findOrFail($dados['conta_corrente_id']);

            if ($tipo === 'saida') {
                if ($conta->saldo < $dados['valor']) {
                    throw new InvalidArgumentException('Saldo insuficiente na conta corrente.');
                }
                $conta->saldo -= $dados['valor'];
            } else { 
                $conta->saldo += $dados['valor'];
            }

            $conta->save();

            
            $dadosParaCriar = [
                'empresa_id'   => $empresaId,
                'conta_corrente_id'     => $conta->id,
                'valor'        => $dados['valor'],
                'tipo'         => $tipo,
                'descricao'    => $dados['descricao'] ?? null,
                'data'         => $dados['data'] ?? Carbon::now(),
                'status'       => $dados['status'] ?? 'finalizado',
                'observacao'   => $dados['observacao'] ?? null,
                
            ];

            $lancamento = ContaCorrenteLancamento::create($dadosParaCriar);

            DB::commit();

            return $lancamento;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
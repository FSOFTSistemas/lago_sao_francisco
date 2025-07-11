<?php

namespace App\Services;

use App\Models\Caixa;
use App\Models\FluxoCaixa;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use InvalidArgumentException;

class CaixaService
{
    public function abrirCaixa(Caixa $caixa, float $valorInicial, string $descricao = 'Abertura de Caixa')
    {
        return DB::transaction(function () use ($caixa, $valorInicial, $descricao) {
            $caixa->update([
                'status' => 'aberto',
                'valor_inicial' => $valorInicial,
                'data_abertura' => Carbon::now(),
                'usuario_abertura_id' => Auth::id(),
            ]);

            $movimentoId = Movimento::where('descricao', 'abertura de caixa')->value('id');
            $planoId = PlanoDeConta::where('descricao', 'Abertura/Fechamento de caixa')->value('id');

            FluxoCaixa::create([
                'descricao' => $descricao,
                'valor' => $valorInicial,
                'data' => Carbon::now(),
                'tipo' => 'abertura',
                'caixa_id' => $caixa->id,
                'usuario_id' => Auth::id(),
                'empresa_id' => $caixa->empresa_id,
                'movimento_id' => $movimentoId,
                'valor_total' => $valorInicial,
                'plano_de_conta_id' => $planoId,
            ]);

            return $caixa->refresh();
        });
    }

    public function fecharCaixa(Caixa $caixa, float $valorFinal, string $descricao = 'Fechamento de Caixa')
    {
        return DB::transaction(function () use ($caixa, $valorFinal, $descricao) {
            $caixa->update([
                'status' => 'fechado',
                'valor_final' => $valorFinal,
                'data_fechamento' => Carbon::now(),
                'usuario_fechamento_id' => Auth::id(),
            ]);

            $movimentoId = Movimento::where('descricao', 'fechamento de caixa')->value('id');
            $planoId = PlanoDeConta::where('descricao', 'Abertura/Fechamento de caixa')->value('id');
            FluxoCaixa::create([
                'descricao' => $descricao,
                'valor' => $valorFinal,
                'data' => Carbon::now(),
                'tipo' => 'fechamento',
                'caixa_id' => $caixa->id,
                'usuario_id' => Auth::id(),
                'empresa_id' => $caixa->empresa_id,
                'movimento_id' => $movimentoId,
                'valor_total' => $valorFinal,
                'plano_de_conta_id' => $planoId,
            ]);

            return $caixa->refresh();
        });
    }

    public function inserirMovimentacao(Caixa $caixa, array $dados)
{
    try {
        // Ajusta dados obrigatórios
        $dados['caixa_id'] = $caixa->id;
        $dados['usuario_id'] = Auth::id();
        $dados['empresa_id'] = $caixa->empresa_id;
        $dados['data'] = $dados['data'] ?? Carbon::now();

        // Validação do saldo para movimentação do tipo saida
        if (isset($dados['tipo']) && $dados['tipo'] === 'saida') {
            $saldoAtual = $this->saldoAtual($caixa);
            $valor = $dados['valor'] ?? 0;

            if ($valor > $saldoAtual) {
                throw new InvalidArgumentException(
                    'Valor da saida não pode ser maior que o saldo atual do caixa (R$ ' .
                    number_format($saldoAtual, 2, ',', '.') . ').'
                );
            }
        }

        return FluxoCaixa::create($dados);

    } catch (\InvalidArgumentException $e) {
        throw $e;
    } catch (\Throwable $e) {
        \Log::error('Erro ao inserir movimentação no caixa: ' . $e->getMessage());
        throw new \Exception('Erro inesperado ao registrar a movimentação no caixa.');
    }
}


    public function removerMovimentacao(FluxoCaixa $movimentacao)
    {
        return $movimentacao->delete();
    }

    public function saldoAtual(Caixa $caixa): float
{
    $fluxos = FluxoCaixa::where('caixa_id', $caixa->id)->get();

    $saldo = $fluxos->where('tipo', 'entrada')->sum('valor') 
           - $fluxos->where('tipo', 'saida')->sum('valor');

    return $saldo;
}
}

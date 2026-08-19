<?php

namespace App\Services;

use App\Models\Excursao;
use InvalidArgumentException;

class ExcursaoFinanceiroService
{
    private const TOLERANCIA_QUITACAO_CENTAVOS = 1;

    /**
     * @param  array<string, mixed>  $dados
     * @param  iterable<mixed>  $recebimentos
     * @return array<string, float|bool>
     */
    public function calcular(array $dados, iterable $recebimentos = []): array
    {
        $qtdPessoas = $this->quantidade($dados['qtd_pessoas'] ?? 0, 'quantidade de pessoas');
        $qtdAlmoco = $this->quantidade($dados['qtd_almoco'] ?? 0, 'quantidade de almoços');
        $valorPessoa = $this->centavos($dados['valor_pessoa'] ?? 0, 'valor por pessoa');
        $valorAlmoco = $this->centavos($dados['valor_almoco'] ?? 0, 'valor do almoço');
        $acrescimo = $this->centavos($dados['acrescimo'] ?? 0, 'acréscimo');
        $desconto = $this->centavos($dados['desconto'] ?? 0, 'desconto');
        $percentualComissao = $this->percentual($dados['percentual_comissao'] ?? 10);

        $valorPessoas = $valorPessoa * $qtdPessoas;
        $totalAlmoco = $valorAlmoco * $qtdAlmoco;
        $subtotal = $valorPessoas + $totalAlmoco;

        if ($desconto > $subtotal + $acrescimo) {
            throw new InvalidArgumentException('O desconto não pode ser maior que o subtotal somado ao acréscimo.');
        }

        $total = $subtotal + $acrescimo - $desconto;

        if ($total <= 0) {
            throw new InvalidArgumentException('O total da excursão deve ser maior que zero.');
        }

        $valorComissao = (int) round($valorPessoas * ($percentualComissao / 100));
        $receitaLiquida = $total - $valorComissao;
        $valorPago = $this->somarRecebimentos($recebimentos);
        $valorRestante = max($total - $valorPago, 0);

        return [
            'valor_pessoas' => $this->reais($valorPessoas),
            'total_almoco' => $this->reais($totalAlmoco),
            'subtotal' => $this->reais($subtotal),
            'total' => $this->reais($total),
            'valor_comissao' => $this->reais($valorComissao),
            'receita_liquida' => $this->reais($receitaLiquida),
            'valor_pago' => $this->reais($valorPago),
            'valor_restante' => $this->reais($valorRestante),
            'quitada' => $valorRestante <= self::TOLERANCIA_QUITACAO_CENTAVOS,
        ];
    }

    /** @return array<string, float|bool> */
    public function calcularParaExcursao(Excursao $excursao): array
    {
        $recebimentos = $excursao->relationLoaded('recebimentos')
            ? $excursao->recebimentos
            : $excursao->recebimentos()->get(['valor']);

        return $this->calcular([
            'qtd_pessoas' => $excursao->qtd_pessoas,
            'valor_pessoa' => $excursao->valor_pessoa,
            'percentual_comissao' => $excursao->percentual_comissao,
            'valor_almoco' => $excursao->valor_almoco,
            'qtd_almoco' => $excursao->qtd_almoco,
            'acrescimo' => $excursao->acrescimo,
            'desconto' => $excursao->desconto,
        ], $recebimentos);
    }

    private function quantidade(mixed $valor, string $campo): int
    {
        if (filter_var($valor, FILTER_VALIDATE_INT) === false || (int) $valor < 0) {
            throw new InvalidArgumentException("A {$campo} deve ser um número inteiro não negativo.");
        }

        return (int) $valor;
    }

    private function centavos(mixed $valor, string $campo): int
    {
        if (! is_numeric($valor) || (float) $valor < 0) {
            throw new InvalidArgumentException("O campo {$campo} deve ser um valor não negativo.");
        }

        return (int) round((float) $valor * 100);
    }

    private function percentual(mixed $valor): float
    {
        if (! is_numeric($valor) || (float) $valor < 0 || (float) $valor > 100) {
            throw new InvalidArgumentException('O percentual de comissão deve estar entre 0 e 100.');
        }

        return round((float) $valor, 2);
    }

    private function somarRecebimentos(iterable $recebimentos): int
    {
        $total = 0;

        foreach ($recebimentos as $recebimento) {
            $valor = is_numeric($recebimento)
                ? $recebimento
                : data_get($recebimento, 'valor', 0);
            $total += $this->centavos($valor, 'valor do recebimento');
        }

        return $total;
    }

    private function reais(int $centavos): float
    {
        return round($centavos / 100, 2);
    }
}

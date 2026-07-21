<?php

namespace App\Http\Controllers;

use App\Models\ContasAReceber;
use App\Models\Reserva;
use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceiroProjecaoController extends Controller
{
    private const MESES_ABREV = [
        '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
        '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
        '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez',
    ];

    public function index(Request $request)
    {
        $dataInicio = Carbon::parse($request->input('data_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay();
        $dataFim = Carbon::parse($request->input('data_fim', now()->endOfMonth()->format('Y-m-d')))->endOfDay();
        $hoje = Carbon::today();

        $usuario = Auth::user();
        $empresaSelecionada = session('empresa_id');

        // --- Reservas (Transacao) ---
        $transacoesPeriodo = Transacao::with(['formaPagamento', 'reserva.hospede'])
            ->where('tipo', 'pagamento')
            ->where('status', true)
            ->whereBetween('data_pagamento', [$dataInicio->toDateString(), $dataFim->toDateString()])
            ->get();

        $reservasRecebido = $transacoesPeriodo->filter(fn ($t) => Carbon::parse($t->data_pagamento)->lte($hoje));
        $reservasProjetado = $transacoesPeriodo->filter(fn ($t) => Carbon::parse($t->data_pagamento)->gt($hoje));

        // Saldo em aberto de reservas: cobre o saldo que falta pagar mesmo quando
        // não há nenhuma parcela futura cadastrada para ele (valor_total - já pago - descontos),
        // atribuído à data de check-out (quando o hóspede normalmente acerta a conta).
        $saldosReservas = collect();

        $reservasEmAberto = Reserva::whereNotIn('situacao', ['cancelado', 'bloqueado', 'noshow'])
            ->whereBetween('data_checkout', [$dataInicio->toDateString(), $dataFim->toDateString()])
            ->with(['hospede', 'quarto', 'transacoes' => fn ($q) => $q->where('status', true)])
            ->get();

        foreach ($reservasEmAberto as $reserva) {
            $pagoAteHoje = $reserva->transacoes->where('tipo', 'pagamento')
                ->filter(fn ($t) => Carbon::parse($t->data_pagamento)->lte($hoje))
                ->sum('valor');
            $descontos = $reserva->transacoes->where('tipo', 'desconto')->sum('valor');
            $agendadoFuturo = $reserva->transacoes->where('tipo', 'pagamento')
                ->filter(fn ($t) => Carbon::parse($t->data_pagamento)->gt($hoje))
                ->sum('valor');

            $saldoTotal = round((float) $reserva->valor_total - $pagoAteHoje - $descontos, 2);
            $naoAgendado = round(max(0, $saldoTotal - $agendadoFuturo), 2);

            if ($naoAgendado > 0.01) {
                $checkout = Carbon::parse($reserva->data_checkout);

                $saldosReservas->push([
                    'descricao' => 'FA:'.str_pad($reserva->id, 6, '0', STR_PAD_LEFT).' - '.($reserva->quarto->nome ?? '-'),
                    'cliente' => $reserva->hospede->nome ?? '-',
                    'vencimento' => $checkout,
                    'status' => $checkout->lt($hoje) ? 'atrasado' : 'pendente',
                    'valor' => $naoAgendado,
                ]);
            }
        }

        $reservasSaldoPendente = round((float) $saldosReservas->where('status', 'pendente')->sum('valor'), 2);
        $reservasSaldoAtrasado = round((float) $saldosReservas->where('status', 'atrasado')->sum('valor'), 2);

        // --- Contas a Receber (aluguel de espaço, lançamentos avulsos) ---
        $contasQuery = $empresaSelecionada === null
            ? ContasAReceber::query()
            : ContasAReceber::where('empresa_id', $usuario->hasRole('Master') ? $empresaSelecionada : $usuario->empresa_id);

        $contasPeriodo = (clone $contasQuery)
            ->where(function ($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('data_vencimento', [$dataInicio->toDateString(), $dataFim->toDateString()])
                    ->orWhereBetween('data_recebimento', [$dataInicio->toDateString(), $dataFim->toDateString()]);
            })
            ->with('cliente')
            ->get();

        $carRecebido = round((float) $contasPeriodo->where('status', 'recebido')->sum('valor_recebido'), 2);
        $carPendente = round((float) $contasPeriodo->where('status', 'pendente')->sum(fn ($c) => $c->valor - ($c->valor_recebido ?? 0)), 2);
        $carAtrasado = round((float) $contasPeriodo->where('status', 'atrasado')->sum(fn ($c) => $c->valor - ($c->valor_recebido ?? 0)), 2);

        $totalReservasRecebido = round((float) $reservasRecebido->sum('valor'), 2);
        $totalReservasProjetado = round((float) $reservasProjetado->sum('valor'), 2);

        $totalRecebido = $totalReservasRecebido + $carRecebido;
        $totalAReceber = $totalReservasProjetado + $carPendente + $reservasSaldoPendente;
        $totalAtrasado = $carAtrasado + $reservasSaldoAtrasado;
        $totalProjetado = $totalRecebido + $totalAReceber + $totalAtrasado;

        $qtdPendentes = $contasPeriodo->where('status', 'pendente')->count()
            + $reservasProjetado->count()
            + $saldosReservas->where('status', 'pendente')->count();
        $qtdAtrasados = $contasPeriodo->where('status', 'atrasado')->count()
            + $saldosReservas->where('status', 'atrasado')->count();

        // --- Recebido por forma de pagamento (reservas) ---
        $porFormaPagamento = $reservasRecebido
            ->groupBy(fn ($t) => $t->formaPagamento->descricao ?? 'Outro')
            ->map(fn ($grupo) => round((float) $grupo->sum('valor'), 2))
            ->sortDesc();

        $formaPagamentoTop = $porFormaPagamento->take(6);
        $formaPagamentoOutros = round((float) $porFormaPagamento->slice(6)->sum(), 2);
        if ($formaPagamentoOutros > 0) {
            $formaPagamentoTop->put('Outros', $formaPagamentoOutros);
        }

        // --- Série por período (recebido vs projetado) ---
        $granularidade = $this->definirGranularidade($dataInicio, $dataFim);
        $buckets = [];

        $adicionarAoBucket = function (Carbon $data, string $tipo, float $valor) use (&$buckets, $granularidade) {
            [$chave, $rotulo] = $this->chaveBucket($data, $granularidade);
            if (! isset($buckets[$chave])) {
                $buckets[$chave] = ['label' => $rotulo, 'recebido' => 0.0, 'projetado' => 0.0];
            }
            $buckets[$chave][$tipo] += $valor;
        };

        foreach ($reservasRecebido as $t) {
            $adicionarAoBucket(Carbon::parse($t->data_pagamento), 'recebido', (float) $t->valor);
        }
        foreach ($reservasProjetado as $t) {
            $adicionarAoBucket(Carbon::parse($t->data_pagamento), 'projetado', (float) $t->valor);
        }
        foreach ($saldosReservas as $s) {
            $adicionarAoBucket($s['vencimento'], 'projetado', (float) $s['valor']);
        }
        foreach ($contasPeriodo as $c) {
            if ($c->status === 'recebido' && $c->data_recebimento) {
                $adicionarAoBucket(Carbon::parse($c->data_recebimento), 'recebido', (float) $c->valor_recebido);
            } elseif (in_array($c->status, ['pendente', 'atrasado'])) {
                $adicionarAoBucket(Carbon::parse($c->data_vencimento), 'projetado', (float) ($c->valor - ($c->valor_recebido ?? 0)));
            }
        }
        ksort($buckets);

        $serieLabels = array_map(fn ($b) => $b['label'], $buckets);
        $serieRecebido = array_map(fn ($b) => round($b['recebido'], 2), $buckets);
        $serieProjetado = array_map(fn ($b) => round($b['projetado'], 2), $buckets);

        // --- Próximos recebimentos ---
        $proximos = collect();

        foreach ($contasPeriodo->whereIn('status', ['pendente', 'atrasado']) as $c) {
            $proximos->push([
                'descricao' => $c->descricao,
                'cliente' => $c->cliente->nome_razao_social ?? '-',
                'vencimento' => Carbon::parse($c->data_vencimento),
                'status' => $c->status,
                'valor' => $c->valor - ($c->valor_recebido ?? 0),
            ]);
        }

        foreach ($reservasProjetado as $t) {
            $proximos->push([
                'descricao' => $t->descricao,
                'cliente' => $t->reserva->hospede->nome ?? '-',
                'vencimento' => Carbon::parse($t->data_pagamento),
                'status' => 'pendente',
                'valor' => (float) $t->valor,
            ]);
        }

        foreach ($saldosReservas as $s) {
            $proximos->push($s);
        }

        $proximos = $proximos->sortBy('vencimento')->take(15)->values();

        return view('financeiro.projecao', [
            'dataInicio' => $dataInicio->format('Y-m-d'),
            'dataFim' => $dataFim->format('Y-m-d'),
            'totalRecebido' => $totalRecebido,
            'totalAReceber' => $totalAReceber,
            'totalAtrasado' => $totalAtrasado,
            'totalProjetado' => $totalProjetado,
            'totalReservasRecebido' => $totalReservasRecebido,
            'carRecebido' => $carRecebido,
            'qtdPendentes' => $qtdPendentes,
            'qtdAtrasados' => $qtdAtrasados,
            'formaPagamentoLabels' => $formaPagamentoTop->keys()->values(),
            'formaPagamentoValores' => $formaPagamentoTop->values()->values(),
            'serieLabels' => array_values($serieLabels),
            'serieRecebido' => array_values($serieRecebido),
            'serieProjetado' => array_values($serieProjetado),
            'proximos' => $proximos,
        ]);
    }

    private function definirGranularidade(Carbon $inicio, Carbon $fim): string
    {
        $dias = $inicio->diffInDays($fim);

        if ($dias <= 45) {
            return 'semana';
        }

        if ($dias <= 366) {
            return 'mes';
        }

        return 'ano';
    }

    private function chaveBucket(Carbon $data, string $granularidade): array
    {
        if ($granularidade === 'semana') {
            $inicioSemana = $data->copy()->startOfWeek();

            return [$inicioSemana->format('Y-m-d'), $inicioSemana->format('d/m')];
        }

        if ($granularidade === 'mes') {
            return [$data->format('Y-m'), self::MESES_ABREV[$data->format('m')].'/'.$data->format('y')];
        }

        return [$data->format('Y'), $data->format('Y')];
    }
}

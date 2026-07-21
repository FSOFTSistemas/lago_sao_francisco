@extends('adminlte::page')

@section('title', 'Projeção Financeira')

@section('content_header')
    <h1>Projeção Financeira</h1>
@stop

@section('css')
    <style>
        .fp-kpi { border-radius: .35rem; padding: 14px 16px; height: 100%; }
        .fp-kpi .fp-label { font-size: .78rem; color: #6c757d; margin-bottom: 4px; }
        .fp-kpi .fp-value { font-size: 1.5rem; font-weight: 700; }
        .fp-kpi .fp-sub { font-size: .75rem; color: #98a0a8; margin-top: 2px; }
        .fp-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .fp-preset { cursor: pointer; }
        .fp-preset.active { background: #2a78d6 !important; color: #fff !important; border-color: #2a78d6 !important; }
        .fp-statusbar { display: flex; height: 26px; border-radius: .35rem; overflow: hidden; margin-bottom: 10px; }
        .fp-statusbar > div { display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; color: #fff; }
        .fp-status-legend span { font-size: .8rem; color: #6c757d; margin-right: 18px; }
        .fp-note {
            background: #eef4fc; border-left: 4px solid #2a78d6; border-radius: .35rem;
            padding: 10px 14px; font-size: .82rem; color: #495057; margin-bottom: 18px;
        }
        .badge-pendente { background: rgba(250,178,25,.18); color: #a67300; }
        .badge-atrasado { background: rgba(208,59,59,.15); color: #d03b3b; }
    </style>
@stop

@section('content')

    <div class="fp-note">
        <strong>Sobre os dados:</strong> "Recebido" e "A receber" somam reservas + aluguel de espaço + lançamentos
        avulsos do Contas a Receber. O gráfico "por forma de pagamento" reflete apenas pagamentos de
        <strong>reservas</strong>, que é a única origem que registra a forma de pagamento hoje.
    </div>

    <form method="GET" action="{{ route('financeiro.projecao') }}" class="mb-4" id="form-filtro-projecao">
        <div class="d-flex flex-wrap align-items-end" style="gap: 10px;">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm fp-preset" data-preset="hoje">Hoje</button>
                <button type="button" class="btn btn-outline-secondary btn-sm fp-preset" data-preset="7dias">7 dias</button>
                <button type="button" class="btn btn-outline-secondary btn-sm fp-preset" data-preset="mes">Este mês</button>
                <button type="button" class="btn btn-outline-secondary btn-sm fp-preset" data-preset="30dias">Próx. 30 dias</button>
            </div>
            <div>
                <label for="data_inicio" class="mb-0 small">De</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" value="{{ $dataInicio }}">
            </div>
            <div>
                <label for="data_fim" class="mb-0 small">Até</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" value="{{ $dataFim }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>
    </form>

    <div class="row">
        <div class="col-lg-3 col-6 mb-3">
            <div class="fp-kpi bg-white border">
                <div class="fp-label"><span class="fp-dot" style="background:#0ca30c"></span>Recebido no período</div>
                <div class="fp-value text-success">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</div>
                <div class="fp-sub">reservas: R$ {{ number_format($totalReservasRecebido, 2, ',', '.') }} · outros: R$ {{ number_format($carRecebido, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="fp-kpi bg-white border">
                <div class="fp-label"><span class="fp-dot" style="background:#fab219"></span>A receber no período</div>
                <div class="fp-value" style="color:#a67300">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</div>
                <div class="fp-sub">{{ $qtdPendentes }} lançamento(s) pendente(s)</div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="fp-kpi bg-white border">
                <div class="fp-label"><span class="fp-dot" style="background:#d03b3b"></span>Em atraso</div>
                <div class="fp-value text-danger">R$ {{ number_format($totalAtrasado, 2, ',', '.') }}</div>
                <div class="fp-sub">{{ $qtdAtrasados }} lançamento(s) vencido(s)</div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="fp-kpi bg-white border">
                <div class="fp-label"><span class="fp-dot" style="background:#2a78d6"></span>Projetado no período</div>
                <div class="fp-value" style="color:#2a78d6">R$ {{ number_format($totalProjetado, 2, ',', '.') }}</div>
                <div class="fp-sub">recebido + a receber + atrasado</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Recebido vs. projetado</h3>
                </div>
                <div class="card-body">
                    @if (count($serieLabels) > 0)
                        <canvas id="graficoSerie" height="110"></canvas>
                    @else
                        <p class="text-muted mb-0">Nenhum dado no período selecionado.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Recebido por forma de pagamento</h3>
                    <p class="text-muted mb-0" style="font-size:.75rem">Reservas · período selecionado</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if ($formaPagamentoLabels->count() > 0)
                        <canvas id="graficoFormaPagamento" height="180"></canvas>
                    @else
                        <p class="text-muted mb-0">Nenhum recebimento de reserva no período.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Situação no período</h3>
            <p class="text-muted mb-0" style="font-size:.75rem">Total projetado: R$ {{ number_format($totalProjetado, 2, ',', '.') }}</p>
        </div>
        <div class="card-body">
            @php
                $pctRecebido = $totalProjetado > 0 ? round($totalRecebido / $totalProjetado * 100, 1) : 0;
                $pctAReceber = $totalProjetado > 0 ? round($totalAReceber / $totalProjetado * 100, 1) : 0;
                $pctAtrasado = $totalProjetado > 0 ? max(0, 100 - $pctRecebido - $pctAReceber) : 0;
            @endphp
            @if ($totalProjetado > 0)
                <div class="fp-statusbar">
                    @if ($pctRecebido > 0)
                        <div style="width:{{ $pctRecebido }}%; background:#0ca30c;">{{ $pctRecebido }}%</div>
                    @endif
                    @if ($pctAReceber > 0)
                        <div style="width:{{ $pctAReceber }}%; background:#fab219; color:#3a2a00;">{{ $pctAReceber }}%</div>
                    @endif
                    @if ($pctAtrasado > 0)
                        <div style="width:{{ $pctAtrasado }}%; background:#d03b3b;">{{ $pctAtrasado }}%</div>
                    @endif
                </div>
                <div class="fp-status-legend">
                    <span><span class="fp-dot" style="background:#0ca30c"></span>Recebido — R$ {{ number_format($totalRecebido, 2, ',', '.') }}</span>
                    <span><span class="fp-dot" style="background:#fab219"></span>A receber — R$ {{ number_format($totalAReceber, 2, ',', '.') }}</span>
                    <span><span class="fp-dot" style="background:#d03b3b"></span>Atrasado — R$ {{ number_format($totalAtrasado, 2, ',', '.') }}</span>
                </div>
            @else
                <p class="text-muted mb-0">Nenhum valor projetado no período selecionado.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Próximos recebimentos</h3>
            <p class="text-muted mb-0" style="font-size:.75rem">Ordenado por vencimento</p>
        </div>
        <div class="card-body p-0">
            @if ($proximos->count() > 0)
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Cliente</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proximos as $item)
                            <tr>
                                <td>{{ $item['descricao'] }}</td>
                                <td>{{ $item['cliente'] }}</td>
                                <td>{{ $item['vencimento']->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $item['status'] }}">
                                        {{ $item['status'] === 'atrasado' ? 'Atrasado' : 'Pendente' }}
                                    </span>
                                </td>
                                <td class="text-right">R$ {{ number_format($item['valor'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted p-3 mb-0">Nenhum recebimento pendente no período selecionado.</p>
            @endif
        </div>
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.querySelectorAll('.fp-preset').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var hoje = new Date();
                var inicio, fim;

                switch (btn.dataset.preset) {
                    case 'hoje':
                        inicio = fim = hoje;
                        break;
                    case '7dias':
                        inicio = new Date(hoje);
                        inicio.setDate(hoje.getDate() - 6);
                        fim = hoje;
                        break;
                    case 'mes':
                        inicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                        fim = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
                        break;
                    case '30dias':
                        inicio = hoje;
                        fim = new Date(hoje);
                        fim.setDate(hoje.getDate() + 30);
                        break;
                }

                var fmt = function (d) { return d.toISOString().split('T')[0]; };
                document.getElementById('data_inicio').value = fmt(inicio);
                document.getElementById('data_fim').value = fmt(fim);
                document.getElementById('form-filtro-projecao').submit();
            });
        });

        @if (count($serieLabels) > 0)
            new Chart(document.getElementById('graficoSerie').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($serieLabels),
                    datasets: [
                        {
                            label: 'Recebido',
                            data: @json($serieRecebido),
                            backgroundColor: '#2a78d6',
                            borderRadius: 4,
                        },
                        {
                            label: 'Projetado (a vencer)',
                            data: @json($serieProjetado),
                            backgroundColor: '#eda100',
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: (v) => 'R$ ' + v.toLocaleString('pt-BR') },
                        },
                    },
                },
            });
        @endif

        @if ($formaPagamentoLabels->count() > 0)
            new Chart(document.getElementById('graficoFormaPagamento').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($formaPagamentoLabels),
                    datasets: [{
                        data: @json($formaPagamentoValores),
                        backgroundColor: ['#2a78d6', '#1baf7a', '#eda100', '#008300', '#4a3aa7', '#e34948', '#e87ba4'],
                        borderColor: '#fff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.label + ': R$ ' + Number(ctx.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 }),
                            },
                        },
                    },
                },
            });
        @endif
    </script>
@stop

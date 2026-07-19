@extends('adminlte::page')

@section('title', 'Previsão de Movimentação')

@section('content_header')
    <h1>Previsão de Movimentação</h1>
@stop

@section('content')
    @php
        $totalDias = count($linhas);
    @endphp

    <div class="stat-cards-row">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #679A4C, #3e7222);">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totalApto }}</span>
                <span class="stat-label">Acomodações ativas</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4dabf7, #1971c2);">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totais['pct_ocupacao_apto'] }}%</span>
                <span class="stat-label">Ocupação média (Apto)</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ffc078, #e8590c);">
                <i class="fas fa-lock"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totais['bloqueio_apto'] }}</span>
                <span class="stat-label">Diárias bloqueadas</span>
            </div>
        </div>
        <div class="stat-card stat-card-dark">
            <div class="stat-icon" style="background: linear-gradient(135deg, #495057, #212529);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totais['ocupacao_pax'] }}</span>
                <span class="stat-label">Pax·dia no período</span>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-toolbar">
            <form action="{{ route('relatorios.movimentacao') }}" method="GET" class="report-filter-form">
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" name="data_inicio" value="{{ $dataInicio }}">
                </div>
                <span class="report-date-sep">até</span>
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" name="data_fim" value="{{ $dataFim }}">
                </div>
                <button type="submit" class="btn btn-report-filter">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </form>

            <a href="{{ route('relatorios.movimentacao.pdf', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}"
               target="_blank"
               class="btn btn-report-pdf">
                <i class="fas fa-file-pdf"></i> Imprimir PDF
            </a>
        </div>

        <div class="report-table-wrap">
            <table class="report-table report-table-movimentacao">
                <thead>
                    <tr>
                        <th rowspan="2">Data</th>
                        <th colspan="2">Saídas</th>
                        <th colspan="2">Entradas</th>
                        <th colspan="2">Ocupação</th>
                        <th colspan="3">Crianças</th>
                        <th colspan="2">Café</th>
                        <th colspan="2">Reservas Bloqueio</th>
                        <th rowspan="2">Disp.</th>
                        <th colspan="2">% Ocupação</th>
                    </tr>
                    <tr>
                        <th class="text-center">Apto</th>
                        <th class="text-center">Pax</th>
                        <th class="text-center">Apto</th>
                        <th class="text-center">Pax</th>
                        <th class="text-center">Apto</th>
                        <th class="text-center">Pax</th>
                        <th class="text-center">Pag</th>
                        <th class="text-center">Free</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Adl</th>
                        <th class="text-center">CHD</th>
                        <th class="text-center">Apto</th>
                        <th class="text-center">Pax</th>
                        <th class="text-center">Apto</th>
                        <th class="text-center">Pax</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($linhas as $linha)
                        <tr>
                            <td>
                                <strong>{{ ucfirst($linha['data']->translatedFormat('D')) }}</strong>
                                {{ $linha['data']->format('d/m/Y') }}
                            </td>
                            <td class="text-center">{{ $linha['saidas_apto'] }}</td>
                            <td class="text-center">{{ $linha['saidas_pax'] }}</td>
                            <td class="text-center">{{ $linha['entradas_apto'] }}</td>
                            <td class="text-center">{{ $linha['entradas_pax'] }}</td>
                            <td class="text-center">{{ $linha['ocupacao_apto'] }}</td>
                            <td class="text-center">{{ $linha['ocupacao_pax'] }}</td>
                            <td class="text-center">{{ $linha['criancas_pag'] }}</td>
                            <td class="text-center">{{ $linha['criancas_free'] }}</td>
                            <td class="text-center">{{ $linha['criancas_total'] }}</td>
                            <td class="text-center">{{ $linha['cafe_adl'] }}</td>
                            <td class="text-center">{{ $linha['cafe_chd'] }}</td>
                            <td class="text-center">{{ $linha['bloqueio_apto'] }}</td>
                            <td class="text-center">{{ $linha['bloqueio_pax'] }}</td>
                            <td class="text-center">
                                <span class="report-number-badge">{{ $linha['disp_apto'] }}</span>
                            </td>
                            <td class="text-center">{{ $linha['pct_ocupacao_apto'] }}%</td>
                            <td class="text-center">{{ $linha['pct_ocupacao_pax'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17">
                                <div class="report-empty">
                                    <i class="fas fa-calendar-times"></i>
                                    <h5>Nenhum dado para este período</h5>
                                    <p>Ajuste o filtro de datas acima.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($totalDias > 0)
                    <tfoot>
                        <tr>
                            <td class="font-weight-bold">Total geral</td>
                            <td class="text-center font-weight-bold">{{ $totais['saidas_apto'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['saidas_pax'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['entradas_apto'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['entradas_pax'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['ocupacao_apto'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['ocupacao_pax'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['criancas_pag'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['criancas_free'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['criancas_total'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['cafe_adl'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['cafe_chd'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['bloqueio_apto'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['bloqueio_pax'] }}</td>
                            <td class="text-center font-weight-bold">{{ $totais['disp_apto'] }}</td>
                            <td class="text-center font-weight-bold" style="color: #3e7222;">{{ $totais['pct_ocupacao_apto'] }}%</td>
                            <td class="text-center font-weight-bold" style="color: #3e7222;">{{ $totais['pct_ocupacao_pax'] }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@stop

@section('css')
<style>
    /* Header de duas linhas: desativa o sticky genérico (evita sobreposição das duas linhas) */
    .report-table-movimentacao thead th {
        position: static;
        text-align: center;
    }
    .report-table-movimentacao thead tr:first-child th {
        background: #eef3ea;
        color: #3e7222;
    }
    .report-table-movimentacao tbody td,
    .report-table-movimentacao tfoot td {
        white-space: nowrap;
    }
</style>
@stop

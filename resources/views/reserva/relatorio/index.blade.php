@extends('adminlte::page')

@section('title', 'Relatórios por Período')

@section('content_header')
<h1>Relatórios Gerenciais</h1>
@stop

@section('content')

    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-users"></i> Totais de Hóspedes e Pets
        </div>
        <div class="report-toolbar">
            <form id="form-filtro-hospedes" method="GET" action="{{ route('relatorio.hospedes.filtrar') }}" class="report-filter-form">
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" id="data_inicio_h" name="data_inicio" value="{{ request('data_inicio', date('Y-m-01')) }}" required>
                </div>
                <span class="report-date-sep">até</span>
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" id="data_fim_h" name="data_fim" value="{{ request('data_fim', date('Y-m-d')) }}" required>
                </div>
                <button type="submit" class="btn btn-report-filter">
                    <i class="fas fa-search"></i> Ver Totais de Hóspedes
                </button>
            </form>

            @if(isset($hospedes))
                <a href="{{ route('relatorio.hospedes.pdf', ['data_inicio' => request('data_inicio'), 'data_fim' => request('data_fim')]) }}"
                    target="_blank" class="btn btn-report-pdf">
                    <i class="fas fa-file-pdf"></i> Imprimir PDF
                </a>
            @endif
        </div>

        @if(isset($hospedes))
            <div class="p-3">
                <div class="stat-cards-row">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4dabf7, #1971c2);">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-body">
                            <span class="stat-number">{{ $hospedes->adultos }}</span>
                            <span class="stat-label">Adultos</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #679A4C, #3e7222);">
                            <i class="fas fa-child"></i>
                        </div>
                        <div class="stat-body">
                            <span class="stat-number">{{ $hospedes->criancas }}</span>
                            <span class="stat-label">Crianças (Pagantes)</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ffc078, #e8590c);">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div class="stat-body">
                            <span class="stat-number">{{ $hospedes->criancas_np }}</span>
                            <span class="stat-label">Crianças (Não Pag.)</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #b197fc, #6f42c1);">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="stat-body">
                            <span class="stat-number">{{ $hospedes->pets }}</span>
                            <span class="stat-label">Pets</span>
                        </div>
                    </div>
                    <div class="stat-card stat-card-dark">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #495057, #212529);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-body">
                            <span class="stat-number">{{ $hospedes->total_geral }}</span>
                            <span class="stat-label">Total geral</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-box"></i> Consumo de Produtos por Período
        </div>
        <div class="report-toolbar">
            <form id="form-filtro-produtos" method="GET" action="{{ route('relatorio.produtos.filtrar') }}" class="report-filter-form">
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" id="data_inicio_p" name="data_inicio" value="{{ request('data_inicio', date('Y-m-01')) }}" required>
                </div>
                <span class="report-date-sep">até</span>
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" id="data_fim_p" name="data_fim" value="{{ request('data_fim', date('Y-m-d')) }}" required>
                </div>
                <button type="submit" class="btn btn-report-filter">
                    <i class="fas fa-search"></i> Ver Relatório de Produtos
                </button>
            </form>

            @if(isset($produtos) && count($produtos) > 0)
                <a href="{{ route('relatorio.produtos.pdf', ['data_inicio' => request('data_inicio'), 'data_fim' => request('data_fim')]) }}"
                    target="_blank" class="btn btn-report-pdf">
                    <i class="fas fa-file-pdf"></i> Imprimir PDF
                </a>
            @endif
        </div>

        @if(isset($produtos) && count($produtos) > 0)
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-right">Valor Unitário</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produtos as $produto)
                        <tr>
                            <td>{{ $produto->descricao }}</td>
                            <td class="text-center">
                                <span class="report-number-badge">{{ $produto->quantidade_total }}</span>
                            </td>
                            <td class="text-right">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($produto->total, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold">Total Geral</td>
                            <td class="text-right font-weight-bold" style="color: #3e7222;">R$ {{ number_format($total_geral, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @elseif(isset($produtos))
            <div class="report-empty">
                <i class="fas fa-box-open"></i>
                <h5>Nenhum produto encontrado no período selecionado</h5>
                <p>Ajuste o filtro de datas acima para ver outro intervalo.</p>
            </div>
        @endif
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-calendar-week"></i> Previsão de Movimentação
        </div>
        <div class="report-toolbar">
            <p class="mb-0 text-muted">Grade dia a dia com saídas, entradas, ocupação, crianças, café da manhã, bloqueios e disponibilidade.</p>
            <a href="{{ route('relatorios.movimentacao') }}" class="btn btn-report-filter">
                <i class="fas fa-arrow-right"></i> Abrir Relatório
            </a>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Validação de datas Hóspedes
        $('#form-filtro-hospedes').submit(function(e) {
            const dataInicio = new Date($('#data_inicio_h').val());
            const dataFim = new Date($('#data_fim_h').val());

            if (dataFim < dataInicio) {
                e.preventDefault();
                alert('No filtro de Hóspedes, a data final não pode ser anterior à data inicial.');
                return false;
            }
        });

        // Validação de datas Produtos
        $('#form-filtro-produtos').submit(function(e) {
            const dataInicio = new Date($('#data_inicio_p').val());
            const dataFim = new Date($('#data_fim_p').val());

            if (dataFim < dataInicio) {
                e.preventDefault();
                alert('No filtro de Produtos, a data final não pode ser anterior à data inicial.');
                return false;
            }
        });
    });
</script>
@stop

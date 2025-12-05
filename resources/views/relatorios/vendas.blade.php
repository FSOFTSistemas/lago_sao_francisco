@extends('adminlte::page')

@section('title', 'Relatório de Vendas')

@section('content_header')
    <h1>Relatório de Vendas por Vendedor</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form action="{{ route('relatorios.vendas') }}" method="GET" class="form-inline">
            <div class="form-group mb-2">
                <label class="mr-2">Período de:</label>
                <input type="date" class="form-control" name="data_inicio" value="{{ $dataInicio }}">
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <label class="mr-2">até:</label>
                <input type="date" class="form-control" name="data_fim" value="{{ $dataFim }}">
            </div>
            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
<a href="{{ route('relatorios.vendas.pdf', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" 
   target="_blank" 
   class="btn btn-danger mb-2 ml-3">
    <i class="fas fa-file-pdf"></i> Gerar PDF
</a>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="width: 40%;">Vendedor</th>
                        <th class="text-right">Total Reservas</th>
                        <th class="text-right">Total Day Use</th>
                        <th class="text-right bg-secondary">Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $somaReservas = 0;
                        $somaDayUse = 0;
                        $somaGeral = 0;
                    @endphp

                    @forelse($relatorio as $venda)
                        @php 
                            $somaReservas += $venda['total_reserva'];
                            $somaDayUse += $venda['total_dayuse'];
                            $somaGeral += $venda['total_geral'];
                        @endphp
                        <tr>
                            <td class="align-middle text-uppercase font-weight-bold">
                                {{ $venda['nome'] }}
                            </td>
                            <td class="text-right">
                                R$ {{ number_format($venda['total_reserva'], 2, ',', '.') }}
                            </td>
                            <td class="text-right">
                                R$ {{ number_format($venda['total_dayuse'], 2, ',', '.') }}
                            </td>
                            <td class="text-right font-weight-bold bg-light" style="font-size: 1.1em; color: #28a745;">
                                R$ {{ number_format($venda['total_geral'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3"></i><br>
                                Nenhum venda encontrada neste período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-secondary font-weight-bold">
                        <td class="text-uppercase text-right">Totais do Período:</td>
                        <td class="text-right">R$ {{ number_format($somaReservas, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($somaDayUse, 2, ',', '.') }}</td>
                        <td class="text-right bg-dark">R$ {{ number_format($somaGeral, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle"></i> 
    Este relatório contabiliza vendas pela <strong>Data de Criação</strong> do registro no sistema, excluindo itens cancelados.
</div>
@stop
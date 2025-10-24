@extends('adminlte::page')

@section('title', 'Relatório por Canal de Venda')

@section('content_header')
    <h1>Relatório por Canal de Venda</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reserva.relatorio.canal') }}" class="form-inline">
                <div class="form-group mb-2 mr-sm-2">
                    <label for="data_inicio" class="mr-sm-2">Data Início (Criação):</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="{{ $data_inicio ?? '' }}">
                </div>
                <div class="form-group mb-2 mr-sm-2">
                    <label for="data_fim" class="mr-sm-2">Data Fim (Criação):</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="{{ $data_fim ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Filtrar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Total de Reservas por Canal</h3>
            <div class="card-tools">
                Período: {{ \Carbon\Carbon::parse($data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($data_fim)->format('d/m/Y') }}
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Canal de Venda</th>
                            <th>Quantidade de Reservas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dadosRelatorio as $canal => $total)
                            <tr>
                                <td>{{ $canal }}</td>
                                <td>{{ $total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Nenhum dado encontrado para este período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8f9fa;">
                            <th style="text-align:right">TOTAL GERAL:</th>
                            <th>{{ $dadosRelatorio->sum() }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop
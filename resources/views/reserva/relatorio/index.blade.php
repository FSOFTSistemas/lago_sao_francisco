@extends('adminlte::page')

@section('title', 'Relatório de Produtos por Período')

@section('content_header')
    <h1>Relatório de Produtos por Período</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                    </div>
                    <div class="card-body">
                        <form id="form-filtro" method="GET" action="{{ route('relatorio.produtos.filtrar') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="data_inicio">Data Início</label>
                                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                            value="{{ request('data_inicio', date('Y-m-01')) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="data_fim">Data Fim</label>
                                        <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                            value="{{ request('data_fim', date('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 d-flex align-items-end">
                                    <div class="form-group mb-0 w-100">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search mr-1"></i> Ver Relatório
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($produtos) && count($produtos) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Produtos no Período: {{ \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') }} a {{ \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('relatorio.produtos.pdf', ['data_inicio' => request('data_inicio'), 'data_fim' => request('data_fim')]) }}" 
                                   target="_blank" class="btn btn-sm btn-danger">
                                    <i class="fas fa-file-pdf mr-1"></i> Imprimir
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
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
                                                <td class="text-center">{{ $produto->quantidade_total }}</td>
                                                <td class="text-right">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                                                <td class="text-right">R$ {{ number_format($produto->total, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="3" class="text-right">Total Geral:</td>
                                            <td class="text-right">R$ {{ number_format($total_geral, 2, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(request()->has('data_inicio'))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        Nenhum produto encontrado no período selecionado.
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        @media print {
            .card-tools, .no-print {
                display: none !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Validação de datas
            $('#form-filtro').submit(function(e) {
                const dataInicio = new Date($('#data_inicio').val());
                const dataFim = new Date($('#data_fim').val());
                
                if (dataFim < dataInicio) {
                    e.preventDefault();
                    alert('A data final não pode ser anterior à data inicial.');
                    return false;
                }
            });
        });
    </script>
@stop

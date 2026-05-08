@extends('adminlte::page')

@section('title', 'Relatórios por Período')

@section('content_header')
<h1>Relatórios Gerenciais</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-2"></i> Totais de Hóspedes e Pets</h3>
        </div>
        <div class="card-body">
            <form id="form-filtro-hospedes" method="GET" action="{{ route('relatorio.hospedes.filtrar') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="data_inicio_h">Data Início</label>
                        <input type="date" class="form-control" id="data_inicio_h" name="data_inicio" value="{{ request('data_inicio', date('Y-m-01')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="data_fim_h">Data Fim</label>
                        <input type="date" class="form-control" id="data_fim_h" name="data_fim" value="{{ request('data_fim', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Ver Totais de Hóspedes
                        </button>
                    </div>
                </div>
            </form>

            @if(isset($hospedes))
            <div class="row mt-4">
                <div class="col-12 mb-3 d-flex justify-content-end">
                    <a href="{{ route('relatorio.hospedes.pdf', ['data_inicio' => request('data_inicio'), 'data_fim' => request('data_fim')]) }}"
                        target="_blank" class="btn btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Imprimir Relatório de Hóspedes
                    </a>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Adultos</span>
                            <span class="info-box-number">{{ $hospedes->adultos }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-child"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Crianças (Pagantes)</span>
                            <span class="info-box-number">{{ $hospedes->criancas }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning text-white"><i class="fas fa-baby"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Crianças (Não Pag.)</span>
                            <span class="info-box-number">{{ $hospedes->criancas_np }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-purple text-white"><i class="fas fa-paw"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pets</span>
                            <span class="info-box-number">{{ $hospedes->pets }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-light border">
                        <strong>Total Hóspedes:</strong> {{ $hospedes->total_geral }}
                        @if($hospedes->pets > 0)
                        <br>
                        <small class="text-muted"><strong>Total Pets:</strong> {{ $hospedes->pets }}</small>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <hr class="my-5">

    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-box mr-2"></i> Consumo de Produtos por Período</h3>
        </div>
        <div class="card-body">
            <form id="form-filtro-produtos" method="GET" action="{{ route('relatorio.produtos.filtrar') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="data_inicio_p">Data Início</label>
                        <input type="date" class="form-control" id="data_inicio_p" name="data_inicio" value="{{ request('data_inicio', date('Y-m-01')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="data_fim_p">Data Fim</label>
                        <input type="date" class="form-control" id="data_fim_p" name="data_fim" value="{{ request('data_fim', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-search mr-1"></i> Ver Relatório de Produtos
                        </button>
                    </div>
                </div>
            </form>

            @if(isset($produtos) && count($produtos) > 0)
            <div class="row mt-4">
                <div class="col-12 mb-3 d-flex justify-content-end">
                    <a href="{{ route('relatorio.produtos.pdf', ['data_inicio' => request('data_inicio'), 'data_fim' => request('data_fim')]) }}"
                        target="_blank" class="btn btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Imprimir Relatório de Produtos
                    </a>
                </div>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover border">
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
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-right font-weight-bold">Total Geral:</td>
                                    <td class="text-right font-weight-bold text-success">R$ {{ number_format($total_geral, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @elseif(isset($produtos))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        Nenhum produto encontrado no período selecionado.
                    </div>
                </div>
            </div>
            @endif
        </div>
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
@extends('adminlte::page') @section('title', 'Relatório de Plano de Contas')

@section('content_header')
    <h1>Relatório de Plano de Contas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('plano-de-contas.relatorio') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_inicio">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $dataInicio ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_fim">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $dataFim ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Receitas</h3>
                </div>
                <div class="card-body">
                    <div id="accordion-receitas">
                        @if($receitas && !empty($receitas->filhos))
                            @foreach($receitas->filhos as $conta)
                                @include('relatorios.partials.plano_conta_item', ['conta' => $conta, 'parentId' => 'accordion-receitas'])
                            @endforeach
                        @else
                            <p>Nenhuma receita encontrada.</p>
                        @endif
                    </div>
                    <hr>
                    <h4>Total Receitas: <span class="text-success">R$ {{ number_format($receitas->total_cumulativo ?? 0, 2, ',', '.') }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Despesas</h3>
                </div>
                <div class="card-body">
                    <div id="accordion-despesas">
                         @if($despesas && !empty($despesas->filhos))
                            @foreach($despesas->filhos as $conta)
                                @include('relatorios.partials.plano_conta_item', ['conta' => $conta, 'parentId' => 'accordion-despesas'])
                            @endforeach
                        @else
                            <p>Nenhuma despesa encontrada.</p>
                        @endif
                    </div>
                    <hr>
                    <h4>Total Despesas: <span class="text-danger">R$ {{ number_format(abs($despesas->total_cumulativo ?? 0), 2, ',', '.') }}</span></h4>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    // Para garantir que o accordion funcione corretamente com IDs dinâmicos
    $(function () {
        $('.collapse').on('show.bs.collapse', function () {
            $(this).prev('.card-header').find('.fa').removeClass('fa-plus').addClass('fa-minus');
        }).on('hide.bs.collapse', function () {
            $(this).prev('.card-header').find('.fa').removeClass('fa-minus').addClass('fa-plus');
        });
    });
</script>
@endpush
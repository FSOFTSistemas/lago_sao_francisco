@extends('adminlte::page')

@section('title', 'ContasAReceber')

@section('content_header')
    <h5>Lista de Contas A Receber</h5>
    <hr>
@stop

@section('content')

    <form method="GET" action="{{ route('contasAReceber.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="data_inicio">Data Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                    value="{{ request('data_inicio') }}">
            </div>
            <div class="col-md-3">
                <label for="data_fim">Data Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
            </div>
            <div class="col-md-3">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="recebido" {{ request('status') == 'recebido' ? 'selected' : '' }}>Recebido</option>
                    <option value="atrasado" {{ request('status') == 'atrasado' ? 'selected' : '' }}>Atrasado</option>
                </select>
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('contasAReceber.index') }}" class="btn btn-secondary">Limpar</a>
            </div>
        </div>
        @if(!request()->filled('data_inicio') && !request()->filled('data_fim'))
    <div class="alert alert-info">
        Exibindo contas com vencimento <strong>hoje</strong>. Para ver outros períodos, selecione as datas no filtro acima.
    </div>
@endif
    </form>


    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createContasAReceberModal">
            <i class="fas fa-plus"></i> Nova Conta a Receber
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 2, 'targets' => 3],
            ['responsivePriority' => 2, 'targets' => 4],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Data de Vencimento</th>
                <th>Data de Pagamento</th>
                <th>Valor Parcela</th>
                <th>Situação</th>
                <th>Cliente</th>
                <th>$</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contasAReceber as $conta)
                <tr>
                    <td>{{ $conta->id }}</td>
                    <td>{{ $conta->descricao }}</td>
                    <td>{{ Illuminate\Support\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                    <td>{{ $conta->data_recebimento ? Illuminate\Support\Carbon::parse($conta->data_vencimento)->format('d/m/Y') : '' }}
                    </td>
                    <td>R${{ $conta->valor }}</td>
                    <td>
                        @if ($conta->status == 'pendente')
                            <p>Pendente <i class="fa-solid fa-triangle-exclamation"></i></p>
                        @elseif($conta->status == 'recebido')
                            <p>recebido <i class="fa-regular fa-circle-check"></i></p>
                        @else
                            <p>Atrasado <i class="fa-solid fa-exclamation"></i></p>
                        @endif
                    </td>
                    <td>{{ $conta->cliente->nome_razao_social }}</td>
                    <td>
                        @if ($conta->status != 'recebido')
                            <div class="col">
                                <button class="btn btn-sm btn-success" title="Receber"
                                    onclick="abrirModalReceber({{ $conta->id }}, {{ $conta->valor }}, {{ $conta->valor_recebido ?? 0 }})">
                                    Receber
                                </button>

                            </div>
                        @endif

                    </td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showContasAReceber{{ $conta->id }}">
                            👁️
                        </button>
                        @if($conta->status != 'recebido')
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteContasAReceberModal{{ $conta->id }}">
                            🗑️
                        </button>
                        @endif
                    </td>
                </tr>
                @include('contasAReceber.modals._show', ['contasAReceber' => $conta])
                @include('contasAReceber.modals._edit', ['contasAReceber' => $conta])
                @include('contasAReceber.modals._delete', ['contasAReceber' => $conta])
                @include('contasAReceber.modals._receber')
                <script>
                    function abrirModalReceber(id, valorTotal, valorRecebido = 0) {
                        // Converte valores para float (caso venham como string)
                        const total = parseFloat(valorTotal);
                        const recebido = parseFloat(valorRecebido);
                        const restante = (total - recebido).toFixed(2);

                        // Preenche os campos do modal
                        $('#pagamento_id').val(id);
                        $('#valor_pago').val(restante).attr('max', restante);
                        $('#valor_restante_texto').text('R$ ' + restante.replace('.', ','));
                        $('#valorReceberTexto').text('Valor: R$ ' + total.toFixed(2).replace('.', ','));
                        $('#forma_pagamento').val('');

                        // Abre o modal
                        $('#modalReceberConta').modal('show');
                    }
                </script>
            @endforeach
        </tbody>
    @endcomponent

    @include('contasAReceber.modals._create')
@section('js')
@endsection

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

@stop

@extends('adminlte::page')

@section('title', 'Fluxo de Caixa')

@section('content_header')
    <h5>Fluxo de Caixas</h5>
    <hr>
@stop

@section('content')
    <form method="GET" action="{{ route('fluxoCaixa.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="data_inicio">Data Início</label>
                <input type="date" class="form-control" name="data_inicio" id="data_inicio" value="{{ request('data_inicio', now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label for="data_fim">Data Fim</label>
                <input type="date" class="form-control" name="data_fim" id="data_fim" value="{{ request('data_fim', now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                    <option value="abertura" {{ request('tipo') == 'abertura' ? 'selected' : '' }}>Abertura</option>
                    <option value="fechamento" {{ request('tipo') == 'fechamento' ? 'selected' : '' }}>Fechamento</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createFluxoCaixaModal">
            <i class="fas fa-plus"></i> Novo Fluxo de Caixa
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 3, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 2,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Movimento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fluxoCaixas as $fluxoCaixa)
                <tr>
                    <td>{{ $fluxoCaixa->id }}</td>
                    <td>{{ $fluxoCaixa->descricao }}</td>
                    <td>R${{ $fluxoCaixa->valor }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($fluxoCaixa->data)->format('d/m/Y') }}</td>
                    <td style="color: {{ $fluxoCaixa->tipo === 'entrada' ? 'green' : 'red' }};">
                        {{ $fluxoCaixa->tipo }}
                    </td>
                    <td>{{ $fluxoCaixa->movimento->descricao }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showFluxoCaixa{{ $fluxoCaixa->id }}">
                            👁️
                        </button>

                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                            data-target="#editFluxoCaixaModal{{ $fluxoCaixa->id }}">
                            ✏️
                        </button>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFluxoCaixaModal{{ $fluxoCaixa->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                @include('fluxoCaixa.modals._show', ['fluxoCaixa' => $fluxoCaixa])
                @include('fluxoCaixa.modals._edit', ['fluxoCaixa' => $fluxoCaixa])
                @include('fluxoCaixa.modals._delete', ['fluxoCaixa' => $fluxoCaixa])
            @endforeach
        </tbody>
    @endcomponent

    @include('fluxoCaixa.modals._create')
    @if (session('sweet_error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Atenção!',
                    text: '{{ session('sweet_error') }}',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    @endif
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop

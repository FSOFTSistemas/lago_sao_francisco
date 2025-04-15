@extends('adminlte::page')

@section('title', 'Fluxo de Caixa')

@section('content_header')
    <h5>Fluxo de Caixas</h5>
@stop

@section('content')
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
        'showTotal' => true,
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
                        <td>{{ $fluxoCaixa->tipo }}</td>
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
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop


@section('css')
<style>
    .new {
        background-color: #679A4C !important;
        border: none !important;
    }
    .new:hover{
        background-color: #3e7222 !important;
    }
</style>
@extends('adminlte::page')

@section('title', 'Aluguel')

@section('content_header')
    <h5>Lista de Aluguéis</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('aluguel.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Aluguel
        </a>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 0,
    ])

            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Espaço</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aluguel as $aluguel)
                    <tr>
                        <td>{{ $aluguel->id }}</td>
                        <td>{{ $aluguel->cliente->nome_razao_social }}</td>
                        <td>{{ $aluguel->espaco->nome }}</td>
                        <td>{{ $aluguel->status }}</td>
                        <td>
                            <a href="{{route('aluguel.edit', $aluguel->id)}}" class="btn btn-warning btn-sm">
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteAluguelModal{{ $aluguel->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('aluguel.modals._delete', ['aluguel' => $aluguel])
                @endforeach
            </tbody>
    @endcomponent

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
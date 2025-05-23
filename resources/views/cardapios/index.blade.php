@extends('adminlte::page')

@section('title', 'Cardapio')

@section('content_header')
    <h5>Lista de Cardápios</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('cardapios.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Cardapio
        </a>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 0,
    ])

            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cardapios as $cardapio)
                    <tr>
                        <td>{{ $cardapio->id }}</td>
                        <td>{{ $cardapio->nome }}</td>
                        <td>
                            <a href="{{route('cardapios.edit', $cardapio->id)}}" class="btn btn-warning btn-sm">
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteCardapioModal{{ $cardapio->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('cardapios.modals._delete', ['cardapio' => $cardapio])
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
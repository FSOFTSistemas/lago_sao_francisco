@extends('adminlte::page')

@section('title', 'Itens do Cardápio')

@section('content_header')
    <h5>Lista de Itens</h5>
@stop

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-success float-end new" href="{{ route('itemCardapio.create') }}"><i class="fas fa-plus"></i> Novo Item</a>
</div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome do Item</th>
                    <th>Tipo do Item</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itens as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nome_item }}</td>
                        <td>{{ $item->tipo_item }}</td>
                        <td>

                            <a class="btn btn-warning btn-sm" href="{{route('itemCardapio.edit', $item->id)}}">
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteItemModal{{ $item->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('itens-do-cardapio.modals._edit', ['item' => $item])
                    @include('itens-do-cardapio.modals._delete', ['item' => $item])
                @endforeach
            </tbody>
    @endcomponent

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
@stop
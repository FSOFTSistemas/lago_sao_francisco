@extends('adminlte::page')

@section('title', 'Buffet')

@section('content_header')
    <h5>Lista de Itens do Buffet</h5>
    <hr>
@stop

@section('content')
    <div class="row mb-3">
            <div class="col">
                <a href="{{ route('preferencias') }}" class="btn btn-success new">               
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                </a>
            </div>

            <div class="col d-flex justify-content-end mb-3">
                <a href="{{route('buffet.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Item
        </a>
            </div>
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
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Valor unitário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itens as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nome }}</td>
                        <td>{{ $item->categoria->nome }}</td>
                        <td>R${{ $item->valor_unitario }}</td>
                        <td>
                            <a href="{{route('buffet.edit', $item->id)}}" class="btn btn-warning btn-sm">
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteItemModal{{ $item->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('buffet.modals._delete', ['itens' => $item])
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
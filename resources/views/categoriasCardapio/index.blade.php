@extends('adminlte::page')

@section('title', 'Categoria do Cardápio')

@section('content_header')
    <h5>Lista de Categorias do Cardápio</h5>
    <hr>
@stop

@section('content')
     <div class="row mb-3">
        <div class="col d-flex justify-content-start">
            <a href="{{ route('preferencias') }}" class="btn btn-success new">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="col d-flex justify-content-end">
            <button class="btn btn-success new" data-toggle="modal" data-target="#createCategoriaModal">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
        </div>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],

            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>{{ $categoria->id }}</td>
                        <td>{{ $categoria->nome }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editCategoriaModal{{ $categoria->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteCategoriaModal{{ $categoria->id }}">
                            🗑️
                          </button>
                        </td>
                    </tr>

                    @include('categoriasCardapio.modals._edit', ['categoria' => $categoria])
                    @include('categoriasCardapio.modals._delete', ['categoria' => $categoria])
                @endforeach
            </tbody>
    @endcomponent

    @include('categoriasCardapio.modals._create')
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop

@section('css')


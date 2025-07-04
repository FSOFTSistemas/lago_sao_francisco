@extends('adminlte::page')

@section('title', 'Categorias')

@section('content_header')
    <h5>Lista de Categorias de Parceiros</h5>
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
        
        <button class="btn btn-success new" data-toggle="modal" data-target="#createCategoriaParceiroModal">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
    </div>
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
            <thead class="table-primary">
                <tr>
                    <th>Id</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>{{ $categoria->id }}</td>
                        <td>{{ $categoria->descricao }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editCategoriaParceiroModal{{ $categoria->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteCategoriaParceiroModal{{ $categoria->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('categoriasParceiro.modals.delete', ['categoriasParceiro' => $categoria])
                    @include('categoriasParceiro.modals.edit', ['categoria' => $categoria])
                    @endforeach
                  </tbody>
                  @endcomponent
                  @include('categoriasParceiro.modals._create')
@stop

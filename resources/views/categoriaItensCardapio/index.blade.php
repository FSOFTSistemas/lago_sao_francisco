@extends('adminlte::page')

@section('title', 'Categorias')

@section('content_header')
    <h5>Lista de Categorias de item</h5>
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
        <a href="{{ route('categoriaItensCardapio.create') }}">
            <button type="button" class="btn btn-success new">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
        </a>
    </div>
</div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => 3],
            ['responsivePriority' => 4, 'targets' => 4],
            ['responsivePriority' => 4, 'targets' => 5],
            ['responsivePriority' => 4, 'targets' => 6],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 0,
    ])
            <thead class="table-primary">
                <tr>
                    <th>Id</th>
                    <th>Sessão</th>
                    <th>Refeição Principal</th>
                    <th>Nome</th>
                    <th>Número de escolhas permitidas</th>
                    <th>Ordem exibição</th>
                    <th>É um grupo de escolha exclusiva?</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>{{ $categoria->id }}</td>
                        <td>{{ $categoria->secaoCardapio->nome_secao_cardapio ?? '' }}</td>
                        <td>{{ $categoria->refeicaoPrincipal->NomeOpcaoRefeicao ?? '' }}</td>
                        <td>{{ $categoria->nome_categoria_item }}</td>
                        <td>{{ $categoria->numero_escolhas_permitidas}}</td>
                        <td>{{ $categoria->ordem_exibicao }}</td>
                        <td>{{ $categoria->eh_grupo_escolha_exclusiva }}</td>
                        <td>
                             <a href="{{ route('categoriaItensCardapio.edit', $categoria->id) }}">
                                <button type="button" class="btn btn-warning btn-sm edit-item">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteCategoriaModal{{ $categoria->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

        
            @include('categoriaItensCardapio.modals.delete', ['categoria' => $categoria])
                @endforeach
            </tbody>
    @endcomponent
@stop

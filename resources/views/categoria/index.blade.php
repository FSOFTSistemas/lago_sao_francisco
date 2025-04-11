@extends('adminlte::page')

@section('title', 'Categorias')

@section('content_header')
    <h5>Lista de Categorias</h5>
@stop

@section('content')
<div class="row mb-3 pt-3">
  <div class="col d-flex justify-content-start">
    <a href="{{ route('preferencias') }}" class="btn btn-success">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
  </div>
    <div class="col d-flex justify-content-end">
        <a href="{{route('categoria.create')}}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nova Categoria
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

            <thead class="table-primary">
                <tr>
                    <th>Ativo?</th>
                    <th>Posição</th>
                    <th>Título</th>
                    <th>Locais</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>
                          @if($categoria->status == true)
                          <i class="fa-regular fa-circle-check"></i>
                          @else
                          <i class="fa-regular fa-circle-xmark"></i>
                          @endif
                        </td>
                        <td>{{ $categoria->posicao }}</td>
                        <td>
                          <a id="editlink" href="{{ route('categoria.edit', $categoria->id) }}">
                            {{ $categoria->titulo }}
                          </a>
                        </td>
                        <td>{{ $categoria->quartos_count }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteCategoriaModal{{ $categoria->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('categoria.modals._delete', ['categoria' => $categoria])
                @endforeach
            </tbody>
    @endcomponent
@stop

@section('css')
<style>
    #editlink {
        text-decoration: none
    }
</style>
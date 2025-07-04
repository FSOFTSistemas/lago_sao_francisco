@extends('adminlte::page')

@section('title', 'Quartos')

@section('content_header')
    <h5>Lista de Quartos</h5>
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
        <a href="{{route('quarto.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Quarto
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
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quartos as $quarto)
                    <tr>
                        <td>
                          @if($quarto->status == true)
                          <i class="fa-regular fa-circle-check"></i>
                          @else
                          <i class="fa-regular fa-circle-xmark"></i>
                          @endif
                        </td>
                        <td>{{ $quarto->posicao }}</td>
                        <td>
                          <a id="editlink" href="{{ route('quarto.edit', $quarto->id) }}">
                            {{ $quarto->nome }}
                          </a>
                        </td>
                        <td>{{ $quarto->categoria->titulo }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteQuartoModal{{ $quarto->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('quarto.modals._delete', ['quarto' => $quarto])
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
<style>
    .new {
        background-color: #679A4C !important;
        border: none !important;
    }
    .new:hover{
        background-color: #3e7222 !important;
    }
</style>
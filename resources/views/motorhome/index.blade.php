@extends('adminlte::page')

@section('title', 'Motorhomes')

@section('content_header')
    <h5>Lista de Motorhomes</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('motorhome.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Motorhome
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
                    <th>Ativo?</th>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Cor</th>
                    <th>Proprietário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($motorhomes as $motorhome)
                    <tr>
                        <td>
                          @if($motorhome->status == true)
                          <i class="fa-regular fa-circle-check text-success"></i>
                          @else
                          <i class="fa-regular fa-circle-xmark text-danger"></i>
                          @endif
                        </td>
                        <td>
                          <a id="editlink" href="{{ route('motorhome.edit', $motorhome->id) }}">
                            {{ $motorhome->placa }}
                          </a>
                        </td>
                        <td>{{ $motorhome->modelo }}</td>
                        <td>{{ $motorhome->cor }}</td>
                        <td>{{ $motorhome->proprietario?->nome ?? '' }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteMotorhomeModal{{ $motorhome->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('motorhome.modals._delete', ['motorhome' => $motorhome])
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
@stop

@extends('adminlte::page')

@section('title', 'Diarias')

@section('content_header')
    <h5>Lista de Diárias</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createDiariaModal">
            <i class="fas fa-plus"></i> Nova Diária
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 4, 'targets' => 2],
            ['responsivePriority' => 3, 'targets' => 3],
            ['responsivePriority' => 3, 'targets' => 4],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Quantidade</th>
                    <th>Cliente</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($diarias as $diaria)
                    <tr>
                        <td>{{ $diaria->id }}</td>
                        <td>
                          @if($diaria->tipo == 'day_use')
                              Day Use
                          @elseif($diaria->tipo == 'passaporte')
                              Passaporte
                          @else
                              Tipo não especificado
                          @endif
                      </td>
                      
                        <td>R${{ $diaria->valor }}</td>
                        <td>{{ $diaria->quantidade }}</td>
                        <td>{{ $diaria->cliente->nome_razao_social }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editDiariaModal{{ $diaria->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteDiariaModal{{ $diaria->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('diaria.modals._edit', ['diaria' => $diaria])
                    @include('diaria.modals._delete', ['diaria' => $diaria])
                @endforeach
            </tbody>
    @endcomponent

    @include('diaria.modals._create')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop

@section('css')
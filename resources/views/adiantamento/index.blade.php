@extends('adminlte::page')

@section('title', 'Adiantamentos')

@section('content_header')
    <h5>Lista de Adiantamentos</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createAdiantamentoModal">
            <i class="fas fa-plus"></i> Novo Adiantamento
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
                    <th>Funcionário</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adiantamentos as $adiantamento)
                    <tr>
                        <td>{{ $adiantamento->id }}</td>
                        <td>{{ $adiantamento->funcionario->nome }}</td>
                        <td>R${{ $adiantamento->valor }}</td>
                        <td>{{Illuminate\Support\Carbon::parse($adiantamento->data)->format('d/m/Y')}}</td>
                        <td>
                            @if($adiantamento->status == "finalizado")
                          <p>Finalizado <i class="fa-regular fa-circle-check"></i></p>
                          @else
                          <p>Pendente <i class="fa-solid fa-triangle-exclamation"></i></p>
                          @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showAdiantamento{{ $adiantamento->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editAdiantamentoModal{{ $adiantamento->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteAdiantamentoModal{{ $adiantamento->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('adiantamento.modals._show', ['adiantamento' => $adiantamento])
                    @include('adiantamento.modals._edit', ['adiantamento' => $adiantamento])
                    @include('adiantamento.modals._delete', ['adiantamento' => $adiantamento])
                @endforeach
            </tbody>
    @endcomponent

    @include('adiantamento.modals._create')
@stop

@section('js')
@stop

@section('css')


@extends('adminlte::page')

@section('title', 'Funcionários')

@section('content_header')
    <h5>Lista de Funcionários</h5>
@stop

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-success float-end new" href="{{ route('funcionario.create') }}"><i class="fas fa-plus"></i> Novo Funcionário</a>
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
        'valueColumnIndex' => 3,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Data de Contratação</th>
                    <th>Setor</th>
                    <th>Cargo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($funcionarios as $funcionario)
                    <tr>
                        <td>{{ $funcionario->id }}</td>
                        <td>{{ $funcionario->nome }}</td>
                        <td>{{ Illuminate\Support\Carbon::parse($funcionario->data_contratacao)->format('d/m/Y')}}</td>
                        <td>{{ $funcionario->setor }}</td>
                        <td>{{ $funcionario->cargo }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showFuncionario{{ $funcionario->id }}">
                                👁️
                            </button>

                            <a class="btn btn-warning btn-sm" href="{{route('funcionario.edit', $funcionario->id)}}"
                                >
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFuncionarioModal{{ $funcionario->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('funcionario.modals._show', ['funcionario' => $funcionario])
                    @include('funcionario.modals._delete', ['funcionario' => $funcionario])
                @endforeach
            </tbody>
            
            @endcomponent

    @include('components.endereco-modal')
@stop
@extends('adminlte::page')

@section('title', 'Vendedor')

@section('content_header')
    <h5>Lista de Vendedores</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('vendedor.create') }}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Vendedor
        </a>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
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
                <th>Email</th>
                <th>Telefone</th>
                <th>CPF</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vendedores as $vendedor)
                <tr>
                    <td>{{ $vendedor->id }}</td>
                    <td>{{ $vendedor->nome }}</td>
                    <td>{{ $vendedor->email }}</td>
                    <td>{{ $vendedor->telefone }}</td>
                    <td>{{ $vendedor->cpf }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showVendedor{{ $vendedor->id }}">
                            👁️
                        </button>

                        <a href="{{ route('vendedor.edit', $vendedor->id) }}" class="btn btn-warning btn-sm">
                            ✏️
                        </a>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteVendedorModal{{ $vendedor->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                @include('vendedor.modals._show', ['vendedor' => $vendedor])
                @include('vendedor.modals._delete', ['vendedor' => $vendedor])
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

        .new:hover {
            background-color: #3e7222 !important;
        }
    </style>
@stop

@section('js')
    <script> console.log("Listagem de vendedores carregada."); </script>
@stop
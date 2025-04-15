@extends('adminlte::page')

@section('title', 'Fornecedor')

@section('content_header')
    <h5>Lista de Fornecedores</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createFornecedorModal">
            <i class="fas fa-plus"></i> Novo Fornecedor
        </button>
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
                    <th>Razão social</th>
                    <th>Nome fantasia</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fornecedor as $fornecedor)
                    <tr>
                        <td>{{ $fornecedor->id }}</td>
                        <td>{{ $fornecedor->razao_social }}</td>
                        <td>{{ $fornecedor->nome_fantasia }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showFornecedor{{ $fornecedor->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editFornecedorModal{{ $fornecedor->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFornecedorModal{{ $fornecedor->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('fornecedor.modals._show', ['fornecedor' => $fornecedor])
                    @include('fornecedor.modals._edit', ['fornecedor' => $fornecedor])
                    @include('fornecedor.modals._delete', ['fornecedor' => $fornecedor])
                @endforeach
            </tbody>
    @endcomponent

    @include('fornecedor.modals._create')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop

@section('css')
<style>
    .new {
        background-color: #679A4C !important;
        border: none !important;
    }
    .new:hover{
        background-color: #3e7222 !important;
    }
</style>
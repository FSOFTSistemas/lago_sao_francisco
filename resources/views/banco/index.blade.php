@extends('adminlte::page')

@section('title', 'Banco')

@section('content_header')
    <h5>Lista de Bancos</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('bancos.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Banco
        </a>
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
                    <th>Descrição</th>
                    <th>Agência</th>
                    <th>Conta</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bancos as $banco)
                    <tr>
                        <td>{{ $banco->id }}</td>
                        <td>{{ $banco->descricao }}</td>
                        <td>{{ $banco->agencia }}</td>
                        <td>{{ $banco->numero_conta }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showBanco{{ $banco->id }}">
                                👁️
                            </button>

                            <a href="{{route('bancos.edit', $banco->id)}}" class="btn btn-warning btn-sm">
                                ✏️
                        </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteBancoModal{{ $banco->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('banco.modals._show', ['banco' => $banco])
                    @include('banco.modals._delete', ['banco' => $banco])
                @endforeach
            </tbody>
    @endcomponent
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
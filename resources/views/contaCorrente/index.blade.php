@extends('adminlte::page')

@section('title', 'Contas Correntes')

@section('content_header')
    <h5>Lista de Contas</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createContaCorrenteModal">
            <i class="fas fa-plus"></i> Nova Conta Corrente
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
                    <th>Titular</th>
                    <th>Número da Conta</th>
                    <th>Descrição</th>
                    <th>Saldo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contaCorrente as $contaCorrente)
                    <tr>
                        <td>{{ $contaCorrente->id }}</td>
                        <td>{{ $contaCorrente->titular }}</td>
                        <td>{{ $contaCorrente->numero_conta }}</td>
                        <td>{{ $contaCorrente->descricao }}</td>
                        <td>R${{ $contaCorrente->saldo }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showContaCorrente{{ $contaCorrente->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editContaCorrenteModal{{ $contaCorrente->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteContaCorrenteModal{{ $contaCorrente->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('contaCorrente.modals._show', ['contaCorrente' => $contaCorrente])
                    @include('contaCorrente.modals._edit', ['contaCorrente' => $contaCorrente])
                    @include('contaCorrente.modals._delete', ['contaCorrente' => $contaCorrente])
                @endforeach
            </tbody>
    @endcomponent

    @include('contaCorrente.modals._create')
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
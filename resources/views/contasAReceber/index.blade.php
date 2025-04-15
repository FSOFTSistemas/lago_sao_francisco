@extends('adminlte::page')

@section('title', 'ContasAReceber')

@section('content_header')
    <h5>Lista de Contas A Receber</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createContasAReceberModal">
            <i class="fas fa-plus"></i> Nova Conta a Receber
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 2, 'targets' => 3],
            ['responsivePriority' => 2, 'targets' => 4],
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
                <th>Data de vencimento</th>
                <th>Valor</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contasAReceber as $conta)
                <tr>
                    <td>{{ $conta->id }}</td>
                    <td>{{ $conta->descricao }}</td>
                    <td>{{ Illuminate\Support\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                    <td>R${{ $conta->valor }}</td>
                    <td>{{ $conta->status }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showContasAReceber{{ $conta->id }}">
                            👁️
                        </button>

                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                            data-target="#editContasAReceberModal{{ $conta->id }}">
                            ✏️
                        </button>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteContasAReceberModal{{ $conta->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>
                @include('contasAReceber.modals._show', ['contasAReceber' => $conta])
                @include('contasAReceber.modals._edit', ['contasAReceber' => $conta])
                @include('contasAReceber.modals._delete', ['contasAReceber' => $conta])
            @endforeach
        </tbody>
    @endcomponent

    @include('contasAReceber.modals._create')
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
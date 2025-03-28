@extends('adminlte::page')

@section('title', 'Lista de planos de conta')

@section('content_header')
    <h5>Lista de planos de conta</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#createPlanoModal">
            <i class="fas fa-plus"></i> Adicionar plano de conta
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
        <table id="planoDeContaTable" class="table table-striped">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th>Empresa</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($planoDeContas as $planoDeConta)
                    <tr>
                        <td>{{ $planoDeConta->id }}</td>
                        <td>{{ $planoDeConta->descricao }}</td>
                        <td>{{ $planoDeConta->tipo }}</td>
                        <td>{{ $planoDeConta->empresa->razao_social }}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editPlanoModal{{ $planoDeConta->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deletePlanoModal{{ $planoDeConta->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>
                    @include('planoDeConta.modals._edit', ['planoDeConta' => $planoDeConta])
                    @include('planoDeConta.modals._delete', ['planoDeConta' => $planoDeConta])
                @endforeach
            </tbody>
        </table>
    @endcomponent

    @include('planoDeConta.modals._create')
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop

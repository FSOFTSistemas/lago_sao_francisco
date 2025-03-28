@extends('adminlte::page')

@section('title', 'ContasAPagar')

@section('content_header')
    <h5>Lista de Contas A Pagar</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#createContasAPagarModal">
            <i class="fas fa-plus"></i> Adicionar Contas a Pagar
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
        <table id="contasAPagarTable" class="table table-striped">
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
                @foreach ($contasAPagar as $contasAPagar)
                    <tr>
                        <td>{{ $contasAPagar->id }}</td>
                        <td>{{ $contasAPagar->descricao }}</td>
                        <td>{{ Illuminate\Support\Carbon::parse($contasAPagar->data_vencimento)->format('d/m/Y')}}</td>
                        <td>R${{ $contasAPagar->valor }}</td>
                        <td>{{ $contasAPagar->status }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showContasAPagar{{ $contasAPagar->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editContasAPagarModal{{ $contasAPagar->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteContasAPagarModal{{ $contasAPagar->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('contasAPagar.modals._show', ['contasAPagar' => $contasAPagar])
                    @include('contasAPagar.modals._edit', ['contasAPagar' => $contasAPagar])
                    @include('contasAPagar.modals._delete', ['contasAPagar' => $contasAPagar])
                @endforeach
            </tbody>
        </table>
    @endcomponent

    @include('contasAPagar.modals._create')
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

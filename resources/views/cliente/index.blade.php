@extends('adminlte::page')

@section('title', 'Cliente')

@section('content_header')
    <h5>Lista de Clientes</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('cliente.create')}}" class="btn btn-success">
            <i class="fas fa-plus"></i> Adicionar Cliente
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
        <table id="clienteTable" class="table table-striped">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome/Razão Social</th>
                    <th>Apelido/Nome Fantasia</th>
                    <th>Tipo</th>
                    <th>Contato</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cliente as $cliente)
                    <tr>
                        <td>{{ $cliente->id }}</td>
                        <td>{{ $cliente->nome_razao_social }}</td>
                        <td>{{ $cliente->apelido_nome_fantasia }}</td>
                        <td>{{ $cliente->tipo}}</td>
                        <td>{{ $cliente->telefone ? $cliente->telefone : $cliente->whatsapp }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showCliente{{ $cliente->id }}">
                                👁️
                            </button>

                            <a href="{{route('cliente.edit', $cliente->id)}}" class="btn btn-warning btn-sm">
                                ✏️
                        </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteClienteModal{{ $cliente->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('cliente.modals._show', ['cliente' => $cliente])
                    @include('cliente.modals._delete', ['cliente' => $cliente])
                @endforeach
            </tbody>
        </table>
    @endcomponent
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

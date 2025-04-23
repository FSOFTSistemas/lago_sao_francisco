@extends('adminlte::page')

@section('title', 'Empresa')

@section('content_header')
    <h5>Lista de Empresas</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-success float-end new" href="{{ route('empresa.create') }}"><i class="fas fa-plus"></i> Nova Empresa</a>
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
                @foreach ($empresas as $empresa)
                    <tr>
                        <td>{{ $empresa->id }}</td>
                        <td>{{ $empresa->razao_social }}</td>
                        <td>{{ $empresa->nome_fantasia }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showEmpresa{{ $empresa->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editEmpresaModal{{ $empresa->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteEmpresaModal{{ $empresa->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('empresa.modals._show', ['empresa' => $empresa])
                    @include('empresa.modals._edit', ['empresa' => $empresa])
                    @include('empresa.modals._delete', ['empresa' => $empresa])
                @endforeach
            </tbody>
    @endcomponent
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
@stop

@extends('adminlte::page')

@section('title', 'Funcionários')

@section('content_header')
    <h5>Lista de Funcionários</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#createFuncionarioModal">
            <i class="fas fa-plus"></i> Adicionar Funcionário
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
        <table id="funcionarioTable" class="table table-striped">
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
                        <td>{{ $funcionario->data_contratacao }}</td>
                        <td>{{ $funcionario->setor }}</td>
                        <td>{{ $funcionario->cargo }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showFuncionario{{ $funcionario->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editFuncionarioModal{{ $funcionario->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFuncionarioModal{{ $funcionario->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('funcionario.modals._show', ['funcionario' => $funcionario])
                    @include('funcionario.modals._edit', ['funcionario' => $funcionario])
                    @include('funcionario.modals._delete', ['funcionario' => $funcionario])
                @endforeach
            </tbody>
        </table>
    @endcomponent

    @include('funcionario.modals._create')
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
    <script>
        $(document).ready(function() {
            $('#planoDeContaTable').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                },
                responsive: true,
                autoWidth: false
            });
        });

        @if (session('success'))
            Swal.fire({
                title: 'Sucesso!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();

            let form = $(this).closest("form");

            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@stop

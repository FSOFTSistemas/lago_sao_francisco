@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <h5>Lista de Usuários</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#createUsuarioModal">
            <i class="fas fa-plus"></i> Adicionar Usuário
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
        <table id="usuarioTable" class="table table-striped">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Permissão</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editUsuarioModal{{ $user->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteUsuarioModal{{ $user->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>

                    @include('usuario.modals._edit', ['usuario' => $user])
                    @include('usuario.modals._delete', ['usuario' => $user])
                @endforeach
            </tbody>
        </table>
    @endcomponent

    @include('components.endereco-modal')
    @include('usuario.modals._create')
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            console.log('aqui')
            $("#cep").on("input", function () {
                $(this).val($(this).val().replace(/\D/g, "").slice(0, 8));
            });

            $("#cep").on("blur", function () {
                let cep = $(this).val().replace(/\D/g, "");

                if (cep.length === 8) {
                    $.getJSON(`/buscar-cep/${cep}`, function (data) {
                        if (!data.erro) {
                            $("#logradouro").val(data.logradouro);
                            $("#bairro").val(data.bairro);
                            $("#cidade").val(data.localidade);
                            $("#uf").val(data.uf);
                            $("#ibge").val(data.ibge);
                        } else {
                            alert("CEP não encontrado!");
                        }
                    }).fail(function () {
                        alert("Erro ao buscar CEP. Verifique sua conexão.");
                    });
                } else {
                    alert("Formato de CEP inválido!");
                }
            });
        });
    </script>

@stop

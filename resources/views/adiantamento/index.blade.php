@extends('adminlte::page')

@section('title', 'Adiantamentos')

@section('content_header')
    <h5>Lista de Adiantamentos</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#createAdiantamentoModal">
            <i class="fas fa-plus"></i> Adicionar Adiantamento
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
        <table id="adiantamentoTable" class="table table-striped">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Funcionário</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adiantamentos as $adiantamento)
                    <tr>
                        <td>{{ $adiantamento->id }}</td>
                        <td>{{ $adiantamento->funcionario->nome }}</td>
                        <td>{{ $adiantamento->valor }}</td>
                        <td>{{Illuminate\Support\Carbon::parse($adiantamento->data)->format('d/m/Y')}}</td>
                        <td>{{ $adiantamento->status }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showAdiantamento{{ $adiantamento->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editAdiantamentoModal{{ $adiantamento->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteAdiantamentoModal{{ $adiantamento->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('adiantamento.modals._show', ['adiantamento' => $adiantamento])
                    @include('adiantamento.modals._edit', ['adiantamento' => $adiantamento])
                    @include('adiantamento.modals._delete', ['adiantamento' => $adiantamento])
                @endforeach
            </tbody>
        </table>
    @endcomponent

    @include('adiantamento.modals._create')
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

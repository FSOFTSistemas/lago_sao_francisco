@extends('adminlte::page')

@section('title', 'Formas de Pagamento')

@section('content_header')
@stop

@section('content')
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('preferencias') }}">
                <button class="btn btn-success float-start">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </button>
            </a>
        </div>

        <div class="col">
            <!-- Botão para abrir o modal de criação -->
            <button class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#createFormaPagamentoModal">
                <i class="fas fa-plus"></i>
                Nova Forma de Pagamento
            </button>
        </div>
    </div>

    <!-- DataTable Customizado -->
    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 4,
    ])
        <thead class="table-primary">
            <tr>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($formaPagamento as $formaPagamento)
                <tr>
                    <td>{{ $formaPagamento->descricao }}</td>
                    <td>
                        <!-- Botão Editar -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#editFormaPagamentoModal{{ $formaPagamento->id }}">
                            ✏️
                        </button>
                        <!-- Botão Excluir -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#deleteFormaPagamentoModal{{ $formaPagamento->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                <!-- Modal Editar -->
                <div class="modal fade" id="editFormaPagamentoModal{{ $formaPagamento->id }}" tabindex="-1"
                    aria-labelledby="editFormaPagamentoModalLabel{{ $formaPagamento->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('formaPagamento.update', $formaPagamento->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit"></i> Editar Forma de Pagamento
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="descricao">Descrição</label>
                                        <input type="text" name="descricao" id="descricao" class="form-control"
                                            value="{{ $formaPagamento->descricao }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Excluir -->
                <div class="modal fade" id="deleteFormaPagamentoModal{{ $formaPagamento->id }}" tabindex="-1"
                    aria-labelledby="deleteFormaPagamentoModalLabel{{ $formaPagamento->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('formaPagamento.destroy', $formaPagamento->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-trash"></i> Confirmar Exclusão
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza que deseja excluir a forma de pagamento
                                    <strong>{{ $formaPagamento->descricao }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </tbody>
    @endcomponent

    <!-- Modal Criar -->
    <div class="modal fade" id="createFormaPagamentoModal" tabindex="-1" aria-labelledby="createFormaPagamentoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('formaPagamento.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cogs"></i> Adicionar Nova Forma de Pagamento
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <input type="text" name="descricao" id="descricao" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@stop

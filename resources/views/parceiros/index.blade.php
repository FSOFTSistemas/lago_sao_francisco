@extends('adminlte::page')

@section('title', 'Adicionais')

@section('content_header')
    <h5>Cadastro de Parceiros</h5>
    <hr>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('preferencias') }}" class="btn btn-success new">               
                    <i class="fas fa-arrow-left"></i>
                    Voltar
            </a>
        </div>

        <div class="col">
            <!-- Botão para abrir o modal de criação -->
            <button class="btn btn-success new float-end" data-bs-toggle="modal" data-bs-target="#createParceiroModal">
                <i class="fas fa-plus"></i>
                Novo Parceiro
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
                <th>Valor</th>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($parceiros as $parceiro)
                <tr>
                    <td>{{ $parceiro->descricao }}</td>
                    <td>R${{ $parceiro->valor }}</td>
                    <td>{{ $parceiro->categoria }}</td>
                    <td>
                        <!-- Botão Editar -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#editParceiroModal{{ $parceiro->id }}">
                            ✏️
                        </button>
                        <!-- Botão Excluir -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#deleteParceiroModal{{ $parceiro->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                <!-- Modal Editar -->
                <div class="modal fade" id="editParceiroModal{{ $parceiro->id }}" tabindex="-1"
                    aria-labelledby="editParceiroModalLabel{{ $parceiro->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('parceiros.update', $parceiro->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit"></i> Editar Parceiro
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="descricao">Descrição</label>
                                        <input type="text" name="descricao" id="descricao" class="form-control"
                                            value="{{ $parceiro->descricao }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="valor">Valor</label>
                                        <input type="number" name="valor" id="valor" class="form-control"
                                            value="{{ $parceiro->valor }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="categoria">Categoria</label>
                                        <input type="text" name="categoria" id="categoria" class="form-control"
                                            value="{{ $parceiro->categoria }}" required>
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
                <div class="modal fade" id="deleteParceiroModal{{ $parceiro->id }}" tabindex="-1"
                    aria-labelledby="deleteParceiroModalLabel{{ $parceiro->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('parceiros.destroy', $parceiro->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-trash"></i> Confirmar Exclusão
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza que deseja excluir o parceiro 
                                    <strong>{{ $parceiro->descricao }}</strong>?
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
    <div class="modal fade" id="createParceiroModal" tabindex="-1" aria-labelledby="createParceiroModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('adicionais.store') }}" method="POST">
                    @csrf
                    <div class="modal-header text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cogs"></i> Adicionar Novo Parceiro
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <input type="text" name="descricao" id="descricao" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="number" name="valor" id="valor" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categoria</label>
                            <input type="categoria" name="categoria" id="categoria" class="form-control" required>
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
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@stop

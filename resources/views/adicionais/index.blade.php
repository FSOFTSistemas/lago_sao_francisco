@extends('adminlte::page')

@section('title', 'Adicionais')

@section('content_header')
    <h5>Cadastro de adicionais (Mobília)</h5>
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
            <button class="btn btn-success new float-end" data-bs-toggle="modal" data-bs-target="#createAdicionalModal">
                <i class="fas fa-plus"></i>
                Nova Mobília
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
                <th>Valor (unitário)</th>
                <th>Quantidade Disponível</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($adicionais as $adicional)
                <tr>
                    <td>{{ $adicional->descricao }}</td>
                    <td>R${{ $adicional->valor }}</td>
                    <td>{{ intval($adicional->quantidade) }} unidades</td>
                    <td>
                        <!-- Botão Editar -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#editAdicionalModal{{ $adicional->id }}">
                            ✏️
                        </button>
                        <!-- Botão Excluir -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#deleteAdicionalModal{{ $adicional->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                <!-- Modal Editar -->
                <div class="modal fade" id="editAdicionalModal{{ $adicional->id }}" tabindex="-1"
                    aria-labelledby="editAdicionalModalLabel{{ $adicional->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('adicionais.update', $adicional->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit"></i> Editar Adicional
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="descricao">Descrição</label>
                                        <input type="text" name="descricao" id="descricao" class="form-control"
                                            value="{{ $adicional->descricao }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="valor">Valor</label>
                                        <input type="number" name="valor" id="valor" class="form-control"
                                            value="{{ $adicional->valor }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="quantidade">Quantidade Disponível</label>
                                        <input type="number" name="quantidade" id="quantidade" class="form-control"
                                            value="{{ $adicional->quantidade }}" required>
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
                <div class="modal fade" id="deleteAdicionalModal{{ $adicional->id }}" tabindex="-1"
                    aria-labelledby="deleteAdicionalModalLabel{{ $adicional->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('adicionais.destroy', $adicional->id) }}" method="POST">
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
                                    Tem certeza que deseja excluir
                                    <strong>{{ $adicional->descricao }}</strong>?
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
    <div class="modal fade" id="createAdicionalModal" tabindex="-1" aria-labelledby="createAdicionalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('adicionais.store') }}" method="POST">
                    @csrf
                    <div class="modal-header text-white">
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
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="number" name="valor" id="valor" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="quantidade">Quantidade Disponível</label>
                            <input type="number" name="quantidade" id="quantidade" class="form-control" required>
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

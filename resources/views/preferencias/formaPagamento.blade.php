@extends('adminlte::page')

@section('title', 'Formas de Pagamento')

@section('content_header')
    <h5>Formas de Pagamento</h5>
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
            <button class="btn btn-success new float-end" data-bs-toggle="modal" data-bs-target="#createFormaPagamentoModal">
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
                                <input type="hidden" name="_origem" value="editFormaPagamentoModal{{ $formaPagamento->id }}">
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
                                        <input type="text" name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror"
                                            value="{{ old('descricao', $formaPagamento->descricao) }}" required>
                                        @error('descricao')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
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
                                <div class="modal-header">
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
                    <input type="hidden" name="_origem" value="createFormaPagamentoModal">
                    <div class="modal-header text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cogs"></i> Adicionar Nova Forma de Pagamento
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <input type="text" name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror"
                                value="{{ old('descricao') }}" required>
                            @error('descricao')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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

            @if ($errors->any())
                var origemComErro = @json(old('_origem'));
                if (origemComErro) {
                    var modalEl = document.getElementById(origemComErro);
                    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        new bootstrap.Modal(modalEl).show();
                    }
                }
            @endif
        });
    </script>
@stop

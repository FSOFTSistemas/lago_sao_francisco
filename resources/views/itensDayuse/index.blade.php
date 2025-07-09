@extends('adminlte::page')

@section('title', 'Entrada/Passeios Day Use')

@section('content_header')
    <h5>Entrada/Passeios Day Use</h5>
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
            <button class="btn btn-success new float-end" data-bs-toggle="modal" data-bs-target="#createItemDayuseModal">
                <i class="fas fa-plus"></i>
                Novo Cadastro
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
                <th>valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($itens as $item)
                <tr>
                    <td>{{ $item->descricao }}</td>
                    <td>R${{ $item->valor }}</td>
                    <td>
                        <!-- Botão Editar -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#editItemDayuseModal{{ $item->id }}">
                            ✏️
                        </button>
                        <!-- Botão Excluir -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#deleteItemDayuseModal{{ $item->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                <!-- Modal Editar -->
                <div class="modal fade" id="editItemDayuseModal{{ $item->id }}" tabindex="-1"
                    aria-labelledby="editItemDayuseModalLabel{{ $item->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('itemDayuse.update', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit"></i> Editar Entrada/Passeio
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="descricao">Descrição</label>
                                        <input type="text" name="descricao" id="descricao" class="form-control"
                                            value="{{ $item->descricao }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="valor">Valor</label>
                                        <input type="number" name="valor" id="valor" class="form-control" value="{{$item->valor}}" required>
                                    </div>
                                    <div class="form-group d-flex align-items-center p-3 rounded bg-light">
                                      <label for="passeio" class="form-label mb-0 mr-3">É Passeio?</label>

                                      <label class="switch-slide mb-0">
                                          <input type="hidden" name="passeio" value="0">
                                          <input type="checkbox" id="passeio" value="1" name="passeio" @checked(old('passeio', $item->passeio))>
                                          <span class="slider-slide"></span>
                                      </label>
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
                <div class="modal fade" id="deleteItemDayuseModal{{ $item->id }}" tabindex="-1"
                    aria-labelledby="deleteItemDayuseModalLabel{{ $item->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('itemDayuse.destroy', $item->id) }}" method="POST">
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
                                    Tem certeza que deseja excluir o Item
                                    <strong>{{ $item->descricao }}</strong>?
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
    <div class="modal fade" id="createItemDayuseModal" tabindex="-1" aria-labelledby="createItemDayuseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('itemDayuse.store') }}" method="POST">
                    @csrf
                    <div class="modal-header text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-cogs"></i> Adicionar Nova Entrada/Passeio
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

                        <div class="form-group d-flex align-items-center p-3 rounded bg-light">
                          <label for="passeio" class="form-label mb-0 mr-3">É Passeio?</label>
                          <label class="switch-slide mb-0">
                            <input type="hidden" name="passeio" value="0">
                            <input type="checkbox" id="passeio" value="1" name="passeio">
                            <span class="slider-slide"></span>
                          </label>
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

@section('css')
<style>
.switch-slide {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.switch-slide input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider-slide {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
}

.slider-slide:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

.switch-slide input:checked + .slider-slide {
  background-color: var(--green-1);
}

.switch-slide input:checked + .slider-slide:before {
  transform: translateX(24px);
}
</style>


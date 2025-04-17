<div class="modal fade" id="editAdiantamentoModal{{$adiantamento->id}}" tabindex="-1" aria-labelledby="editAdiantamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAdiantamentoModalLabel">Atualizar Adiantamentos</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAdiantamentoForm" action="{{ route('adiantamento.update',$adiantamento->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nome">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{$adiantamento->descricao}}" required>
                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label for="cpf">Funcionário:</label>
                            <select class="form-control" id="funcionario_id" name="funcionario_id" required>
                                @foreach ($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="valor">Valor:</label>
                        <input type="text" class="form-control" id="valor" name="valor" value="{{$adiantamento->valor}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="dataContratacao">Data do Adiantamento:</label>
                        <input type="date" class="form-control" id="data" name="data" value="{{$adiantamento->data}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="tipo">Situação:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pendente" {{ $adiantamento->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="finalizado" {{ $adiantamento->status == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        </select>
                    </div>

                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                    </form>
            </div>

        </div>
    </div>
</div>


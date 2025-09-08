<div class="modal fade" id="editPlanoModal{{$planoDeConta->id}}" tabindex="-1" aria-labelledby="editPlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanoModalLabel">Editar Plano de Conta</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPlanoForm" action="{{ route('planoDeConta.update',$planoDeConta->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descrição</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{$planoDeConta->descricao}}" required>
                      </div>
                      
                      <div class="mb-3">
                        <label for="tipo">Tipo</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                          <option value="receita">Receita</option>
                          <option value="despesa">Despesa</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="planoDeConta">Plano de conta pai</label>
                        <select class="form-control" id="planoDeConta" name="plano_de_conta_pai">
                          <option value="">Selecione</option>
                          @foreach ($planoDeContas as $planoDeConta)
                            <option value="{{$planoDeConta->id}}">{{$planoDeConta->descricao}}</option>
                          @endforeach
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


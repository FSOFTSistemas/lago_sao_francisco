  <!-- Modal -->
  <div class="modal fade" id="createPlanoModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="createPlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createPlanoModalLabel">Cadastro de Plano de contas</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="createPlanoForm" action="{{route('planoDeConta.store')}}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="descricao">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao" required>
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
              <button type="submit" class="btn btn-primary">Criar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

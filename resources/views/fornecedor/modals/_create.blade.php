  <!-- Modal -->
  <div class="modal fade" id="createFornecedorModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="createFornecedorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createFornecedorModalLabel">Cadastro de Fornecedor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="createFornecedorForm" action="{{route('fornecedor.store')}}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="razaoSocial">Razão Social:</label>
              <input type="text" class="form-control" id="razaoSocial" name="razao_social" required>
            </div>
            <div class="mb-3">
              <label for="nomeFantasia">Nome Fantasia:</label>
              <input type="text" class="form-control" id="nomeFantasia" name="nome_fantasia">
            </div>
            <div class="mb-3">
              <label for="cnpj">CNPJ:</label>
              <input type="text" class="form-control" id="cnpj" name="cnpj">
            </div>
            <div class="mb-3">
              <label for="inscricaoEstadual">Inscrição Estadual:</label>
              <input type="text" class="form-control" id="inscricaoEstadual" name="inscricao_estadual">
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

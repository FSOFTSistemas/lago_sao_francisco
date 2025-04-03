  <!-- Modal -->
  <div class="modal fade" id="createEspacoModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createEspacoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createEspacoModalLabel">Cadastro de Espacos</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                    <form id="createEspacoForm" action="{{ route('espaco.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nome">Nome:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>

                        <div class="mb-3">
                            <label for="valor">Valor:</label>
                            <input type="text" class="form-control" id="valor" name="valor" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo">Situação:</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="disponivel">Disponível</option>
                                <option value="alugado">Alugado</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="empresa">Empresa:</label>
                            <select class="form-control" id="empresa" name="empresa_id" required>
                                <option value="">Selecione</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razao_social }}</option>
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
  </div>

  <!-- Modal -->
  <div class="modal fade" id="createEspacoModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createEspacoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createEspacoModalLabel">Cadastro de Espacos</h5>
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                    <form id="createEspacoForm" action="{{ route('espaco.store') }}" method="POST">
                        @csrf
                        <div class="form-group d-flex align-items-center p-3 rounded bg-light">
                            <label for="capela" class="form-label mb-0 mr-3">Capela?</label>

                            <label class="switch-slide mb-0">
                                <input type="hidden" name="capela" value="0">
                                <input type="checkbox" id="capela" value="1" name="capela">
                                <span class="slider-slide"></span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="nome">Nome:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>

                        <div class="mb-3">
                            <label for="valor_semana" id="labelSemana">Valor na semana (Seg a Qui):</label>
                            <input type="text" class="form-control" id="valor_semana" name="valor_semana" required>
                        </div>
                        <div class="mb-3">
                            <label for="valor_fim" id="labelFim">Valor no Fim de semana (Sex a Dom):</label>
                            <input type="text" class="form-control" id="valor_fim" name="valor_fim" required>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Criar</button>
                        </div>
                    </form>
              </div>

          </div>
      </div>
  </div>
  </div>

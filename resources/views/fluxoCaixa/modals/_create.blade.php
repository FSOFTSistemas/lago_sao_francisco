  <!-- Modal -->
  <div class="modal fade" id="createFluxoCaixaModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createFluxoCaixaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createFluxoCaixaModalLabel">Cadastro de Fluxo de Caixa</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createFluxoCaixaForm" action="{{ route('fluxoCaixa.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="descricao">Descricao:</label>
                          <input type="text" class="form-control" id="descricao" name="descricao" required>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor">Valor:</label>
                            <input type="number" class="form-control" id="valor" name="valor" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tipo">Tipo:</label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="entrada">Entrada</option>
                                <option value="saida">Saída</option>
                            </select>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data">Data:</label>
                            <input type="date" class="form-control" id="data" name="data" required>
                        </div>
  
                        <div class="col-md-6 mb-3">
                          <label for="caixa">caixa:</label>
                          <select class="form-control" id="caixa" name="caixa_id" required>
                              <option value="">Selecione</option>
                              @foreach ($caixas as $caixa)
                                  <option value="{{ $caixa->id }}">{{ $caixa->descricao}}</option>
                              @endforeach
                          </select>
                        </div>
                      </div>
                      
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="movimento">Movimento:</label>
                            <select class="form-control" id="movimento" name="movimento_id" required>
                                <option value="">Selecione</option>
                                @foreach ($movimento as $movimento)
                                    <option value="{{ $movimento->id }}">{{ $movimento->descricao }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valorTotal">Valor Total:</label>
                            <input type="number" class="form-control" id="valorTotal" name="valor_total" required>
                        </div>
                    </div>
                    

                    <div class="mb-3">
                        <label for="empresa">Empresa:</label>
                        <select class="form-control" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->nome_fantasia }}</option>
                            @endforeach
                        </select>
                    </div>

                      
                      <div class="mb-3">
                        <label for="planoDeConta">Plano de conta:</label>
                        <select class="form-control" id="planoDeConta" name="plano_de_conta_id" required>
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
  </div>

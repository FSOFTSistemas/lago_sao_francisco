  <!-- Modal -->
  <div class="modal fade" id="createContasAPagarModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createContasAPagarModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createContasAPagarModalLabel">Cadastro de Contas A Pagar</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createContasAPagarForm" action="{{ route('contasAPagar.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="descricao">Descrição:</label>
                          <input type="text" class="form-control" id="descricao" name="descricao" required>
                      </div>

                      <div class="row">
                          <div class="mb-3">
                              <label for="valor">Valor:</label>
                              <input type="text" class="form-control" id="valor" name="valor">
                          </div>

                      </div>
                      <div class="mb-3">
                          <label for="valorPago">Valor Pago:</label>
                          <input type="text" class="form-control" id="valorPago" name="valor_pago">
                      </div>
              </div>

              <div class="mb-3">
                  <label for="dataVencimento">Data de Vencimento:</label>
                  <input type="date" class="form-control" id="dataVencimento" name="data_vencimento">
              </div>

              <div class="mb-3">
                  <label for="dataPagamento">Data do Pagamento:</label>
                  <input type="date" class="form-control" id="dataPagamento" name="data_pagamento">
              </div>

              <div class="mb-3">
                  <label for="tipo">Situação</label>
                  <select class="form-control" id="status" name="status" required>
                      <option value="pendente">Pendente</option>
                      <option value="finalizado">Finalizado</option>
                  </select>
              </div>

              <div class="mb-3">
                  <label for="planoDeConta">Plano de contas</label>
                  <select class="form-control" id="planoDeConta" name="plano_de_contas_pai">
                      <option value="">Selecione</option>
                      @foreach ($planoDeContas as $planoDeConta)
                          <option value="{{ $planoDeConta->id }}">{{ $planoDeConta->descricao }}</option>
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

<div class="modal fade" id="editFluxoCaixaModal{{$fluxoCaixa->id}}" tabindex="-1" aria-labelledby="editFluxoCaixaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFluxoCaixaModalLabel">Editar Funcionário</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFluxoCaixaForm" action="{{ route('fluxoCaixa.update',$fluxoCaixa->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descricao:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{$fluxoCaixa->descricao}}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valor">Valor:</label>
                        <input type="text" class="form-control" id="valor" name="valor" value="{{$fluxoCaixa->valor}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="data">Data:</label>
                        <input type="date" class="form-control" id="data" name="data" value="{{$fluxoCaixa->data}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="tipo">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value="PF">Pessoa Física</option>
                            <option value="PJ">Pessoa Jurídica</option>
                        </select>
                    </div>

                    <div class="mb-3">
                      <label for="caixa">caixa:</label>
                      <select class="form-control" id="caixa" name="caixa_id" required>
                          <option value="">Selecione</option>
                          @foreach ($caixa as $caixa)
                              <option value="{{ $caixa->id }}">{{ $caixa->data_abertura }}</option>
                          @endforeach
                      </select>
                  </div>

                  
                  <div class="mb-3">
                      <label for="empresa">Empresa:</label>
                      <select class="form-control" id="empresa" name="empresa_id" required>
                          <option value="">Selecione</option>
                          @foreach ($empresa as $empresa)
                              <option value="{{ $empresa->id }}">{{ $empresa->razao_social }}</option>
                          @endforeach
                      </select>
                  </div>

                    <div class="mb-3">
                        <label for="valorTotal">Valor Total:</label>
                        <input type="text" class="form-control" id="valorTotal" name="valor_total" value="{{$fluxoCaixa->valor_total}}" required>
                    </div>

                    <div class="mb-3">
                      <label for="planoDeConta">Plano de conta:</label>
                      <select class="form-control" id="planoDeConta" name="plano_de_conta_id">
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


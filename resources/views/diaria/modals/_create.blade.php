  <!-- Modal -->
  <div class="modal fade" id="createDiariaModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createDiariaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createDiariaModalLabel">Cadastro de Diarias</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createDiariaForm" action="{{ route('diaria.store') }}" method="POST">
                      @csrf
                      
                      <div class="mb-3">
                        <label for="tipo">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value="day_use" data-price="30">Day use</option>
                            <option value="passaporte" data-price="50">Passaporte</option>
                        </select>
                    </div>
                    

                        <div class="row">
                            <div class="mb-3">
                                <label for="cliente">Cliente:</label>
                                <select class="form-control" id="cliente_id" name="cliente_id" required>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}">{{ $cliente->nome_razao_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="quantidade">Quantidade:</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="valor">Valor:</label>
                            <input type="text" class="form-control" id="valor_exibido" required>
                            <input type="hidden" id="valor_real" name="valor">
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

<script>
    const valorExibidoInput = document.getElementById('valor_exibido');
    const valorRealInput = document.getElementById('valor_real');
    const tipoSelect = document.getElementById('tipo');
    const quantidadeInput = document.getElementById('quantidade');

    function calcularValor() {
        const tipoSelecionado = tipoSelect.options[tipoSelect.selectedIndex];
        const precoPorUnidade = parseFloat(tipoSelecionado.getAttribute('data-price')) || 0;
        const quantidade = parseInt(quantidadeInput.value) || 0;

        const valorTotal = precoPorUnidade * quantidade;

        valorRealInput.value = valorTotal;

        valorExibidoInput.value = `R$ ${valorTotal.toFixed(2)}`;
    }

    tipoSelect.addEventListener('change', calcularValor);
    quantidadeInput.addEventListener('input', calcularValor);

    calcularValor();
</script>


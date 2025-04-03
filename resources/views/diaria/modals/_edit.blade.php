<div class="modal fade" id="editDiariaModal{{$diaria->id}}" tabindex="-1" aria-labelledby="editDiariaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDiariaModalLabel">Editar Diarias</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDiariaForm" action="{{ route('diaria.update',$diaria->id) }}" method="POST" >
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="tipo">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value="day_use" data-price="30" {{ $diaria->tipo == 'day_use' ? 'selected' : '' }}>Day use</option>
                            <option value="passaporte" data-price="50" {{ $diaria->tipo == 'passaporte' ? 'selected' : '' }}>Passaporte</option>
                        </select>
                    </div>
    

                    <div class="row">
                        <div class="mb-3">
                            <label for="cliente">Cliente:</label>
                            <select class="form-control" id="cliente_id" name="cliente_id" required>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ $cliente->id == $diaria->cliente_id ? 'selected' : '' }}>
                                        {{ $cliente->nome_razao_social }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quantidade">Quantidade:</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" value="{{ $diaria->quantidade }}" required>
                    </div>
    
                    
                    <div class="mb-3">
                        <label for="valor">Valor:</label>
                        <input type="text" class="form-control" id="valor_exibido" required>
                        <input type="hidden" id="valor_real" name="valor" value="{{ $diaria->valor }}">
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

<script>
    const tipoSelect = document.getElementById('tipo');
    const quantidadeInput = document.getElementById('quantidade');
    const valorExibidoInput = document.getElementById('valor_exibido');
    const valorRealInput = document.getElementById('valor_real');

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
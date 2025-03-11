<div class="modal fade" id="editCaixaModal{{$caixa->id}}" tabindex="-1" aria-labelledby="editCaixaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCaixaModalLabel">Editar Contas A Pagar</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCaixaForm" action="{{ route('caixa.update',$caixa->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{$caixa->descricao}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="dataAbertura">Data de Abertura:</label>
                        <input type="date" class="form-control" id="dataAbertura" name="data_abertura" value="{{$caixa->data_abertura}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="dataFechamento">Data de Fechamento:</label>
                        <input type="date" class="form-control" id="dataFechamento" name="data_fechamento" value="{{$caixa->data_fechamento}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="valorInicial">Valor Inicial:</label>
                        <input type="text" class="form-control" id="valorInicial" name="valor-inicial" value="{{$caixa->valor_inicial}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="valorFinal">Valor Final:</label>
                        <input type="text" class="form-control" id="valorFinal" name="valor_final" value="{{$caixa->valor_final}}">
                    </div>

                    <div class="mb-3">
                        <label for="tipo">Situação:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="aberto">Aberto</option>
                            <option value="fechado">Fechado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="empresa">Empresa:</label>
                        <select class="form-control" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                      <label for="observacoes">Observações:</label>
                      <input type="text" class="form-control" id="observacoes" name="observacoes" value="{{$caixa->observacoes}}">
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


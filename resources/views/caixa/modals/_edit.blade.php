<div class="modal fade" id="editCaixaModal{{$caixa->id}}" tabindex="-1" aria-labelledby="editCaixaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCaixaModalLabel">Atualizar Caixa</h5>
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dataAbertura">Data de Abertura:</label>
                            <input type="date" class="form-control" id="dataAbertura" name="data_abertura" 
                                   value="{{ old('data_abertura', isset($caixa->data_abertura) ? \Carbon\Carbon::parse($caixa->data_abertura)->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dataFechamento">Data de Fechamento:</label>
                            <input type="date" class="form-control" id="dataFechamento" name="data_fechamento" 
                                   value="{{ old('data_fechamento', isset($caixa->data_fechamento) ? \Carbon\Carbon::parse($caixa->data_fechamento)->format('Y-m-d') : '') }}" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valorInicial">Valor Inicial:</label>
                            <input type="text" class="form-control" id="valorInicial" name="valor_inicial" value="{{$caixa->valor_inicial}}" required>
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="valorFinal">Valor Final:</label>
                            <input type="text" class="form-control" id="valorFinal" name="valor_final" value="{{$caixa->valor_final}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo">Situação:</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="aberto" {{ old('status', $caixa->status ?? '') == 'aberto' ? 'selected' : '' }}>Aberto</option>
                                <option value="fechado" {{ old('status', $caixa->status ?? '') == 'fechado' ? 'selected' : '' }}>Fechado</option>
                            </select>
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="empresa">Empresa:</label>
                            <select class="form-control" id="empresa" name="empresa_id" required>
                                <option value="">Selecione</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" 
                                        {{ old('empresa_id', $caixa->empresa_id ?? '') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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


<div class="modal fade" id="editFluxoCaixaModal{{$fluxoCaixa->id}}" tabindex="-1" aria-labelledby="editFluxoCaixaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFluxoCaixaModalLabel">Atualizar Fluxo de caixa</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFluxoCaixaForm" action="{{ route('fluxoCaixa.update',$fluxoCaixa->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descricao:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" 
                               value="{{ old('descricao', $fluxoCaixa->descricao ?? '') }}" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor">Valor:</label>
                            <input type="text" class="form-control" id="valor" name="valor" 
                                   value="{{ old('valor', $fluxoCaixa->valor ?? '') }}" required>
                        </div>
                    
                        <div class="col-md-6 mb-3">
                            <label for="tipo">Tipo:</label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="entrada" {{ old('tipo', $fluxoCaixa->tipo ?? '') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="saida" {{ old('tipo', $fluxoCaixa->tipo ?? '') == 'saida' ? 'selected' : '' }}>Saída</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data">Data:</label>
                            <input type="date" class="form-control" id="data" name="data" 
                                   value="{{ old('data', isset($fluxoCaixa->data) ? \Carbon\Carbon::parse($fluxoCaixa->data)->format('Y-m-d') : '') }}" required>
                        </div>
                    
                        <div class="col-md-6 mb-3">
                            <label for="caixa">Caixa:</label>
                            <select class="form-control" id="caixa" name="caixa_id" required>
                                <option value="">Selecione</option>
                                @foreach ($caixa as $item)
                                    <option value="{{ $item->id }}" {{ old('caixa_id', $fluxoCaixa->caixa_id ?? '') == $item->id ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($item->data_abertura)->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="movimento">Movimento:</label>
                            <select class="form-control" id="movimento" name="movimento_id" required>
                                <option value="">Selecione</option>
                                @foreach ($movimento as $item)
                                    <option value="{{ $item->id }}" {{ old('movimento_id', $fluxoCaixa->movimento_id ?? '') == $item->id ? 'selected' : '' }}>
                                        {{ $item->descricao }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valorTotal">Valor Total:</label>
                            <input type="text" class="form-control" id="valorTotal" name="valor_total" 
                                   value="{{ old('valor_total', $fluxoCaixa->valor_total ?? '') }}" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="empresa">Empresa:</label>
                        <select class="form-control" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresa as $item)
                                <option value="{{ $item->id }}" {{ old('empresa_id', $fluxoCaixa->empresa_id ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->razao_social }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="planoDeConta">Plano de conta:</label>
                        <select class="form-control" id="planoDeConta" name="plano_de_conta_id">
                            <option value="">Selecione</option>
                            @foreach ($planoDeContas as $planoDeConta)
                                <option value="{{ $planoDeConta->id }}" {{ old('plano_de_conta_id', $fluxoCaixa->plano_de_conta_id ?? '') == $planoDeConta->id ? 'selected' : '' }}>
                                    {{ $planoDeConta->descricao }}
                                </option>
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


<div class="modal fade" id="editContasAPagarModal{{$contasAPagar->conta_id}}" tabindex="-1" aria-labelledby="editContasAPagarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContasAPagarModalLabel">Atualizar Conta a Pagar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editContasAPagarForm{{$contasAPagar->conta_id}}" action="{{ route('contasAPagar.update', $contasAPagar->conta_id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" name="descricao" required value="{{$contasAPagar->descricao}}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor">Valor:</label>
                            <input type="number" class="form-control" name="valor" step="0.01" value="{{$contasAPagar->valor}}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valorPago">Valor Pago:</label>
                            {{-- Desabilitado, pois o pagamento é feito em outra ação --}}
                            <input type="number" class="form-control" name="valor_pago" step="0.01" value="{{$contasAPagar->valor_pago}}" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dataVencimento">Data de Vencimento:</label>
                            {{-- CORREÇÃO: Formatação da data para o input type="date" --}}
                            <input type="date" class="form-control" name="data_vencimento" value="{{ \Carbon\Carbon::parse($contasAPagar->data_vencimento)->format('Y-m-d') }}">
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="dataPagamento">Data do Pagamento:</label>
                            <input type="date" class="form-control" name="data_pagamento" value="{{ $contasAPagar->data_pagamento ? \Carbon\Carbon::parse($contasAPagar->data_pagamento)->format('Y-m-d') : '' }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status">Situação</label>
                        <select class="form-control" name="status" required>
                            <option value="pendente" {{ old('status', $contasAPagar->status) == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="finalizado" {{ old('status', $contasAPagar->status) == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="planoDeConta">Plano de Contas</label>
                        {{-- CORREÇÃO: name="plano_de_contas_id" e a variável de comparação --}}
                        <select class="form-control" name="plano_de_contas_id" required>
                            <option value="">Selecione</option>
                            @foreach ($planoDeContas as $plano)
                                <option value="{{ $plano->id }}"
                                    {{ (old('plano_de_contas_id', $contasAPagar->plano_de_contas_id) == $plano->id) ? 'selected' : '' }}>
                                    {{ $plano->descricao }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fornecedor">Fornecedor</label>
                        <select class="form-control" name="fornecedor_id">
                            <option value="">Selecione</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}" 
                                    {{ old('fornecedor_id', $contasAPagar->fornecedor_id) == $fornecedor->id ? 'selected' : '' }}>
                                    {{ $fornecedor->nome_fantasia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
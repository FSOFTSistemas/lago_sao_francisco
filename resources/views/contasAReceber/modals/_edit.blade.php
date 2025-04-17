<div class="modal fade" id="editContasAReceberModal{{$contasAReceber->id}}" tabindex="-1" aria-labelledby="editContasAReceberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContasAReceberModalLabel">Atualizar Contas A Receber</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editContasAReceberForm" action="{{ route('contasAReceber.update',$contasAReceber->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" required value="{{$contasAReceber->descricao}}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor">Valor:</label>
                            <input type="text" class="form-control" id="valor" name="valor" value="{{$contasAReceber->valor}}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="valorRecebido">Valor Recebido:</label>
                            <input type="text" class="form-control" id="valorRecebido" name="valor_recebido" value="{{$contasAReceber->valor_recebido}}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dataVencimento">Data de Vencimento:</label>
                            <input type="date" class="form-control" id="dataVencimento" name="data_vencimento" value="{{$contasAReceber->data_vencimento}}">
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="dataRecebimento">Data do Recebimento:</label>
                            <input type="date" class="form-control" id="dataRecebimento" name="data_recebimento" value="{{$contasAReceber->data_recebimento}}">
                        </div>
                    </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo">Situação</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="pendente" {{ old('status', $contasAReceber->status) == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="finalizado" {{ old('status', $contasAReceber->status) == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="parcela">Parcelas:</label>
                                <select class="form-control" id="parcela" name="parcela">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ old('parcela', $contasAReceber->parcela ?? '') == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="planoDeConta">Plano de contas</label>
                            <select class="form-control" id="planoDeConta" name="plano_de_contas_id" required>
                                <option value="">Selecione</option>
                                @foreach ($planoDeContas as $planoDeConta)
                                    <option value="{{ $planoDeConta->id }}" 
                                        {{ old('plano_de_contas_id', $contasAReceber->plano_de_contas_id ?? '') == $planoDeConta->id ? 'selected' : '' }}>
                                        {{ $planoDeConta->descricao }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    
        
                        <div class="mb-3">
                            <label for="cliente">Cliente</label>
                            <select class="form-control" id="cliente" name="cliente_id">
                                <option value="">Selecione</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" 
                                        {{ old('cliente_id', $contasAReceber->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->apelido_nome_fantasia }}
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


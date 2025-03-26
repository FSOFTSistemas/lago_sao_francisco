<div class="modal fade" id="editContasAPagarModal{{$contasAPagar->id}}" tabindex="-1" aria-labelledby="editContasAPagarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContasAPagarModalLabel">Editar Contas A Pagar</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editContasAPagarForm" action="{{ route('contasAPagar.update',$contasAPagar->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" required value="{{$contasAPagar->descricao}}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor">Valor:</label>
                            <input type="text" class="form-control" id="valor" name="valor" value="{{$contasAPagar->valor}}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valorPago">Valor Pago:</label>
                            <input type="text" class="form-control" id="valorPago" name="valor_pago" value="{{$contasAPagar->valor_pago}}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dataVencimento">Data de Vencimento:</label>
                            <input type="date" class="form-control" id="dataVencimento" name="data_vencimento" value="{{$contasAPagar->data_vencimento}}">
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="dataPagamento">Data do Pagamento:</label>
                            <input type="date" class="form-control" id="dataPagamento" name="data_pagamento" value="{{$contasAPagar->data_pagamento}}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tipo">Situação</label>
                        <select class="form-control" id="status" name="status" required">
                            <option value="pendente">Pendente</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="planoDeConta">Plano de contas</label>
                        <select class="form-control" id="planoDeConta" name="plano_de_contas_pai" required value="{{$contasAPagar->plano_de_contas_pai}}">
                            <option value="">Selecione</option>
                            @foreach ($planoDeContas as $planoDeConta)
                                <option value="{{ $planoDeConta->id }}">{{ $planoDeConta->descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fornecedor">Fornecedor</label>
                        <select class="form-control" id="fornecedor" name="fornecedor_id" required value="{{$contasAPagar->fornecedor_id}}">
                            <option value="">Selecione</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->razao_social }}</option>
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


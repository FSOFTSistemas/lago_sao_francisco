<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showContasAPagar{{ $contasAPagar->id }}" tabindex="-1" aria-labelledby="shoswContasAPagarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showContasAPagarLabel">Detalhes das Contas A Pagar</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Descrição:</strong> <span id="descricao">{{$contasAPagar->descricao}}</span></p>
                <p><strong>Valor:</strong> <span id="valor">{{$contasAPagar->valor}}</span></p>
                <p><strong>Valor Pago:</strong> <span id="valorPago">{{$contasAPagar->valor_pago}}</span></p>
                <p><strong>Data de Vencimento:</strong> <span id="dataVencimento">{{$contasAPagar->data_vencimento}}</span></p>
                <p><strong>Data do Pagamento:</strong> <span id="dataPagamento">{{$contasAPagar->data_pagamento}}</span></p>
                <p><strong>Situação:</strong> <span id="status">{{$contasAPagar->status}}</span></p>
                <p><strong>Plano de Contas:</strong> <span id="planoDeContas">{{$contasAPagar->plano_de_contas_pai}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$contasAPagar->empresa_id}}</span></p>
            </div>
        </div>
    </div>
</div>

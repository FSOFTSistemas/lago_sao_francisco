<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showContasAReceber{{ $contasAReceber->id }}" tabindex="-1" aria-labelledby="shoswContasAReceberLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showContasAReceberLabel">Detalhes das Contas A Receber</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Descrição:</strong> <span id="descricao">{{$contasAReceber->descricao}}</span></p>
                <p><strong>Valor:</strong> <span id="valor">{{$contasAReceber->valor}}</span></p>
                <p><strong>Valor Recebido:</strong> <span id="valorPago">{{$contasAReceber->valor_pago}}</span></p>
                <p><strong>Data de Vencimento:</strong> <span id="dataVencimento">{{$contasAReceber->data_vencimento}}</span></p>
                <p><strong>Data do Pagamento:</strong> <span id="dataPagamento">{{$contasAReceber->data_pagamento}}</span></p>
                <p><strong>Situação:</strong> <span id="status">{{$contasAReceber->status}}</span></p>
                <p><strong>Plano de Contas:</strong> <span id="planoDeContas">{{$contasAReceber->plano_de_contas_pai}}</span></p>
                <p><strong>Parcelas:</strong><span id="parcela">{{$contasAReceber->parcela}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$contasAReceber->empresa_id}}</span></p>
            </div>
        </div>
    </div>
</div>

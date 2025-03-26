<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showContasAReceber{{ $contasAReceber->id }}" tabindex="-1" aria-labelledby="shoswContasAReceberLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showContasAReceberLabel">Detalhes das Contas A Receber</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Descrição:</strong> <span id="descricao">{{$contasAReceber->descricao}}</span></p>
                <p><strong>Valor:</strong> <span id="valor">R${{$contasAReceber->valor}}</span></p>
                <p><strong>Valor Recebido:</strong> <span id="valorRecebido">R${{$contasAReceber->valor_recebido}}</span></p>
                <p><strong>Data de Vencimento:</strong> <span id="dataVencimento">{{ Illuminate\Support\Carbon::parse($contasAReceber->data_vencimento)->format('d/m/Y') }}</span></p>
                <p><strong>Data do Recebimento:</strong> <span id="dataRecebimento">{{Illuminate\Support\Carbon::parse($contasAReceber->data_recebimento)->format('d/m/Y')}}</span></p>
                <p><strong>Situação:</strong> <span id="status">{{$contasAReceber->status}}</span></p>
                <p><strong>Plano de Contas:</strong> <span id="planoDeContas">{{ $contasAReceber->planoDeConta->descricao ?? '' }}</span></p>
                <p><strong>Parcelas:</strong><span id="parcela"> {{$contasAReceber->parcela}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$contasAReceber->daEmpresa->razao_social}}</span></p>
            </div>
        </div>
    </div>
</div>

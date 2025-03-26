<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showFluxoCaixa{{ $fluxoCaixa->id }}" tabindex="-1" aria-labelledby="showFluxoCaixaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showFluxoCaixaLabel">Detalhes do Fluxo de caixa</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Descrição:</strong> <span id="descricao">{{$fluxoCaixa->descricao}}</span></p>
                <p><strong>Valor:</strong> <span id="valor">R${{$fluxoCaixa->valor}}</span></p>
                <p><strong>Data:</strong> <span id="data">{{ \Illuminate\Support\Carbon::parse($fluxoCaixa->data)->format('d/m/Y') }}</span></p>
                <p><strong>Tipo:</strong> <span id="tipo">{{$fluxoCaixa->tipo}}</span></p>
                <p><strong>Caixa do dia:</strong> <span id="caixa">{{ \Illuminate\Support\Carbon::parse($fluxoCaixa->caixa->data_abertura)->format('d/m/Y') }}</span></p>
                <p><strong>Usuário:</strong> <span id="usuario">{{$users->name}}</span></p>
                <p><strong>Movimento:</strong> <span id="status">{{$fluxoCaixa->movimento->descricao}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$fluxoCaixa->daEmpresa->razao_social}}</span></p>
                <p><strong>Valor Total:</strong> <span id="valorTotal">{{$fluxoCaixa->valor_total}}</span></p>
                <p><strong>Plano de Conta:</strong> <span id="planoDeConta">{{$fluxoCaixa->planoDeConta->descricao}}</span></p>
            </div>
        </div>
    </div>
</div>

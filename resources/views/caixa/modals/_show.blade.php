<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showCaixa{{ $caixa->id }}" tabindex="-1" aria-labelledby="showCaixaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showCaixaLabel">Detalhes do Caixa</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Decrição:</strong> <span id="descricao">{{$caixa->descricao}}</span></p>
                <p><strong>Data de Abertura:</strong> <span id="dataAbertura">{{ \Illuminate\Support\Carbon::parse($caixa->data_abertura)->format('d/m/Y') }}</span></p>
                <p><strong>Data de Fechamento:</strong> <span id="dataFechamento">{{ \Illuminate\Support\Carbon::parse($caixa->data_fechamento)->format('d/m/Y') }}</span></p>
                <p><strong>Valor Inicial:</strong> <span id="valorInicial">R${{$caixa->valor_inicial}}</span></p>
                <p><strong>Valor Final:</strong> <span id="valorFinal">R${{$caixa->valor_final}}</span></p>
                <p><strong>Usuário de Abertura:</strong> <span id="usuarioAbertura">{{$users->name}}</span></p>
                <p><strong>Usuário de Fechamento:</strong> <span id="usuarioFechamento">{{$users->name}}</span></p>
                <p><strong>Situação:</strong> <span id="status">{{$caixa->status}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$caixa->empresa->razao_social}}</span></p>
                <p><strong>Observações:</strong> <span id="observacoes">{{$caixa->observacoes}}</span></p>
            </div>
        </div>
    </div>
</div>

<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showBanco{{ $banco->id }}" tabindex="-1" aria-labelledby="showBancoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showBancoLabel">Detalhes da Banco</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Descrição:</strong> <span id="descricao">{{$banco->descricao}}</span></p>
                <p><strong>Agência:</strong> <span id="agencia"></span>{{$banco->agencia}}</p>
                <p><strong>Digito Número da Agência:</strong> <span id="digitoAgencia"></span>{{$banco->digito_agencia}}</p>
                <p><strong>Número do banco:</strong> <span id="numeroBanco"></span>{{$banco->numero_banco}}</p>
                <p><strong>Digito Número do Banco:</strong> <span id="digitoBanco"></span>{{$banco->digito_numero}}</p>
                <p><strong>Número da Conta:</strong> <span id="numeroConta"></span>{{$banco->numero_conta}}</p>
                <p><strong>Digito Número da Conta:</strong> <span id="digitoConta"></span>{{$banco->digito_conta}}</p>
                <p><strong>UF da Agência:</strong> <span id="agenciaUf"></span>{{$banco->agencia_uf}}</p>
                <p><strong>Cidade da Agência:</strong> <span id="agenciaCidade"></span>{{$banco->agencia_cidade}}</p>
                <p><strong>Taxa:</strong> <span id="axa"></span>{{$banco->taxa}}</p>
            </div>
        </div>
    </div>
</div>

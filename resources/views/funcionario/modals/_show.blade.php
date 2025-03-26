<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showFuncionario{{ $funcionario->id }}" tabindex="-1" aria-labelledby="showFuncionarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showFuncionarioLabel">Detalhes do Funcionário</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nome:</strong> <span id="nome">{{$funcionario->nome}}</span></p>
                <p><strong>CPF:</strong> <span id="cpf">{{$funcionario->cpf}}</span></p>
                <p><strong>Data de Contratação:</strong> <span id="dataContratacao">{{\Illuminate\Support\Carbon::parse($funcionario->data_contratacao)->format('d/m/Y') }}</span></p>
                <p><strong>Salário:</strong> <span id="salario">R${{$funcionario->salario}}</span></p>
                <p><strong>Setor:</strong> <span id="setor">{{$funcionario->setor}}</span></p>
                <p><strong>Cargo:</strong> <span id="cargo">{{$funcionario->cargo}}</span></p>
                <p><strong>Situação:</strong> <span id="status">{{$funcionario->status}}</span></p>
                <p><strong>Empresa:</strong> <span id="empresa">{{$funcionario->empresa->razao_social}}</span></p>
                <p><strong>Endereço:</strong> <span id="endereco">{{$funcionario->endereco->logradouro}}, {{$funcionario->endereco->numero}}</span></p>
            </div>
        </div>
    </div>
</div>

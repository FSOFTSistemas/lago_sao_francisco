<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showCliente{{ $cliente->id }}" tabindex="-1" aria-labelledby="showClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showClienteLabel">Detalhes da Cliente</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nome/Razão Social:</strong> <span id="nomeRazaoSocial">{{$cliente->nome_razao_social}}</span></p>
                <p><strong>Apelido/Nome Fantasia:</strong> <span id="apelidoNomeFantasia"></span>{{$cliente->apelido_nome_fantasia}}</p>
                <p><strong>Telefone:</strong> <span id="telefone"></span>{{$cliente->telefone ?? " Não informado"}}</p>
                <p><strong>Whatsapp:</strong> <span id="whatsapp"></span>{{$cliente->whatsapp ?? " Não informado"}}</p>
                {!! $cliente->data_nascimento ? '<p><strong>Data de Nascimento:</strong> <span id="dataNascimento">' . \Illuminate\Support\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') . '</span></p>' : '' !!}

                <p><strong>Endereço:</strong> <span id="endereco"></span>{{$cliente->endereco->logradouro}}</p>
                <p><strong>CPF/CNPJ:</strong> <span id="cpfCnpj"></span>{{$cliente->cpf_cnpj}}</p>
                <p><strong>RG/Inscrição Estadual:</strong> <span id="rgIe"></span>{{$cliente->rg_ie}}</p>
                <p><strong>Tipo:</strong> <span id="tipo"></span>{{$cliente->tipo}}</p>
            </div>
        </div>
    </div>
</div>

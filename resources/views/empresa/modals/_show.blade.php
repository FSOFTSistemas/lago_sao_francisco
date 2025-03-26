<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showEmpresa{{ $empresa->id }}" tabindex="-1" aria-labelledby="showEmpresaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showEmpresaLabel">Detalhes da Empresa</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Razão Social:</strong> <span id="razaoSocial">{{$empresa->razao_social}}</span></p>
                <p><strong>Nome Fantasia:</strong> <span id="nomeFantasia"></span>{{$empresa->nome_fantasia}}</p>
                <p><strong>CNPJ:</strong> <span id="cnpj"></span>{{$empresa->cnpj}}</p>
                <p><strong>Inscrição Estadual:</strong> <span id="inscricaoEstadual"></span>{{$empresa->inscricao_estadual}}</p>
            </div>
        </div>
    </div>
</div>

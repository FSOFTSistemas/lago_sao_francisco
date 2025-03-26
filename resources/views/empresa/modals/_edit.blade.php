<div class="modal fade" id="editEmpresaModal{{$empresa->id}}" tabindex="-1" aria-labelledby="editEmpresaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmpresaModalLabel">Editar Empresa</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmpresaForm" action="{{ route('empresa.update',$empresa->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="razao_social" class="form-label">Razão Social</label>
                        <input type="text" class="form-control" name="razao_social" value={{$empresa->razao_social}} required>
                    </div>
                    <div class="mb-3">
                        <label for="nomeFantasia">Nome Fantasia:</label>
                        <input type="text" class="form-control" id="nomeFantasia" name="nome_fantasia" value="{{$empresa->nome_fantasia}}">
                      </div>
                      <div class="mb-3">
                        <label for="cnpj">CNPJ:</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj" value="{{$empresa->cnpj}}">
                      </div>
                      <div class="mb-3">
                        <label for="inscricaoEstadual">Inscrição Estadual:</label>
                        <input type="text" class="form-control" id="inscricaoEstadual" name="inscricao_estadual" value="{{$empresa->inscricao_estadual}}">
                      </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>


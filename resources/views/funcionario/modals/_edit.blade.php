<div class="modal fade" id="editFuncionarioModal{{$funcionario->id}}" tabindex="-1" aria-labelledby="editFuncionarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFuncionarioModalLabel">Editar Funcionário</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFuncionarioForm" action="{{ route('funcionario.update',$funcionario->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nome">Nome:</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{$funcionario->nome}}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cpf">CPF:</label>
                            <input type="text" class="form-control" id="valor" name="cpf" value="{{$funcionario->cpf}}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="salario">Salário:</label>
                            <input type="text" class="form-control" id="salario" name="salario" value="{{$funcionario->salario}}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dataContratacao">Data de Contratação:</label>
                            <input type="date" class="form-control" id="dataContratacao" name="data_contratacao" requried value="{{$funcionario->data_contratacao}}">
                        </div>
                    
                        <div class="col-md-6 mb-3">
                            <label for="empresa">Empresa:</label>
                            <select class="form-control" id="empresa" name="empresa_id" required>
                                <option value="">Selecione</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->razao_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="setor">Setor:</label>
                            <input type="text" class="form-control" id="setor" name="setor" value="{{$funcionario->setor}}" required>
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="cargo">Cargo:</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" value="{{$funcionario->cargo}}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo">Situação:</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    

                    

                    <div class="mb-3">
                        <label for="endereco">Endereço:</label>
                        <select class="form-control" id="endereco" name="endereco_id">
                            <option value="">Selecione</option>
                            @foreach ($enderecos as $endereco)
                                <option value="{{ $endereco->id }}">{{ $endereco->logradouro }}</option>
                            @endforeach
                        </select>
                    </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
            </div>

        </div>
    </div>
</div>


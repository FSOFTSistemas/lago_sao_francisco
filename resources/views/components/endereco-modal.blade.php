<div class="modal fade" id="enderecoModal" tabindex="-1" aria-labelledby="enderecoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('endereco.store') }}" id="formEndereco">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Novo Endereço</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <div class="input-group">
                            <input type="text" name="cep" id="cep" class="form-control"
                                placeholder="Digite o CEP" required />
                            <button type="button" class="btn btn-info" id="buscarCep">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <x-adminlte-input id="logradouro" name="logradouro" label="Logradouro"
                        placeholder="Digite o logradouro" required />
                    <x-adminlte-input id="numero" name="numero" label="Número" placeholder="Digite o número"
                        required />
                    <x-adminlte-input id="bairro" name="bairro" label="Bairro" placeholder="Digite o bairro"
                        required />
                    <x-adminlte-input id="cidade" name="cidade" label="Cidade" placeholder="Digite a cidade"
                        required />
                    <x-adminlte-input id="uf" name="uf" label="Estado" placeholder="Digite o estado"
                        required />
                    <x-adminlte-input id="ibge" name="ibge" label="ibge" placeholder="Digite o ebge"
                        required />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Salvar Endereço</button>
                </div>
            </form>
        </div>
    </div>
</div>

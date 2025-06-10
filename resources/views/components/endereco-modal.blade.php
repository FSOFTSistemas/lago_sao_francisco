<div class="modal fade" id="enderecoModal" tabindex="-1" aria-labelledby="enderecoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('endereco.store') }}" id="formEndereco">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Novo Endereço</h5>
                    <button class="btn-primary" type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <div class="input-group">
                            <input type="text" name="cep" id="cep" class="form-control"
                                placeholder="Digite o CEP" />
                            {{-- <button type="button" class="btn btn-info" id="buscarCep">
                                <i class="fas fa-search"></i> Buscar
                            </button> --}}
                        </div>
                    </div>
                    <x-adminlte-input id="logradouro" name="logradouro" label="Logradouro"
                        placeholder="Digite o logradouro" />
                    <x-adminlte-input id="numero" name="numero" label="Número" placeholder="Digite o número"
                        />
                    <x-adminlte-input id="bairro" name="bairro" label="Bairro" placeholder="Digite o bairro"
                    />
                    <x-adminlte-input id="cidade" name="cidade" label="Cidade" placeholder="Digite a cidade"
                    />
                        <x-adminlte-select id="uf" name="uf" label="Estado">
                            <option value="">Selecione um estado</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AP">AP</option>
                            <option value="AM">AM</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </x-adminlte-select>
                    <x-adminlte-input id="ibge" name="ibge" label="ibge" placeholder="Digite o ebge"
                         />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar Endereço</button>
                </div>
            </form>
        </div>
    </div>
</div>

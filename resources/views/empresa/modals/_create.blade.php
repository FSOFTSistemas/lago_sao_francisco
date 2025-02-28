  <!-- Modal -->
  <div class="modal fade" id="createEmpresaModal" data-backdrop="static" tabindex="-1" role="dialog"
      aria-labelledby="createEmpresaModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createEmpresaModalLabel">Cadastro de Empresa</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="createEmpresaForm" action="{{ route('empresa.store') }}" method="POST">
                      @csrf
                      <div class="mb-3">
                          <label for="razaoSocial">Razão Social:</label>
                          <input type="text" class="form-control" id="razaoSocial" name="razao_social" required>
                      </div>
                      <div class="mb-3">
                          <label for="nomeFantasia">Nome Fantasia:</label>
                          <input type="text" class="form-control" id="nomeFantasia" name="nome_fantasia">
                      </div>
                      <div class="mb-3">
                          <label for="cnpj">CNPJ:</label>
                          <input type="text" class="form-control" id="cnpj" name="cnpj">
                      </div>
                      <div class="mb-3">
                          <label for="inscricaoEstadual">Inscrição Estadual:</label>
                          <input type="text" class="form-control" id="inscricaoEstadual" name="inscricao_estadual">
                      </div>
                      <div class="mb-3">
                          <label for="endereco_id">Endereço</label>
                          <div class="input-group">
                              <select class="form-control" id="endereco_id" name="endereco_id" required>
                                  <option value="" disabled {{ !isset($empresa->endereco_id) ? 'selected' : '' }}>
                                      Selecione um endereço...</option>
                                  @foreach ($enderecos as $endereco)
                                      <option value="{{ $endereco->id }}"
                                          {{ isset($empresa->endereco_id) && $empresa->endereco_id == $endereco->id ? 'selected' : '' }}>
                                          {{ $endereco->logradouro }}, {{ $endereco->numero }} - {{ $endereco->cidade }}
                                      </option>
                                  @endforeach
                              </select>
                              <button type="button" class="btn btn-success" data-toggle="modal"
                                  data-target="#enderecoModal">
                                  <i class="fas fa-plus"></i> Novo Endereço
                              </button>
                          </div>
                      </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                          <button type="submit" class="btn btn-primary">Criar</button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </div>


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
                      <x-adminlte-input id="estado" name="estado" label="Estado" placeholder="Digite o estado"
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

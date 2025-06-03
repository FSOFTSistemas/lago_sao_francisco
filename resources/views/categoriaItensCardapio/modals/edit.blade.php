<div class="modal fade" id="editCategoriaModal{{ $categoria->id }}" tabindex="-1" role="dialog" aria-labelledby="editCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoriaModalLabel">Editar Categoria: {{ $categoria->nome_categoria_item }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('categoriaItensCardapio.update', $categoria->id) }}" method="POST" class="edit-categoria-form">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nome_categoria">Nome da Categoria*</label>
                                <input type="text" class="form-control" id="edit_nome_categoria" 
                                       name="nome_categoria_item" value="{{ old('nome_categoria_item', $categoria->nome_categoria_item) }}" 
                                       required minlength="3" maxlength="100">
                                @error('nome_categoria_item')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_secao_cardapio">Seção do Cardápio*</label>
                                <select class="form-control" id="edit_secao_cardapio" name="sessao_cardapio_id" required>
                                    <option value="">Selecione a Seção</option>
                                    @foreach($secoes as $secao)
                                        <option value="{{ $secao->id }}" {{ old('sessao_cardapio_id', $categoria->sessao_cardapio_id) == $secao->id ? 'selected' : '' }}>
                                            {{ $secao->nome_secao_cardapio }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sessao_cardapio_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_escolhas_permitidas">Nº de Escolhas Permitidas*</label>
                                <input type="number" class="form-control" id="edit_escolhas_permitidas" 
                                       name="numero_escolhas_permitidas" value="{{ old('numero_escolhas_permitidas', $categoria->numero_escolhas_permitidas) }}" 
                                       min="1" max="10" required>
                                @error('numero_escolhas_permitidas')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_ordem_exibicao">Ordem de Exibição*</label>
                                <input type="number" class="form-control" id="edit_ordem_exibicao" 
                                       name="ordem_exibicao" value="{{ old('ordem_exibicao', $categoria->ordem_exibicao) }}" 
                                       min="1" required>
                                @error('ordem_exibicao')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Escolha Exclusiva?</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="edit_escolha_exclusiva" 
                                           name="eh_grupo_escolha_exclusiva" value="1" 
                                           {{ old('eh_grupo_escolha_exclusiva', $categoria->eh_grupo_escolha_exclusiva) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="edit_escolha_exclusiva">Sim</label>
                                </div>
                                @error('eh_grupo_escolha_exclusiva')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_refeicao_principal">Refeição Principal Relacionada</label>
                                <select class="form-control" id="edit_refeicao_principal" name="refeicao_principal_id">
                                    <option value="">Nenhuma</option>
                                    @foreach($refeicoes as $refeicao)
                                        <option value="{{ $refeicao->id }}" {{ old('refeicao_principal_id', $categoria->refeicao_principal_id) == $refeicao->id ? 'selected' : '' }}>
                                            {{ $refeicao->NomeOpcaoRefeicao }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('refeicao_principal_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
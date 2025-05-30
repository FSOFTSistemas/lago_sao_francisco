<div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editItemModalLabel">Editar: {{ $item->nome_item }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('itemCardapio.update', $item->id) }}" method="POST" class="edit-item-form">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit_nome_item">Nome do Item*</label>
                                <input type="text" class="form-control" id="edit_nome_item" 
                                       name="nome_item" value="{{ old('nome_item', $item->nome_item) }}" 
                                       required minlength="3" maxlength="100">
                                @error('nome_item')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_tipo_item">Tipo*</label>
                                <select class="form-control" id="edit_tipo_item" name="tipo_item" required>
                                    <option value="">Selecione...</option>
                                    <option value="Entrada" {{ old('tipo_item', $item->tipo_item) == 'Entrada' ? 'selected' : '' }}>Entrada</option>
                                    <option value="Prato Principal" {{ old('tipo_item', $item->tipo_item) == 'Prato Principal' ? 'selected' : '' }}>Prato Principal</option>
                                    <option value="Sobremesa" {{ old('tipo_item', $item->tipo_item) == 'Sobremesa' ? 'selected' : '' }}>Sobremesa</option>
                                    <option value="Bebida" {{ old('tipo_item', $item->tipo_item) == 'Bebida' ? 'selected' : '' }}>Bebida</option>
                                </select>
                                @error('tipo_item')
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
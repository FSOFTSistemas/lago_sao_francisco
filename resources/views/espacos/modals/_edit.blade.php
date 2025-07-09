<div class="modal fade" id="editEspacoModal{{ $espaco->id }}" tabindex="-1" aria-labelledby="editEspacoModalLabel{{ $espaco->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEspacoModalLabel{{ $espaco->id }}">Atualizar Espaços</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEspacoForm{{ $espaco->id }}" action="{{ route('espaco.update',$espaco->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="form-group d-flex align-items-center p-3 rounded bg-light">
                        <label for="capela{{ $espaco->id }}" class="form-label mb-0 mr-3">Capela?</label>

                        <label class="switch-slide mb-0">
                            <input type="hidden" name="capela" value="0">
                            <input type="checkbox" id="capela{{ $espaco->id }}" value="1" name="capela" @checked(old('capela', $espaco->capela))>
                            <span class="slider-slide"></span>
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="nome{{ $espaco->id }}">Nome:</label>
                        <input type="text" class="form-control" id="nome{{ $espaco->id }}" name="nome" value="{{ $espaco->nome }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="valor_semana{{ $espaco->id }}" id="labelSemana{{ $espaco->id }}">Valor na Semana (Seg a Qui):</label>
                        <input type="text" class="form-control" id="valor_semana{{ $espaco->id }}" name="valor_semana" value="{{ $espaco->valor_semana }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor_fim{{ $espaco->id }}" id="labelFim{{ $espaco->id }}">Valor no Fim de Semana (Sex a Dom):</label>
                        <input type="text" class="form-control" id="valor_fim{{ $espaco->id }}" name="valor_fim" value="{{ $espaco->valor_fim }}" required>
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

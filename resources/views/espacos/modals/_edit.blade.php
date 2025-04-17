<div class="modal fade" id="editEspacoModal{{$espaco->id}}" tabindex="-1" aria-labelledby="editEspacoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEspacoModalLabel">Atualizar Espaços</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEspacoForm" action="{{ route('espaco.update',$espaco->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nome">Nome:</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{$espaco->nome}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="valor">Valor:</label>
                        <input type="text" class="form-control" id="valor" name="valor" value="{{$espaco->valor}}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo">Situação:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="disponivel" {{ $espaco->status == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                            <option value="alugado" {{ $espaco->status == 'alugado' ? 'selected' : '' }}>Alugado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="empresa">Empresa</label>
                        <select class="form-control select2" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ ($espaco->empresa_id ?? '') == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->nome_fantasia }}
                                </option>
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


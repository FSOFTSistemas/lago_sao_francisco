<div class="modal fade" id="fecharCaixaModal{{ $caixa->id }}" tabindex="-1" aria-labelledby="fecharCaixaModalLabel{{ $caixa->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        
            <div class="modal-header">
                <h5 class="modal-title" id="fecharCaixaModalLabel{{ $caixa->id }}">Fechar Caixa #{{ $caixa->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <form method="POST" action="{{ route('caixas.fechar', $caixa->id) }}">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="valor_final{{ $caixa->id }}" class="form-label">Saldo Atual</label>
                        <input type="text" class="form-control" name="valor_final" id="valor_final{{ $caixa->id }}" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Resumo por forma de pagamento:</label>
                        <ul class="list-group" id="resumoFormas{{ $caixa->id }}">
                            <li class="list-group-item">Carregando...</li>
                        </ul>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Fechar Caixa</button>
                </div>
            </form>
        </div>
    </div>
</div>

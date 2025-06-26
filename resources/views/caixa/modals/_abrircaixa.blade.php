<div class="modal fade" id="abrirCaixaModal{{ $caixa->id }}" tabindex="-1" aria-labelledby="abrirCaixaLabel{{ $caixa->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('caixas.abrir', $caixa->id) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="abrirCaixaLabel{{ $caixa->id }}">Abrir Caixa #{{ $caixa->id }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label for="valor_inicial{{ $caixa->id }}">Valor Inicial:</label>
          <input type="number" step="0.01" min="0" name="valor_inicial" id="valor_inicial{{ $caixa->id }}" class="form-control" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Confirmar Abertura</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

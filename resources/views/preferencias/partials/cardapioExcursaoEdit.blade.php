<div class="modal fade" id="modalEditarCardapio{{ $cardapio->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('cardapios-excursao.update', $cardapio) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_origem" value="modalEditarCardapio{{ $cardapio->id }}">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Editar cardápio de excursão</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @include('preferencias.partials.cardapioExcursaoForm', ['prefixo' => 'editar_'.$cardapio->id])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

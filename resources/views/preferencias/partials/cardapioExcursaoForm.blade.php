@php
    $origemFormulario = $prefixo === 'novo'
        ? 'modalCriarCardapio'
        : 'modalEditarCardapio'.str_replace('editar_', '', $prefixo);
    $usarDadosAnteriores = old('_origem') === $origemFormulario;
@endphp

<div class="form-group">
    <label for="nome_{{ $prefixo }}">Nome <span class="text-danger">*</span></label>
    <input type="text" id="nome_{{ $prefixo }}" name="nome"
        class="form-control @error('nome') is-invalid @enderror"
        value="{{ $usarDadosAnteriores ? old('nome') : ($cardapio->nome ?? '') }}" maxlength="255" required>
    @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

@php
    $itensCardapio = $usarDadosAnteriores ? old('itens') : null;
    if ($itensCardapio === null) {
        $itensCardapio = isset($cardapio)
            ? preg_split('/\r\n|\r|\n/', $cardapio->descricao_cardapio, -1, PREG_SPLIT_NO_EMPTY)
            : [];
    }
@endphp

<div class="form-group itens-cardapio-editor">
    <label>Itens do cardápio <span class="text-danger">*</span></label>
    <div class="input-group mb-2">
        <input type="text" class="form-control novo-item-cardapio" maxlength="255"
            placeholder="Digite um item e pressione Enter ou clique em Adicionar">
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-success adicionar-item-cardapio">
                <i class="fas fa-plus mr-1"></i> Adicionar
            </button>
        </div>
    </div>
    <div class="itens-cardapio-lista border rounded px-2 py-1">
        @foreach ($itensCardapio as $indice => $item)
            <div class="d-flex align-items-center py-1 item-cardapio-linha {{ $loop->last ? '' : 'border-bottom' }}">
                <span class="badge badge-light border mr-2 numero-item">{{ $indice + 1 }}</span>
                <span class="flex-grow-1 texto-item-cardapio">{{ $item }}</span>
                <input type="hidden" name="itens[]" value="{{ $item }}">
                <button type="button" class="btn btn-link text-danger btn-sm p-1 remover-item-cardapio" title="Remover item">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endforeach
        <div class="text-muted text-center py-2 lista-itens-vazia">Nenhum item adicionado.</div>
    </div>
    @error('itens') <div class="text-danger small">{{ $message }}</div> @enderror
    <small class="form-text text-muted">Adicione separadamente todos os pratos, acompanhamentos, sobremesas e bebidas incluídos.</small>
</div>

<div class="form-row align-items-end">
    <div class="form-group col-md-6">
        <label for="valor_{{ $prefixo }}">Valor por pessoa <span class="text-danger">*</span></label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
            <input type="number" id="valor_{{ $prefixo }}" name="valor_por_pessoa" min="0.01" max="99999999.99" step="0.01"
                class="form-control @error('valor_por_pessoa') is-invalid @enderror"
                value="{{ $usarDadosAnteriores ? old('valor_por_pessoa') : ($cardapio->valor_por_pessoa ?? '') }}" required>
            @error('valor_por_pessoa') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group col-md-6">
        <div class="custom-control custom-switch mb-2">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" class="custom-control-input" id="ativo_{{ $prefixo }}" name="ativo" value="1"
                @checked($usarDadosAnteriores ? old('ativo') : ($cardapio->ativo ?? true))>
            <label class="custom-control-label" for="ativo_{{ $prefixo }}">Disponível para novas excursões</label>
        </div>
    </div>
</div>

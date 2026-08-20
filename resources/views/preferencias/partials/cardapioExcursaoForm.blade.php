<div class="form-group">
    <label for="nome_{{ $prefixo }}">Nome <span class="text-danger">*</span></label>
    <input type="text" id="nome_{{ $prefixo }}" name="nome"
        class="form-control @error('nome') is-invalid @enderror"
        value="{{ old('nome', $cardapio->nome ?? '') }}" maxlength="255" required>
    @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="descricao_{{ $prefixo }}">Descrição do cardápio <span class="text-danger">*</span></label>
    <textarea id="descricao_{{ $prefixo }}" name="descricao_cardapio" rows="6" maxlength="5000"
        class="form-control @error('descricao_cardapio') is-invalid @enderror"
        placeholder="Descreva os pratos, acompanhamentos, sobremesas e bebidas incluídos..." required>{{ old('descricao_cardapio', $cardapio->descricao_cardapio ?? '') }}</textarea>
    <small class="form-text text-muted">Esta descrição será apresentada ao selecionar o almoço na excursão.</small>
    @error('descricao_cardapio') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-row align-items-end">
    <div class="form-group col-md-6">
        <label for="valor_{{ $prefixo }}">Valor por pessoa <span class="text-danger">*</span></label>
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
            <input type="number" id="valor_{{ $prefixo }}" name="valor_por_pessoa" min="0.01" max="99999999.99" step="0.01"
                class="form-control @error('valor_por_pessoa') is-invalid @enderror"
                value="{{ old('valor_por_pessoa', $cardapio->valor_por_pessoa ?? '') }}" required>
            @error('valor_por_pessoa') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group col-md-6">
        <div class="custom-control custom-switch mb-2">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" class="custom-control-input" id="ativo_{{ $prefixo }}" name="ativo" value="1"
                @checked(old('ativo', $cardapio->ativo ?? true))>
            <label class="custom-control-label" for="ativo_{{ $prefixo }}">Disponível para novas excursões</label>
        </div>
    </div>
</div>

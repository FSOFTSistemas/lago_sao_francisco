@extends('adminlte::page')

@section('title', isset($cardapio) ? 'Editar Cardápio' : 'Novo Cardápio')

@section('content_header')
    <h1>{{ isset($cardapio) ? 'Editar Cardápio' : 'Novo Cardápio de Buffet' }}</h1>
    <hr>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">
            {{ isset($cardapio) ? 'Editar informações do cardápio' : 'Preencha os dados do cardápio' }}
        </h3>
    </div>
    <div class="card-body">
        <form id="createCardapioForm" action="{{ isset($cardapio) ? route('cardapios.update', $cardapio->id) : route('cardapios.store') }}" method="POST">
            @csrf
            @if (isset($cardapio))
                @method('PUT')
            @endif

            <div class="form-group row">
                <label class="col-md-3 label-control">*Nome:</label>
                <div class="col-md-4">
                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $cardapio->nome ?? '') }}" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 label-control">Observações:</label>
                <div class="col-md-6">
                    <textarea name="observacoes" class="form-control">{{ old('observacoes', $cardapio->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div>
                <h4 class="form-section"><i class="fa fa-caret-down"></i> Categorias e Itens</h4>
                <hr>
            </div>

            @foreach ($categorias as $categoria)
                @php
                    $categoriaSelecionada = isset($selecionadas[$categoria->id]);
                    $quantidade = $selecionadas[$categoria->id] ?? 0;
                    $itensMarcados = $itensSelecionados[$categoria->id] ?? [];
                @endphp

                <div class="card mb-3 border border-primary">
                    <div class="card-header bg-light">
                        <div class="form-check">
                            <input class="form-check-input categoria-checkbox" type="checkbox" id="categoria_{{ $categoria->id }}"
                                name="categorias[{{ $categoria->id }}][ativa]" value="1"
                                {{ $categoriaSelecionada ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold" for="categoria_{{ $categoria->id }}">
                                {{ $categoria->nome }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label">Quantidade de itens permitidos:</label>
                            <div class="col-md-2">
                                <input type="number" name="categorias[{{ $categoria->id }}][quantidade]" min="0"
                                    value="{{ $quantidade }}" class="form-control quantidade-input"
                                    {{ $categoriaSelecionada ? '' : 'disabled' }}>
                            </div>
                        </div>

                        <div class="row">
                            @foreach ($categoria->itens as $item)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input item-checkbox categoria-{{ $categoria->id }}"
                                            type="checkbox"
                                            name="categorias[{{ $categoria->id }}][itens][]"
                                            value="{{ $item->id }}"
                                            id="item_{{ $categoria->id }}_{{ $item->id }}"
                                            {{ in_array($item->id, $itensMarcados) ? 'checked' : '' }}
                                            {{ $categoriaSelecionada ? '' : 'disabled' }}>
                                        <label class="form-check-label" for="item_{{ $categoria->id }}_{{ $item->id }}">
                                            {{ $item->nome }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            @endforeach

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Habilita/desabilita quantidade e itens de acordo com a categoria selecionada
        document.querySelectorAll('.categoria-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const categoriaId = this.id.split('_')[1];
                const inputs = document.querySelectorAll('.categoria-' + categoriaId);
                const quantidade = document.querySelector('input[name="categorias[' + categoriaId + '][quantidade]"]');
                if (this.checked) {
                    inputs.forEach(input => input.disabled = false);
                    if (quantidade) quantidade.disabled = false;
                } else {
                    inputs.forEach(input => input.disabled = true);
                    if (quantidade) quantidade.disabled = true;
                }
            });

            // Executar uma vez ao carregar para aplicar o estado atual
            checkbox.dispatchEvent(new Event('change'));
        });
    });
</script>
@stop

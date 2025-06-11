@extends('adminlte::page')

@section('title', isset($produto) ? 'Atualizar Produto' : 'Criar Produto')

@section('content_header')
    <h1>{{ isset($produto) ? 'Atualizar Produto' : 'Criar Produto' }}</h1>
@endsection

@section('content')
    <form action="{{ isset($produto) ? route('produto.update', $produto->id) : route('produto.store') }}" method="POST">
        @csrf
        @if (isset($produto))
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="categoria">* Categoria:</label>
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-control select2" id="categoria" name="categoria_produto_id" required>
                                <option value="">Selecione uma opção</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_produto_id', $produto->categoria_produto_id ?? '') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->descricao }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-success new" data-toggle="modal" data-target="#createCategoriaProdutoModal">
                                <i class="fas fa-plus"></i> Nova Categoria
                            </button>
                        </div>
                    </div>
                </div>

                <div id="camposExtra" style="display: none;">
                    <ul class="nav nav-tabs mt-4" id="produtoTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações
                                do Produto</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="dias-tab" data-toggle="tab" href="#dias" role="tab">Produto / Fiscal</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="produtoTabsContent">
                        {{-- Aba 1: Informações da Produto --}}
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="descricao">* Descrição:</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="descricao" name="descricao" required
                                        style="text-transform: uppercase" value="{{ $produto->descricao ?? '' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="ean">EAN:</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="ean" name="ean"
                                        value="{{ $produto->ean ?? '' }}" maxlength="13">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="precoCusto">* Preço de Custo:</label>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" class="form-control" id="precoCusto" name="preco_custo"
                                        required value="{{ isset($produto) ? number_format($produto->preco_custo, 2, '.', '') : '' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="precoVenda">* Preço de Venda:</label>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" class="form-control" id="precoVenda" name="preco_venda"
                                        required value="{{ isset($produto) ? number_format($produto->preco_venda, 2, '.', '') : '' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="comissao">* Comissão (%):</label>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                        id="comissao" name="comissao" required
                                        value="{{ isset($produto->comissao) ? number_format($produto->comissao, 2, '.', '') : '0.00' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="observacoes" class="col-md-3 label-control">Observações extras:</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" name="observacoes" rows="3" style="text-transform: uppercase">{{ old('observacoes', $produto->observacoes ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="form-label d-block label-control col-md-3">* Produto Ativo?</label>
                                <div class="col-md-3 form-check form-switch">
                                    <input type="hidden" name="ativo" value="0">
                                    <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo"
                                        value="1" {{ old('ativo', $produto->ativo ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                                        {{ old('ativo', $produto->ativo ?? true) ? 'Ativo' : 'Inativo' }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Aba 2: Produto Fiscal --}}
                        <div class="tab-pane fade" id="dias" role="tabpanel">
                            <div class="alert alert-secondary">
                                <strong>DICA:</strong> Preencha os dados fiscais conforme a tributação do produto.
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="cst">* CST:</label>
                                <div class="col-md-3">
                                    <select class="form-control select2" id="cst" name="cst" required>
                                        <option value="">Selecione uma opção</option>
                                        <option value="00" {{ old('cst', $produto->cst ?? '') == '00' ? 'selected' : '' }}>00 - Tributada integralmente</option>
                                        <option value="10" {{ old('cst', $produto->cst ?? '') == '10' ? 'selected' : '' }}>10 - ICMS ST</option>
                                        <option value="20" {{ old('cst', $produto->cst ?? '') == '20' ? 'selected' : '' }}>20 - Redução BC</option>
                                        <option value="40" {{ old('cst', $produto->cst ?? '') == '40' ? 'selected' : '' }}>40 - Isenta</option>
                                        <option value="90" {{ old('cst', $produto->cst ?? '') == '90' ? 'selected' : '' }}>90 - Outras</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="ncm">* NCM:</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="ncm" name="ncm" required value="{{ $produto->ncm ?? '' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="cfopInterno">* CFOP Interno:</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="cfopInterno" name="cfop_interno" required value="{{ $produto->cfop_interno ?? '' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="cfopExterno">* CFOP Externo:</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="cfopExterno" name="cfop_externo" required value="{{ $produto->cfop_externo ?? '' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="aliquota">* Alíquota:</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="aliquota" name="aliquota" required value="{{ $produto->aliquota ?? '20.5' }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="csosn">* CSOSN:</label>
                                <div class="col-md-3">
                                    <select class="form-control select2" id="csosn" name="csosn" required>
                                        <option value="">Selecione uma opção</option>
                                        <option value="101" {{ old('csosn', $produto->csosn ?? '') == '101' ? 'selected' : '' }}>101 - Com crédito</option>
                                        <option value="102" {{ old('csosn', $produto->csosn ?? '') == '102' ? 'selected' : '' }}>102 - Sem crédito</option>
                                        <option value="103" {{ old('csosn', $produto->csosn ?? '') == '103' ? 'selected' : '' }}>103 - Isento</option>
                                        <option value="500" {{ old('csosn', $produto->csosn ?? '') == '500' ? 'selected' : '' }}>500 - ICMS ST</option>
                                        <option value="900" {{ old('csosn', $produto->csosn ?? '') == '900' ? 'selected' : '' }}>900 - Outros</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- fim camposExtra -->
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
    @include('categoriaProduto.modals._create')
@endsection

@section('js')
    <script>
        function toggleCampos() {
            const categoria = document.getElementById('categoria');
            const camposExtra = document.getElementById('camposExtra');
            if (categoria.value) {
                camposExtra.style.display = 'block';
            } else {
                camposExtra.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            toggleCampos(); // para manter campos visíveis se vier com categoria já preenchida
            document.getElementById('categoria').addEventListener('change', toggleCampos);
        });
    </script>
@endsection

@extends('adminlte::page')

@section('title', isset($produto) ? 'Atualizar Produto' : 'Criar Produto')

@section('content_header')
    <h1>{{ isset($produto) ? 'Atualizar Produto' : 'Criar Produto' }}</h1>
@endsection

@section('content')
<form action="{{ isset($produto) ? route('produto.update', $produto->id) : route('produto.store') }}" method="POST">
    @csrf
    @if(isset($produto))
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="produtoTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações da Produto</a>
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
                            <input type="text" class="form-control" id="descricao" name="descricao" required value="{{ $produto->descricao ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="ean"> EAN:</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="ean" name="ean" value="{{ $produto->ean ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="categoria">* Categoria:</label>
                        <div class="col-md-3">
                            <select class="form-control select2" id="categoria" name="categoria" required>
                                <option value="">Selecione uma opção</option>
                                <option value="opcao1">Opção 1</option>
                                <option value="opcao2">Opção 2</option>
                                <option value="opcao3">Opção 3</option>
                            </select>
                        </div>
                    </div>
                    

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="precoCusto">* Preço de Custo:</label>
                        <div class="col-md-3">
                            <input type="number" step="0.01" class="form-control" id="precoCusto" name="preco_custo" required 
                                value="{{ isset($produto) ? number_format($produto->preco_custo, 2, '.', '') : '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="precoVenda">* Preço de Venda:</label>
                        <div class="col-md-3">
                            <input type="number" step="0.01" class="form-control" id="precoVenda" name="preco_venda" required 
                                value="{{ isset($produto) ? number_format($produto->preco_venda, 2, '.', '') : '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="comissao">* Comissão (%):</label>
                        <div class="col-md-3">
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="comissao" name="comissao" required 
                                value="{{ isset($produto->comissao) ? number_format($produto->comissao, 2, '.', '') : '0.00' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="observacoes" class="col-md-3 label-control">Observações extras:</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $produto->observacoes ?? '') }}</textarea>
                        </div>
                    </div>
                    

                    <div class="form-group row">
                        <label class="form-label d-block label-control col-md-3">* Produto Ativo?</label>
                        <div class="col-md-3 form-check form-switch">
                            <input type="hidden" name="ativo" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="ativoSwitch"
                                name="ativo"
                                value="1"
                                {{ old('ativo', $produto->ativo ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                                {{ old('ativo', $produto->ativo ?? true) ? 'Ativo' : 'Inativo' }}
                            </label>
                        </div>
                    </div>

                </div>


                {{-- Aba 2: Produto por Dia da Semana --}}
                <div class="tab-pane fade" id="dias" role="tabpanel">
                    <div class="alert alert-secondary">
                        <strong>DICA:</strong> Preencha os dados fiscais conforme a tributação do produto. Verifique NCM, CFOP e CST/CSOSN para evitar erros na emissão de notas fiscais. <br>
                        <em>ATENÇÃO: dados incorretos como NCM ou CFOP podem causar rejeição da NF-e.</em>
                    </div>

                   <div class="form-group row">
                        <label class="col-md-3 label-control" for="ncm">* NCM:</label>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="ncm" name="ncm" required value="{{ $produto->ncm ?? '' }}">
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="cst">* CST:</label>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="cst" name="cst" required value="{{ $produto->cst ?? '' }}">
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
                            <input type="number" class="form-control" id="aliquota" name="aliquota" required value="{{ $produto->aliquota ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="csosn">* CSOSN:</label>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="csosn" name="csosn" required value="{{ $produto->csosn ?? '' }}">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Infos finais --}}
            @if(isset($produto))
                <p class="text-muted mt-3">
                    Criado em: {{ $produto->created_at->format('d/m/Y H:i:s') }}<br>
                    Alterado em: {{ $produto->updated_at->format('d/m/Y H:i:s') }}<br>
                    Alterado por: {{ Auth::user()->name }}
                </p>
            @endif
        </div>

        
        <div class="card-footer">
            <a href="{{ route('produto.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-primary">{{ isset($produto) ? 'Atualizar Produto' : 'Adicionar Produto' }}</button>
        </div>
    </div>
</form>
@endsection


@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.form-switch {
    padding-left: 3em;
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-switch .form-check-input {
    width: 3.5rem;
    height: 1.75rem;
    background-color: #dee2e6;
    border-radius: 1.75rem;
    position: relative;
    transition: background-color 0.3s ease-in-out;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
}

.form-switch .form-check-input:checked {
    background-color: #0d6efd;
}

.form-switch .form-check-input::before {
    content: "";
    position: absolute;
    width: 1.5rem;
    height: 1.5rem;
    top: 0.125rem;
    left: 0.125rem;
    border-radius: 50%;
    background-color: white;
    transition: transform 0.3s ease-in-out;
}

.form-switch .form-check-input:checked::before {
    transform: translateX(1.75rem);
}

.label-control{
    text-align: right
}

.card-footer{
    text-align: right
}
</style>
<style>
    @media (max-width: 768px) {
      .label-control{
        text-align: start
      }
    }
  </style>
@endsection


@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('ativoSwitch');
        const label = document.getElementById('ativoLabel');

        switchInput.addEventListener('change', function () {
            label.textContent = this.checked ? 'Ativo' : 'Inativo';
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione uma opção",
            allowClear: true
        });
    });
</script>

@endsection
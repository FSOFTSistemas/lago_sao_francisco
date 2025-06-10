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
                <ul class="nav nav-tabs" id="produtoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações
                            do Produto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="dias-tab" data-toggle="tab" href="#dias" role="tab">Produto /
                            Fiscal</a>
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
                            <label class="col-md-3 label-control" for="ean"> EAN:</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="ean" name="ean"
                                    value="{{ $produto->ean ?? '' }}" maxlength="13">
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
                                <input type="number" step="0.01" class="form-control" id="precoCusto" name="preco_custo"
                                    required
                                    value="{{ isset($produto) ? number_format($produto->preco_custo, 2, '.', '') : '' }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="precoVenda">* Preço de Venda:</label>
                            <div class="col-md-3">
                                <input type="number" step="0.01" class="form-control" id="precoVenda" name="preco_venda"
                                    required
                                    value="{{ isset($produto) ? number_format($produto->preco_venda, 2, '.', '') : '' }}">
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


                    {{-- Aba 2: Produto por Dia da Semana --}}
                    <div class="tab-pane fade" id="dias" role="tabpanel">
                        <div class="alert alert-secondary">
                            <strong>DICA:</strong> Preencha os dados fiscais conforme a tributação do produto. Verifique
                            NCM, CFOP e CST/CSOSN para evitar erros na emissão de notas fiscais. <br>
                            <em>ATENÇÃO: dados incorretos como NCM ou CFOP podem causar rejeição da NF-e.</em>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="cst">* CST:</label>
                            <div class="col-md-3">
                                <select class="form-control select2" id="cst" name="cst" required>
                                    <option value="">Selecione uma opção</option>
                                    <option value="00"
                                        {{ old('cst', $produto->cst ?? '') == '00' ? 'selected' : '' }}>00 - Tributada
                                        integralmente</option>
                                    <option value="10"
                                        {{ old('cst', $produto->cst ?? '') == '10' ? 'selected' : '' }}>10 - Tributada e
                                        com cobrança do ICMS por ST</option>
                                    <option value="20"
                                        {{ old('cst', $produto->cst ?? '') == '20' ? 'selected' : '' }}>20 - Com redução de
                                        base de cálculo</option>
                                    <option value="30"
                                        {{ old('cst', $produto->cst ?? '') == '30' ? 'selected' : '' }}>30 - Isenta/Não
                                        tributada e com cobrança do ICMS por ST</option>
                                    <option value="40"
                                        {{ old('cst', $produto->cst ?? '') == '40' ? 'selected' : '' }}>40 - Isenta
                                    </option>
                                    <option value="41"
                                        {{ old('cst', $produto->cst ?? '') == '41' ? 'selected' : '' }}>41 - Não Tributada
                                    </option>
                                    <option value="50"
                                        {{ old('cst', $produto->cst ?? '') == '50' ? 'selected' : '' }}>50 - Com Suspensão
                                    </option>
                                    <option value="51"
                                        {{ old('cst', $produto->cst ?? '') == '51' ? 'selected' : '' }}>51 - Com
                                        Diferimento</option>
                                    <option value="60"
                                        {{ old('cst', $produto->cst ?? '') == '60' ? 'selected' : '' }}>60 - ICMS Cobrado
                                        na Operação Anterior por Substituição Tributária</option>
                                    <option value="70"
                                        {{ old('cst', $produto->cst ?? '') == '70' ? 'selected' : '' }}>70 - Com redução de
                                        base de cálculo no ICMS ST</option>
                                    <option value="90"
                                        {{ old('cst', $produto->cst ?? '') == '90' ? 'selected' : '' }}>90 - Outras
                                        Operações</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="ncm">* NCM:</label>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="ncm" name="ncm" required
                                        value="{{ $produto->ncm ?? '' }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal"
                                            data-bs-target="#modalNCM">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="cfopInterno">* CFOP Interno:</label>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="cfopInterno" name="cfop_interno" required
                                    value="{{ $produto->cfop_interno ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="cfopExterno">* CFOP Externo:</label>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="cfopExterno" name="cfop_externo" required
                                    value="{{ $produto->cfop_externo ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="aliquota">* Alíquota:</label>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="aliquota" name="aliquota" required
                                    value="{{ $produto->aliquota ?? '20.5' }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="csosn">* CSOSN:</label>
                            <div class="col-md-3">
                                <select class="form-control select2" id="csosn" name="csosn" required>
                                    <option value="">Selecione uma opção</option>
                                    <option value="101"
                                        {{ old('csosn', $produto->csosn ?? '') == '101' ? 'selected' : '' }}>101 -
                                        Tributação pelo Simples com Permissão de Crédito</option>
                                    <option value="102"
                                        {{ old('csosn', $produto->csosn ?? '') == '102' ? 'selected' : '' }}>102 -
                                        Tributação pelo Simples sem Permissão de Crédito</option>
                                    <option value="103"
                                        {{ old('csosn', $produto->csosn ?? '') == '103' ? 'selected' : '' }}>103 - Isenção
                                        do ICMS no Simples para receita bruta</option>
                                    <option value="201"
                                        {{ old('csosn', $produto->csosn ?? '') == '201' ? 'selected' : '' }}>201 - Simples
                                        Nacional com Permissão de Crédito e ICMS por Substituição Tributária</option>
                                    <option value="202"
                                        {{ old('csosn', $produto->csosn ?? '') == '202' ? 'selected' : '' }}>202 - Simples
                                        Nacional sem Permissão de crédito e com cobrança de ICMS por substituição tributária
                                    </option>
                                    <option value="203"
                                        {{ old('csosn', $produto->csosn ?? '') == '203' ? 'selected' : '' }}>203 - Isenção
                                        do ICMS no Simples para faixa da Receita Bruta e com cobrança de ICMS por
                                        substituição tributária</option>
                                    <option value="300"
                                        {{ old('csosn', $produto->csosn ?? '') == '300' ? 'selected' : '' }}>300 -
                                        Imunidade</option>
                                    <option value="400"
                                        {{ old('csosn', $produto->csosn ?? '') == '400' ? 'selected' : '' }}>400 - Não
                                        tributado pelo Simples</option>
                                    <option value="500"
                                        {{ old('csosn', $produto->csosn ?? '') == '500' ? 'selected' : '' }}>500 - ICMS
                                        cobrado anteriormente por substituição</option>
                                    <option value="900"
                                        {{ old('csosn', $produto->csosn ?? '') == '900' ? 'selected' : '' }}>900 - Outros
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Infos finais --}}
                @if (isset($produto))
                    <p class="text-muted mt-3">
                        Criado em: {{ $produto->created_at->format('d/m/Y H:i:s') }}<br>
                        Alterado em: {{ $produto->updated_at->format('d/m/Y H:i:s') }}<br>
                        Alterado por: {{ Auth::user()->name }}
                    </p>
                @endif
            </div>


            <div class="card-footer">
                <a href="{{ route('produto.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit"
                    class="btn btn-primary">{{ isset($produto) ? 'Atualizar Produto' : 'Adicionar Produto' }}</button>
            </div>
        </div>

        @include('produto.modals._ncm')
    </form>
@endsection


@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .label-control {
            text-align: right
        }

        .card-footer {
            text-align: right
        }
    </style>
    <style>
        @media (max-width: 768px) {
            .label-control {
                text-align: start
            }
        }
    </style>
@endsection


@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switchInput = document.getElementById('ativoSwitch');
            const label = document.getElementById('ativoLabel');

            switchInput.addEventListener('change', function() {
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
    <script>
        function selecionarNCM(codigo) {
            const inputNcm = document.getElementById('ncm');
            if (inputNcm) {
                inputNcm.value = codigo;
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNCM'));
                if (modal) modal.hide();
            } else {
                console.error('Campo NCM não encontrado!');
            }
        }

        $(document).ready(function() {
            $('#filtroCodigo, #filtroDescricao').on('keyup', function() {
                const filtroCod = $('#filtroCodigo').val().toLowerCase();
                const filtroDesc = $('#filtroDescricao').val().toLowerCase();

                $('#tabelaNCM tbody tr').filter(function() {
                    const textoCod = $(this).find('td:eq(0)').text().toLowerCase();
                    const textoDesc = $(this).find('td:eq(1)').text().toLowerCase();
                    $(this).toggle(
                        textoCod.includes(filtroCod) && textoDesc.includes(filtroDesc)
                    );
                });
            });
        });
    </script>
@endsection

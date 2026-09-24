@extends('adminlte::page')

@section('title', 'Conferência da Nota de Entrada')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-clipboard-check text-primary mr-2"></i>Conferência da Nota Fiscal de Entrada
            </h1>
            <p class="text-muted mb-0">
                Verifique os dados da NF-e, faça o vínculo (De/Para) dos produtos com o estoque/almoxarifado e confirme as duplicatas financeiras.
            </p>
        </div>
        <div>
            <a href="{{ route('dfe.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times mr-1"></i> Cancelar
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('entradas.store') }}" method="POST" id="formConferirEntrada">
        @csrf
        <input type="hidden" name="xml" value="{{ $dadosNota['xml'] }}">
        <input type="hidden" name="dfe_documento_id" value="{{ $dfeDocumento->id ?? '' }}">

        {{-- 1. Dados Gerais da Nota e Fornecedor --}}
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold text-sm">
                    <i class="fas fa-file-invoice mr-1"></i> Cabeçalho da Nota & Fornecedor
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="text-xs text-muted mb-0">Número / Série</label>
                        <div class="font-weight-bold text-lg text-primary">
                            NF-e Nº {{ $dadosNota['numero_nota'] }} @if($dadosNota['serie']) (Série {{ $dadosNota['serie'] }}) @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-xs text-muted mb-0">Data de Emissão</label>
                        <div>{{ $dadosNota['data_emissao'] ? \Carbon\Carbon::parse($dadosNota['data_emissao'])->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-xs text-muted mb-0">Data de Entrada no Hotel</label>
                        <input type="datetime-local" name="data_entrada" class="form-control form-control-sm font-weight-bold" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-xs text-muted mb-0">Natureza da Operação</label>
                        <div class="text-truncate" title="{{ $dadosNota['natureza_operacao'] }}">{{ $dadosNota['natureza_operacao'] ?: 'Compra para comercialização / insumos' }}</div>
                    </div>
                </div>

                <hr class="my-2">

                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="text-xs text-muted mb-0">Fornecedor (Emitente)</label>
                        <div class="font-weight-bold">{{ $dadosNota['fornecedor']['razao_social'] }}</div>
                        <small class="text-muted">CNPJ/CPF: {{ $dadosNota['fornecedor']['cnpj'] }} | IE: {{ $dadosNota['fornecedor']['ie'] }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="text-xs text-muted mb-0">Endereço do Fornecedor</label>
                        <div class="text-sm text-truncate" title="{{ $dadosNota['fornecedor']['endereco'] }}">{{ $dadosNota['fornecedor']['endereco'] ?: '-' }}</div>
                    </div>
                    <div class="col-md-3 text-right">
                        <label class="text-xs text-muted mb-0">Valor Total da Nota</label>
                        <div class="font-weight-bold text-xl text-success">
                            R$ {{ number_format($dadosNota['totais']['valor_total'], 2, ',', '.') }}
                        </div>
                        <small class="text-muted">Produtos: R$ {{ number_format($dadosNota['totais']['valor_produtos'], 2, ',', '.') }} | Frete: R$ {{ number_format($dadosNota['totais']['valor_frete'], 2, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Itens da Nota e Vínculo (De/Para) --}}
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-sm">
                    <i class="fas fa-boxes mr-1"></i> Itens da Nota ({{ count($dadosNota['itens']) }} itens para conferência e De/Para)
                </h3>
                <span class="badge badge-light border">Defina o destino: Venda (Produto) ou Consumo (Almoxarifado)</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-bordered mb-0 text-sm">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th style="min-width: 260px;">Item na Nota do Fornecedor</th>
                            <th style="width: 90px;" class="text-center">Qtd Nota</th>
                            <th style="width: 105px;" class="text-right">Unitário Nota</th>
                            <th style="width: 170px;" class="text-center bg-light border-left border-right">
                                <i class="fas fa-boxes text-info mr-1"></i> Conversão / Fator
                            </th>
                            <th style="width: 110px;" class="text-right">Total (R$)</th>
                            <th style="width: 170px;">Destino no Hotel</th>
                            <th style="min-width: 320px;">Vínculo no Sistema (De / Para)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dadosNota['itens'] as $idx => $item)
                            <tr>
                                <td class="text-center font-weight-bold align-middle">{{ $item['numero_item'] }}</td>
                                <td class="align-middle">
                                    <strong class="d-block">{{ $item['descricao'] }}</strong>
                                    <small class="text-muted">
                                        Cód: {{ $item['codigo_fornecedor'] }} 
                                        @if($item['codigo_barras']) | EAN: {{ $item['codigo_barras'] }} @endif
                                        | NCM: {{ $item['ncm'] }} | CFOP: {{ $item['cfop'] }}
                                    </small>
                                </td>
                                <td class="text-center font-weight-bold align-middle">
                                    {{ number_format($item['quantidade'], 2, ',', '.') }} {{ $item['unidade'] }}
                                </td>
                                <td class="text-right align-middle">R$ {{ number_format($item['valor_unitario'], 2, ',', '.') }}</td>

                                {{-- Conversão de Embalagem / Caixa para Unidades individuais --}}
                                @if (!empty($item['is_embalagem']))
                                    <td class="align-middle border-left border-right" style="background-color: #fffdf2;">
                                        <div class="text-center mb-1">
                                            <span class="badge badge-warning text-xs font-weight-bold" title="Unidade coletiva da nota: {{ $item['unidade'] }}">
                                                <i class="fas fa-box-open mr-1"></i> Embalagem ({{ $item['unidade'] }})
                                            </span>
                                        </div>
                                        <div class="input-group input-group-sm">
                                            <input type="number" 
                                                   name="itens[{{ $idx }}][fator_conversao]" 
                                                   class="form-control form-control-sm text-center font-weight-bold input-fator-conversao border-warning" 
                                                   value="{{ $item['fator_conversao'] ?? 1 }}" 
                                                   min="0.0001" 
                                                   step="any"
                                                   data-index="{{ $idx }}"
                                                   data-qtd="{{ $item['quantidade'] }}"
                                                   data-preco="{{ $item['valor_unitario'] }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text text-xs font-weight-bold bg-warning-subtle">un/{{ $item['unidade'] }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-1 small preview-conversao-container" id="preview_conversao_{{ $idx }}">
                                            @php
                                                $fatorAtual = (float) ($item['fator_conversao'] ?? 1);
                                                $qtdConvertidaInicial = (float) $item['quantidade'] * ($fatorAtual > 0 ? $fatorAtual : 1);
                                                $custoConvertidoInicial = $fatorAtual > 0 ? ((float) $item['valor_unitario'] / $fatorAtual) : (float) $item['valor_unitario'];
                                            @endphp
                                            <div class="d-flex justify-content-between align-items-center text-xs">
                                                <span class="text-muted">Entrará:</span>
                                                <strong class="text-success preview-qtd-convertida">
                                                    {{ number_format($qtdConvertidaInicial, 2, ',', '.') }} UN
                                                </strong>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center text-xs">
                                                <span class="text-muted">Custo:</span>
                                                <span class="text-dark font-weight-bold preview-custo-convertido">
                                                    R$ {{ number_format($custoConvertidoInicial, 2, ',', '.') }}/un
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td class="align-middle text-center border-left border-right bg-light">
                                        <span class="badge badge-light border text-secondary font-weight-normal py-1 px-2" title="Item unitário na nota fiscal">
                                            <i class="fas fa-check text-success mr-1"></i> Unitário (1:1)
                                        </span>
                                        <input type="hidden" 
                                               name="itens[{{ $idx }}][fator_conversao]" 
                                               id="fator_hidden_{{ $idx }}"
                                               value="1" 
                                               class="input-fator-conversao" 
                                               data-index="{{ $idx }}" 
                                               data-qtd="{{ $item['quantidade'] }}" 
                                               data-preco="{{ $item['valor_unitario'] }}">
                                        <div class="mt-1">
                                            <a href="javascript:void(0)" class="text-xs text-muted btn-abrir-conversao" data-index="{{ $idx }}" style="font-size: 0.72rem;">
                                                <i class="fas fa-sliders-h mr-1"></i>Converter?
                                            </a>
                                        </div>
                                        <div id="conversao_custom_{{ $idx }}" class="d-none mt-1">
                                            <div class="input-group input-group-sm">
                                                <input type="number" 
                                                       class="form-control form-control-sm text-center font-weight-bold input-fator-manual" 
                                                       value="1" 
                                                       min="0.0001" 
                                                       step="any" 
                                                       data-index="{{ $idx }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text text-xs">fator</span>
                                                </div>
                                            </div>
                                            <small class="text-xs text-success font-weight-bold d-block mt-1 preview-manual-txt"></small>
                                        </div>
                                    </td>
                                @endif

                                <td class="text-right font-weight-bold align-middle text-primary">R$ {{ number_format($item['valor_total'], 2, ',', '.') }}</td>
                                
                                {{-- Seletor de Destino: Produto vs Almoxarifado --}}
                                <td class="align-middle">
                                    <select name="itens[{{ $idx }}][destino]" class="form-control form-control-sm seletor-destino" data-index="{{ $idx }}" required>
                                        <option value="produto" selected>🛒 Venda (Produto)</option>
                                        <option value="almoxarifado">📦 Consumo (Almoxarifado)</option>
                                    </select>
                                </td>

                                {{-- Campos de Mapeamento (De/Para) --}}
                                <td class="align-middle">
                                    {{-- Bloco 1: Produto de Venda --}}
                                    <div class="bloco-produto" id="bloco_produto_{{ $idx }}">
                                        <div class="mb-1">
                                            <select name="itens[{{ $idx }}][produto_id]" class="form-control form-control-sm select2-produto">
                                                <option value="">+ Criar novo produto no catálogo</option>
                                                @foreach ($produtos as $p)
                                                    <option value="{{ $p->id }}" {{ (!empty($item['codigo_barras']) && $p->ean === $item['codigo_barras']) || stripos($p->descricao, $item['descricao']) !== false ? 'selected' : '' }}>
                                                        {{ $p->descricao }} (Cód: {{ $p->id }} | Atual: {{ $p->preco_venda_formatado }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row gx-1">
                                            <div class="col-6">
                                                <label class="text-xs text-muted mb-0">Categoria Produto</label>
                                                <select name="itens[{{ $idx }}][categoria_produto_id]" class="form-control form-control-sm">
                                                    @foreach ($categoriasProduto as $cp)
                                                        <option value="{{ $cp->id }}">{{ $cp->descricao }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-xs text-muted mb-0">Margem Lucro (%)</label>
                                                <input type="number" step="0.01" name="itens[{{ $idx }}][margem_lucro]" class="form-control form-control-sm" value="35">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bloco 2: Almoxarifado (Insumos) --}}
                                    <div class="bloco-almoxarifado d-none" id="bloco_almoxarifado_{{ $idx }}">
                                        <div class="mb-1">
                                            <select name="itens[{{ $idx }}][almoxarifado_item_id]" class="form-control form-control-sm">
                                                <option value="">+ Criar novo item no Almoxarifado</option>
                                                @foreach ($itensAlmoxarifado as $ai)
                                                    <option value="{{ $ai->id }}" {{ stripos($ai->nome, $item['descricao']) !== false ? 'selected' : '' }}>
                                                        {{ $ai->nome }} (Estoque atual: {{ $ai->estoque_formatado }} {{ $ai->unidade_medida }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row gx-1">
                                            <div class="col-12">
                                                <label class="text-xs text-muted mb-0">Categoria no Almoxarifado</label>
                                                <select name="itens[{{ $idx }}][almoxarifado_categoria_id]" class="form-control form-control-sm">
                                                    @foreach ($categoriasAlmoxarifado as $ca)
                                                        <option value="{{ $ca->id }}">{{ $ca->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. Financeiro & Faturas / Duplicatas --}}
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-sm">
                    <i class="fas fa-money-check-alt mr-1"></i> Faturamento & Contas a Pagar
                </h3>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="checkGerarFinanceiro" name="gerar_contas_a_pagar" value="1" {{ !empty($dadosNota['duplicatas']) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="checkGerarFinanceiro">Lançar automaticamente no Contas a Pagar</label>
                </div>
            </div>
            <div class="card-body" id="secaoFinanceiro">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-xs font-weight-bold">Plano de Contas Financeiro</label>
                        <select name="plano_de_contas_id" class="form-control form-control-sm">
                            <option value="">Selecione o Plano de Contas...</option>
                            @foreach ($planosDeContas as $plano)
                                <option value="{{ $plano->id }}">{{ $plano->descricao }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-xs text-muted">Observações da Entrada</label>
                        <input type="text" name="observacoes" class="form-control form-control-sm" placeholder="Observações opcionais sobre esta compra">
                    </div>
                </div>

                @if (!empty($dadosNota['duplicatas']))
                    <h6 class="font-weight-bold text-xs text-muted text-uppercase mb-2">Parcelas da NF-e identificadas no XML:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 bg-light" style="max-width: 600px;">
                            <thead>
                                <tr>
                                    <th style="width: 100px;">Parcela</th>
                                    <th>Data de Vencimento</th>
                                    <th class="text-right">Valor da Parcela</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dadosNota['duplicatas'] as $dup)
                                    <tr>
                                        <td><strong>{{ $dup['numero'] ?: 'Única' }}</strong></td>
                                        <td>{{ $dup['data_vencimento'] ? \Carbon\Carbon::parse($dup['data_vencimento'])->format('d/m/Y') : '-' }}</td>
                                        <td class="text-right font-weight-bold text-dark">R$ {{ number_format($dup['valor'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border py-2 text-sm mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Esta nota fiscal não trouxe parcelas de cobrança (<cobr>) no XML (compra à vista ou sem faturamento a prazo).
                    </div>
                @endif
            </div>
        </div>

        {{-- Barra de Ação Final --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="{{ route('dfe.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-success btn-lg font-weight-bold shadow">
                <i class="fas fa-check-circle mr-1"></i> Confirmar e Dar Entrada no Estoque
            </button>
        </div>
    </form>
@stop

@section('js')
    <script>
        function formatarNumeroBr(val, decimais = 2) {
            return Number(val).toLocaleString('pt-BR', {
                minimumFractionDigits: decimais,
                maximumFractionDigits: decimais
            });
        }

        // Alternância de Blocos (Produto vs Almoxarifado)
        document.querySelectorAll('.seletor-destino').forEach(function(select) {
            select.addEventListener('change', function() {
                var idx = this.getAttribute('data-index');
                var blocoProduto = document.getElementById('bloco_produto_' + idx);
                var blocoAlmoxarifado = document.getElementById('bloco_almoxarifado_' + idx);

                if (this.value === 'almoxarifado') {
                    blocoProduto.classList.add('d-none');
                    blocoAlmoxarifado.classList.remove('d-none');
                } else {
                    blocoProduto.classList.remove('d-none');
                    blocoAlmoxarifado.classList.add('d-none');
                }
            });
        });

        // Atualização em tempo real do Fator de Conversão (Embalagem -> Unidades)
        document.querySelectorAll('.input-fator-conversao').forEach(function(input) {
            function atualizarCalculo() {
                var idx = input.getAttribute('data-index');
                var qtdOriginal = parseFloat(input.getAttribute('data-qtd')) || 0;
                var precoOriginal = parseFloat(input.getAttribute('data-preco')) || 0;
                var fator = parseFloat(input.value);

                if (isNaN(fator) || fator <= 0) {
                    fator = 1;
                }

                var qtdFinal = qtdOriginal * fator;
                var precoFinal = precoOriginal / fator;

                var container = document.getElementById('preview_conversao_' + idx);
                if (container) {
                    var elQtd = container.querySelector('.preview-qtd-convertida');
                    var elCusto = container.querySelector('.preview-custo-convertido');

                    if (elQtd) {
                        elQtd.textContent = formatarNumeroBr(qtdFinal, 2) + ' UN';
                    }
                    if (elCusto) {
                        elCusto.textContent = 'R$ ' + formatarNumeroBr(precoFinal, 2) + '/un';
                    }
                }
            }

            input.addEventListener('input', atualizarCalculo);
            input.addEventListener('change', atualizarCalculo);
        });

        // Alternar painel manual de conversão para item unitário
        document.querySelectorAll('.btn-abrir-conversao').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = this.getAttribute('data-index');
                var painel = document.getElementById('conversao_custom_' + idx);
                if (painel) {
                    painel.classList.toggle('d-none');
                }
            });
        });

        // Quando o usuário define um fator manual para um item unitário
        document.querySelectorAll('.input-fator-manual').forEach(function(manualInput) {
            function atualizarManual() {
                var idx = manualInput.getAttribute('data-index');
                var hiddenInput = document.getElementById('fator_hidden_' + idx);
                var fator = parseFloat(manualInput.value) || 1;
                if (fator <= 0) fator = 1;

                if (hiddenInput) {
                    hiddenInput.value = fator;
                    var qtdOriginal = parseFloat(hiddenInput.getAttribute('data-qtd')) || 0;
                    var precoOriginal = parseFloat(hiddenInput.getAttribute('data-preco')) || 0;
                    var total = qtdOriginal * fator;
                    var custo = precoOriginal / fator;

                    var container = manualInput.closest('td');
                    var txt = container.querySelector('.preview-manual-txt');
                    if (txt) {
                        txt.textContent = '= ' + formatarNumeroBr(total, 2) + ' UN (R$ ' + formatarNumeroBr(custo, 2) + '/un)';
                    }
                }
            }

            manualInput.addEventListener('input', atualizarManual);
            manualInput.addEventListener('change', atualizarManual);
        });
    </script>
@stop

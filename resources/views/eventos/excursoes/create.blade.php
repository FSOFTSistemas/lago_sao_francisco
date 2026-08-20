@extends('adminlte::page')

@php
    $somenteLeitura = $visualizacao ?? false;
    $edicao = isset($excursao);
    $valorCampo = fn (string $campo, mixed $padrao = 0) => old($campo, $excursao->{$campo} ?? $padrao);
    $formatarMoeda = fn (mixed $valor) => number_format((float) ($valor ?: 0), 2, ',', '.');
    $recebimentosIniciais = old('recebimentos', [['valor' => '', 'forma_pagamento_id' => '']]);
@endphp

@section('title', $somenteLeitura ? 'Visualizar excursão' : ($edicao ? 'Editar excursão' : 'Cadastrar excursão'))

@section('content_header')
    <h5>{{ $somenteLeitura ? 'Visualizar excursão' : ($edicao ? 'Editar excursão' : 'Cadastrar excursão') }}</h5>
    <hr>
@endsection

@section('content')
    <form id="form-excursao" enctype="multipart/form-data" action="{{ $edicao ? route('eventos.excursoes.update', $excursao) : route('eventos.excursoes.store') }}" method="POST" novalidate>
        @csrf
        @if ($edicao) @method('PUT') @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revise os dados informados:</strong>
                <ul class="mb-0 mt-2 pl-4">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body mt-3">
                        <ul class="nav nav-tabs" id="excursao-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="informacoes-tab" data-toggle="pill"
                                    href="#tab-informacoes" role="tab">
                                    <i class="fas fa-info-circle mr-1"></i> Informações
                                </a>
                            </li>
                            <li class="nav-item" id="almoco-tab-item"
                                @unless(old('possui_almoco', ($excursao->qtd_almoco ?? 0) > 0)) style="display: none" @endunless>
                                <a class="nav-link" id="almoco-tab" data-toggle="pill" href="#tab-almoco" role="tab">
                                    <i class="fas fa-utensils mr-1"></i> Almoço
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pagamento-tab" data-toggle="pill" href="#tab-pagamento" role="tab">
                                    <i class="fas fa-hand-holding-usd mr-1"></i> Pagamento
                                </a>
                            </li>
                        </ul>

                <div class="tab-content mt-3" id="excursao-tabs-content">
                    <div class="tab-pane fade show active" id="tab-informacoes" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <button type="button" class="btn btn-primary w-25" id="proximo-informacoes">
                                Próximo
                            </button>
                        </div>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Dados gerais</h3></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="data">Data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('data') is-invalid @enderror" id="data" name="data"
                                    value="{{ old('data', $edicao ? $excursao->data->format('Y-m-d') : '') }}"
                                    @unless($edicao) min="{{ now()->format('Y-m-d') }}" @endunless required @disabled($somenteLeitura)>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="responsavel">Responsável <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('responsavel') is-invalid @enderror" id="responsavel" name="responsavel"
                                    value="{{ old('responsavel', $excursao->responsavel ?? '') }}" maxlength="255" required @disabled($somenteLeitura)>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="telefone_responsavel">Telefone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('telefone_responsavel') is-invalid @enderror" id="telefone_responsavel"
                                    name="telefone_responsavel" value="{{ old('telefone_responsavel', $excursao->telefone_responsavel ?? '') }}"
                                    maxlength="15" placeholder="(00) 00000-0000" required @disabled($somenteLeitura)>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="descricao">Descrição <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="3"
                                maxlength="200" required @disabled($somenteLeitura)>{{ old('descricao', $excursao->descricao ?? '') }}</textarea>
                            <small class="form-text text-muted"><span id="descricao-contador">0</span>/200 caracteres</small>
                        </div>
                        <div class="form-group mt-3 mb-0">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="possui_almoco" value="0">
                                <input type="checkbox" class="custom-control-input" id="possui_almoco"
                                    name="possui_almoco" value="1"
                                    @checked(old('possui_almoco', ($excursao->qtd_almoco ?? 0) > 0))
                                    @disabled($somenteLeitura)>
                                <label class="custom-control-label" for="possui_almoco">
                                    Almoço?
                                    <strong id="possui-almoco-label">
                                        {{ old('possui_almoco', ($excursao->qtd_almoco ?? 0) > 0) ? 'Sim' : 'Não' }}
                                    </strong>
                                </label>
                            </div>
                        </div>
                        @if ($edicao)
                            <div class="form-group mt-3 mb-0"><label>Status</label>
                                <input class="form-control" value="{{ ucfirst(strtolower(str_replace('_', ' ', $excursao->status))) }}" disabled>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-2"></i>Pessoas e valores</h3></div>
                    <div class="card-body"><div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="qtd_pessoas">Quantidade de pessoas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('qtd_pessoas') is-invalid @enderror" id="qtd_pessoas" name="qtd_pessoas" min="1" step="1"
                                value="{{ old('qtd_pessoas', $excursao->qtd_pessoas ?? 1) }}" required @disabled($somenteLeitura)>
                            @error('qtd_pessoas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="valor_pessoa_display">Valor por pessoa <span class="text-danger">*</span></label>
                            <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                <input type="text" class="form-control money-display @error('valor_pessoa') is-invalid @enderror" id="valor_pessoa_display" data-money-target="valor_pessoa"
                                    value="{{ $formatarMoeda($valorCampo('valor_pessoa')) }}" inputmode="numeric" required @disabled($somenteLeitura)>
                                <input type="hidden" id="valor_pessoa" name="valor_pessoa" value="{{ $valorCampo('valor_pessoa') }}">
                            </div>
                            @error('valor_pessoa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="percentual_comissao">Comissão <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('percentual_comissao') is-invalid @enderror" id="percentual_comissao" name="percentual_comissao" min="0" max="100" step="0.01"
                                    value="{{ $valorCampo('percentual_comissao', 10) }}" required @disabled($somenteLeitura)>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                            @error('percentual_comissao') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div></div>
                </div>

                    </div>

                    <div class="tab-pane fade" id="tab-almoco" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <button type="button" class="btn btn-primary w-25" id="proximo-almoco">Próximo</button>
                        </div>
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-utensils mr-2"></i>Almoço</h3>
                            </div>
                            <div class="card-body text-center py-5">
                                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                                <h5>Configuração do almoço</h5>
                                <p class="text-muted mb-0">Os dados do almoço serão adicionados na próxima etapa da implementação.</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <button type="button" class="btn btn-outline-secondary" id="anterior-almoco">
                                <i class="fas fa-arrow-left mr-1"></i> Anterior
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-pagamento" role="tabpanel">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Ajustes</h3></div>
                        <div class="card-body"><div class="form-row">
                            @foreach (['acrescimo' => 'Acréscimo', 'desconto' => 'Desconto'] as $campo => $rotulo)
                                <div class="form-group col-md-6 mb-0">
                                    <label for="{{ $campo }}_display">{{ $rotulo }}</label>
                                    <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="text" class="form-control money-display @error($campo) is-invalid @enderror" id="{{ $campo }}_display" data-money-target="{{ $campo }}"
                                            value="{{ $formatarMoeda($valorCampo($campo)) }}" inputmode="numeric" required @disabled($somenteLeitura)>
                                        <input type="hidden" id="{{ $campo }}" name="{{ $campo }}" value="{{ $valorCampo($campo) }}">
                                    </div>
                                    @error($campo) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            @endforeach
                        </div></div>
                    </div>

                    @unless ($edicao)
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-2"></i>Pagamentos iniciais</h3>
                            <button type="button" id="adicionar-recebimento" class="btn btn-sm btn-success ml-auto"><i class="fas fa-plus mr-1"></i>Adicionar forma</button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Informe um ou mais pagamentos. O total recebido deve ser de pelo menos 50% do valor da excursão.</p>
                            <div id="recebimentos-container">
                                @foreach ($recebimentosIniciais as $indice => $recebimento)
                                    <div class="recebimento-row border rounded p-3 mb-3" data-index="{{ $indice }}">
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-4">
                                                <label>Forma de pagamento <span class="text-danger">*</span></label>
                                                <select class="form-control forma-pagamento @error("recebimentos.$indice.forma_pagamento_id") is-invalid @enderror" name="recebimentos[{{ $indice }}][forma_pagamento_id]" required>
                                                    <option value="">Selecione</option>
                                                    @foreach ($formasPagamento as $forma)
                                                        <option value="{{ $forma->id }}" data-exige-comprovante="{{ $forma->exige_comprovante ? '1' : '0' }}"
                                                            @selected((string) ($recebimento['forma_pagamento_id'] ?? '') === (string) $forma->id)>{{ $forma->descricao }}</option>
                                                    @endforeach
                                                </select>
                                                @error("recebimentos.$indice.forma_pagamento_id") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Valor <span class="text-danger">*</span></label>
                                                <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                                    <input type="text" class="form-control money-display pagamento-display @error("recebimentos.$indice.valor") is-invalid @enderror" data-money-target="recebimento_valor_{{ $indice }}"
                                                        value="{{ $formatarMoeda($recebimento['valor'] ?? 0) }}" inputmode="numeric" required>
                                                    <input type="hidden" class="pagamento-valor" id="recebimento_valor_{{ $indice }}"
                                                        name="recebimentos[{{ $indice }}][valor]" value="{{ $recebimento['valor'] ?? 0 }}">
                                                </div>
                                                @error("recebimentos.$indice.valor") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group col-md-4 comprovante-group">
                                                <label>Comprovante <span class="text-danger comprovante-obrigatorio d-none">*</span></label>
                                                <input type="file" class="form-control-file comprovante" name="recebimentos[{{ $indice }}][comprovante]" accept="image/*,.pdf">
                                                @error("recebimentos.$indice.comprovante") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group col-md-1"><button type="button" class="btn btn-outline-danger remover-recebimento" title="Remover"><i class="fas fa-trash"></i></button></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="alert alert-info mb-0">
                                <div class="row"><div class="col-sm-4">Recebido: <strong id="total-recebido">R$ 0,00</strong></div>
                                    <div class="col-sm-4">Mínimo (50%): <strong id="minimo-recebido">R$ 0,00</strong></div>
                                    <div class="col-sm-4">Restante: <strong id="valor-restante">R$ 0,00</strong></div></div>
                                <small id="situacao-pagamento" class="d-block mt-2"></small>
                            </div>
                        </div>
                    </div>
                    @endunless
                        <div class="d-flex justify-content-start mb-3">
                            <button type="button" class="btn btn-outline-secondary" id="anterior-pagamento">
                                <i class="fas fa-arrow-left mr-1"></i> Anterior
                            </button>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-outline card-success shadow-sm sticky-top" style="top: 1rem">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Resumo financeiro</h3></div>
                    <div class="card-body">
                        @foreach (['valor-pessoas' => 'Valor das pessoas', 'resumo-almoco' => 'Total do almoço', 'subtotal' => 'Subtotal', 'resumo-acrescimo' => 'Acréscimo', 'resumo-desconto' => 'Desconto'] as $id => $rotulo)
                            <div class="d-flex justify-content-between mb-2"><span>{{ $rotulo }}</span><strong id="{{ $id }}">R$ 0,00</strong></div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between h5"><span>Total</span><strong id="total">R$ 0,00</strong></div>
                        <div class="d-flex justify-content-between text-danger"><span>Comissão</span><strong id="valor-comissao">R$ 0,00</strong></div>
                        <div class="d-flex justify-content-between text-success mt-2"><span>Receita líquida</span><strong id="receita-liquida">R$ 0,00</strong></div>
                        @if ($edicao)
                            <hr><div class="d-flex justify-content-between"><span>Valor pago</span><strong>{{ $formatarMoeda($excursao->valor_pago) }}</strong></div>
                            <div class="d-flex justify-content-between"><span>Valor restante</span><strong>{{ $formatarMoeda($excursao->valor_restante) }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card"><div class="card-footer d-flex justify-content-between">
            <a href="{{ route('eventos.excursoes.index') }}" class="btn btn-light border">Cancelar</a>
            @unless ($somenteLeitura)
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i>{{ $edicao ? 'Salvar alterações' : 'Cadastrar excursão' }}</button>
            @endunless
        </div></div>
    </form>

    @unless ($edicao)
        <template id="recebimento-template">
            <div class="recebimento-row border rounded p-3 mb-3" data-index="__INDEX__"><div class="form-row align-items-end">
                <div class="form-group col-md-4"><label>Forma de pagamento <span class="text-danger">*</span></label>
                    <select class="form-control forma-pagamento" name="recebimentos[__INDEX__][forma_pagamento_id]" required><option value="">Selecione</option>
                        @foreach ($formasPagamento as $forma)<option value="{{ $forma->id }}" data-exige-comprovante="{{ $forma->exige_comprovante ? '1' : '0' }}">{{ $forma->descricao }}</option>@endforeach
                    </select></div>
                <div class="form-group col-md-3"><label>Valor <span class="text-danger">*</span></label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                    <input type="text" class="form-control money-display pagamento-display" data-money-target="recebimento_valor___INDEX__" value="0,00" inputmode="numeric" required>
                    <input type="hidden" class="pagamento-valor" id="recebimento_valor___INDEX__" name="recebimentos[__INDEX__][valor]" value="0.00"></div></div>
                <div class="form-group col-md-4 comprovante-group"><label>Comprovante <span class="text-danger comprovante-obrigatorio d-none">*</span></label>
                    <input type="file" class="form-control-file comprovante" name="recebimentos[__INDEX__][comprovante]" accept="image/*,.pdf"></div>
                <div class="form-group col-md-1"><button type="button" class="btn btn-outline-danger remover-recebimento"><i class="fas fa-trash"></i></button></div>
            </div></div>
        </template>
    @endunless
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-excursao');
    const moeda = valor => Number(valor || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
    const numero = id => Number(document.getElementById(id)?.value || 0);
    const definir = (id, valor) => { const el = document.getElementById(id); if (el) el.textContent = moeda(valor); };

    function recalcular() {
        const valorPessoas = numero('valor_pessoa') * numero('qtd_pessoas');
        const totalAlmoco = numero('valor_almoco') * numero('qtd_almoco');
        const subtotal = valorPessoas + totalAlmoco;
        const total = subtotal + numero('acrescimo') - numero('desconto');
        const comissao = valorPessoas * (numero('percentual_comissao') / 100);
        const recebido = [...document.querySelectorAll('.pagamento-valor')].reduce((soma, el) => soma + Number(el.value || 0), 0);
        const minimo = Math.max(total, 0) / 2;

        definir('valor-pessoas', valorPessoas); definir('resumo-almoco', totalAlmoco); definir('subtotal', subtotal);
        definir('resumo-acrescimo', numero('acrescimo')); definir('resumo-desconto', numero('desconto'));
        definir('total', total); definir('valor-comissao', comissao); definir('receita-liquida', total - comissao);
        definir('total-recebido', recebido); definir('minimo-recebido', minimo); definir('valor-restante', Math.max(total - recebido, 0));
        const totalAlmocoCampo = document.getElementById('total_almoco_display');
        if (totalAlmocoCampo) totalAlmocoCampo.value = moeda(totalAlmoco);
        const situacao = document.getElementById('situacao-pagamento');
        if (situacao) {
            const valido = total > 0 && recebido + 0.01 >= minimo && recebido <= total + 0.01;
            situacao.className = `d-block mt-2 ${valido ? 'text-success' : 'text-danger'}`;
            situacao.textContent = recebido > total + 0.01 ? 'O valor recebido não pode superar o total.' :
                (recebido + 0.01 < minimo ? `Faltam ${moeda(minimo - recebido)} para atingir a entrada mínima.` : 'Entrada mínima atingida.');
        }
        return {total, recebido, minimo};
    }

    function configurarMoeda(campo) {
        if (campo.dataset.maskReady || campo.disabled) return;
        campo.dataset.maskReady = '1';
        const oculto = document.getElementById(campo.dataset.moneyTarget);
        let digitos = oculto?.value ? String(Math.round(Number(oculto.value) * 100)) : '';
        const renderizar = () => {
            const centavos = Number(digitos || 0);
            campo.value = (centavos / 100).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            if (oculto) oculto.value = (centavos / 100).toFixed(2);
            recalcular();
        };
        campo.addEventListener('keydown', event => {
            if (/^\d$/.test(event.key)) { event.preventDefault(); digitos = (digitos + event.key).replace(/^0+/, '').slice(0, 12); renderizar(); }
            else if (event.key === 'Backspace') { event.preventDefault(); digitos = digitos.slice(0, -1); renderizar(); }
            else if (event.key === 'Delete') { event.preventDefault(); digitos = ''; renderizar(); }
        });
        campo.addEventListener('paste', event => { event.preventDefault(); digitos = (event.clipboardData.getData('text').match(/\d/g) || []).join('').replace(/^0+/, '').slice(0, 12); renderizar(); });
        renderizar();
    }

    function atualizarComprovante(linha) {
        const select = linha.querySelector('.forma-pagamento');
        const arquivo = linha.querySelector('.comprovante');
        const exige = select?.selectedOptions[0]?.dataset.exigeComprovante === '1';
        if (arquivo) arquivo.required = exige;
        linha.querySelector('.comprovante-obrigatorio')?.classList.toggle('d-none', !exige);
    }

    function configurarLinha(linha) {
        linha.querySelectorAll('.money-display').forEach(configurarMoeda);
        linha.querySelector('.forma-pagamento')?.addEventListener('change', () => atualizarComprovante(linha));
        linha.querySelector('.remover-recebimento')?.addEventListener('click', () => { linha.remove(); recalcular(); });
        atualizarComprovante(linha);
    }

    document.querySelectorAll('.money-display').forEach(configurarMoeda);
    document.querySelectorAll('.recebimento-row').forEach(configurarLinha);
    ['qtd_pessoas', 'qtd_almoco'].forEach(id => document.getElementById(id)?.addEventListener('input', recalcular));
    const percentual = document.getElementById('percentual_comissao');
    percentual?.addEventListener('input', () => {
        if (Number(percentual.value) > 100) percentual.value = 100;
        if (Number(percentual.value) < 0) percentual.value = 0;
        const partes = percentual.value.split('.');
        if (partes[1]?.length > 2) percentual.value = `${partes[0]}.${partes[1].slice(0, 2)}`;
        recalcular();
    });

    const possuiAlmoco = document.getElementById('possui_almoco');
    const possuiAlmocoLabel = document.getElementById('possui-almoco-label');
    const almocoTabItem = document.getElementById('almoco-tab-item');

    function abrirAba(id) {
        document.getElementById(id)?.click();
    }

    function atualizarAbaAlmoco() {
        const incluirAlmoco = possuiAlmoco?.checked ?? false;
        if (possuiAlmocoLabel) possuiAlmocoLabel.textContent = possuiAlmoco.checked ? 'Sim' : 'Não';
        if (almocoTabItem) almocoTabItem.style.display = incluirAlmoco ? '' : 'none';

        if (!incluirAlmoco && document.getElementById('tab-almoco')?.classList.contains('active')) {
            abrirAba('informacoes-tab');
        }
    }

    possuiAlmoco?.addEventListener('change', atualizarAbaAlmoco);
    atualizarAbaAlmoco();

    function nomeCampo(campo) {
        const label = campo.closest('.form-group')?.querySelector('label');
        return label?.textContent.replace('*', '').trim() || 'Campo obrigatório';
    }

    function mensagemCampo(campo) {
        const nome = nomeCampo(campo);
        if (campo.dataset.moneyTarget === 'valor_pessoa' && numero('valor_pessoa') <= 0) {
            return 'O valor por pessoa deve ser maior que zero.';
        }
        if (campo.validity.valueMissing) return `Preencha: ${nome}.`;
        if (campo.validity.typeMismatch) return `Informe um valor válido em: ${nome}.`;
        if (campo.validity.rangeUnderflow) return `${nome} deve ser no mínimo ${campo.min}.`;
        if (campo.validity.rangeOverflow) return `${nome} deve ser no máximo ${campo.max}.`;
        if (campo.validity.tooLong) return `${nome} excedeu o limite permitido.`;
        return `Revise o campo: ${nome}.`;
    }

    function validarPainel(seletor, titulo = 'Revise esta etapa') {
        const campos = [...document.querySelectorAll(`${seletor} input, ${seletor} select, ${seletor} textarea`)]
            .filter(campo => !campo.disabled && campo.type !== 'hidden');
        const invalidos = campos.filter(campo => !campo.checkValidity()
            || (campo.dataset.moneyTarget === 'valor_pessoa' && numero('valor_pessoa') <= 0));

        campos.forEach(campo => campo.classList.toggle('is-invalid', invalidos.includes(campo)));
        if (!invalidos.length) return true;

        const mensagens = [...new Set(invalidos.map(mensagemCampo))];
        Swal.fire({
            icon: 'warning',
            title: titulo,
            html: `<ul class="text-left mb-0">${mensagens.map(mensagem => `<li>${mensagem}</li>`).join('')}</ul>`,
        }).then(() => invalidos[0].focus());
        return false;
    }

    document.getElementById('proximo-informacoes')?.addEventListener('click', () => {
        if (!validarPainel('#tab-informacoes', 'Preencha as informações obrigatórias')) return;
        if (possuiAlmoco?.checked) {
            abrirAba('almoco-tab');
        } else if (document.getElementById('pagamento-tab')) {
            abrirAba('pagamento-tab');
        }
    });

    document.getElementById('anterior-almoco')?.addEventListener('click', () => {
        abrirAba('informacoes-tab');
    });

    document.getElementById('proximo-almoco')?.addEventListener('click', () => {
        abrirAba('pagamento-tab');
    });

    document.getElementById('anterior-pagamento')?.addEventListener('click', () => {
        abrirAba(possuiAlmoco?.checked ? 'almoco-tab' : 'informacoes-tab');
    });

    form?.addEventListener('invalid', event => {
        const painel = event.target.closest('.tab-pane');
        if (painel?.id === 'tab-informacoes') abrirAba('informacoes-tab');
        if (painel?.id === 'tab-pagamento') abrirAba('pagamento-tab');
    }, true);

    document.querySelectorAll('#excursao-tabs a[data-toggle="pill"]').forEach(aba => {
        aba.addEventListener('click', event => {
            const informacoesAtivas = document.getElementById('tab-informacoes')?.classList.contains('active');
            if (informacoesAtivas && aba.getAttribute('href') !== '#tab-informacoes'
                && !validarPainel('#tab-informacoes', 'Preencha as informações obrigatórias')) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });
    });

    form?.querySelectorAll('input, select, textarea').forEach(campo => {
        ['input', 'change'].forEach(evento => campo.addEventListener(evento, () => {
            if (campo.checkValidity()) campo.classList.remove('is-invalid');
        }));
    });

    const telefone = document.getElementById('telefone_responsavel');
    const formatarTelefone = valor => {
        const d = valor.replace(/\D/g, '').slice(0, 11);
        if (!d.length) return '';
        if (d.length <= 2) return `(${d}`;
        if (d.length <= 6) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
        if (d.length <= 10) return `(${d.slice(0, 2)}) ${d.slice(2, 6)}-${d.slice(6)}`;
        return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
    };
    if (telefone && !telefone.disabled) { telefone.value = formatarTelefone(telefone.value); telefone.addEventListener('input', () => telefone.value = formatarTelefone(telefone.value)); }

    const descricao = document.getElementById('descricao');
    const contador = document.getElementById('descricao-contador');
    const contar = () => contador.textContent = descricao.value.length;
    descricao?.addEventListener('input', contar); if (descricao && contador) contar();

    let proximoIndice = document.querySelectorAll('.recebimento-row').length;
    document.getElementById('adicionar-recebimento')?.addEventListener('click', () => {
        const html = document.getElementById('recebimento-template').innerHTML.replaceAll('__INDEX__', proximoIndice++);
        document.getElementById('recebimentos-container').insertAdjacentHTML('beforeend', html);
        configurarLinha(document.getElementById('recebimentos-container').lastElementChild);
    });

    form?.addEventListener('submit', event => {
        if (!validarPainel('#tab-informacoes', 'Não foi possível cadastrar a excursão')) {
            event.preventDefault();
            abrirAba('informacoes-tab');
            return;
        }

        if (!validarPainel('#tab-pagamento', 'Revise os dados de pagamento')) {
            event.preventDefault();
            abrirAba('pagamento-tab');
            return;
        }

        if (!document.getElementById('recebimentos-container')) return;
        const {total, recebido, minimo} = recalcular();
        let mensagem = '';
        if (!document.querySelector('.recebimento-row')) mensagem = 'Adicione pelo menos uma forma de pagamento.';
        else if (total <= 0) mensagem = 'O total da excursão deve ser maior que zero.';
        else if (recebido + 0.01 < minimo) mensagem = 'O pagamento inicial deve ser de pelo menos 50% do total.';
        else if (recebido > total + 0.01) mensagem = 'O valor recebido não pode ser maior que o total da excursão.';
        if (mensagem) { event.preventDefault(); Swal.fire({icon: 'warning', title: 'Revise os pagamentos', text: mensagem}); }
    });
    recalcular();
});
</script>
@stop

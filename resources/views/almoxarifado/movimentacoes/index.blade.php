@extends('adminlte::page')

@section('title', 'Movimentações do Almoxarifado')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-1 text-dark"><i class="fas fa-exchange-alt text-success mr-2"></i>Almoxarifado - Movimentações de Estoque</h1>
            <p class="text-muted mb-0">Registro e rastreabilidade de entradas, saídas para setores e ajustes físicos de estoque.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('almoxarifado.itens.index') }}" class="btn btn-outline-success mr-1">
                <i class="fas fa-boxes mr-1"></i> Itens do Estoque
            </a>
            <a href="{{ route('almoxarifado.categorias.index') }}" class="btn btn-outline-secondary mr-1">
                <i class="fas fa-tags mr-1"></i> Categorias
            </a>
            <a href="{{ route('preferencias') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </div>
    <hr class="mt-2">
@stop

@section('content')
    {{-- Mensagens de Feedback --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <strong>Atenção:</strong> Verifique os erros no formulário.
            <ul class="mb-0 mt-1 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Cards Informativos de Resumo --}}
    <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-2">
                <span class="info-box-icon bg-success"><i class="fas fa-arrow-circle-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Total Entradas</span>
                    <span class="info-box-number text-dark h4 mb-0">{{ $totalEntradas }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-2">
                <span class="info-box-icon bg-danger"><i class="fas fa-arrow-circle-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Total Saídas</span>
                    <span class="info-box-number text-dark h4 mb-0">{{ $totalSaidas }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-2">
                <span class="info-box-icon bg-info"><i class="fas fa-sliders-h"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Total Ajustes</span>
                    <span class="info-box-number text-dark h4 mb-0">{{ $totalAjustes }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box shadow-sm mb-2">
                <span class="info-box-icon bg-secondary"><i class="fas fa-history"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted">Total Movimentações</span>
                    <span class="info-box-number text-dark h4 mb-0">{{ $movimentacoes->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Barra de Ações Rápidas e Filtros fora da tabela --}}
    <div class="card card-outline card-success shadow-sm mb-3">
        <div class="card-body p-3">
            {{-- Linha de Botões de Ação --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap border-bottom pb-3 mb-3">
                <div>
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fas fa-filter text-success mr-1"></i> Filtros de Pesquisa
                    </h5>
                    <small class="text-secondary">Refine as movimentações por texto, item, tipo ou período.</small>
                </div>
                <div class="mt-2 mt-md-0">
                    <button type="button" class="btn btn-success mr-1" data-toggle="modal" data-target="#modalNovaEntrada">
                        <i class="fas fa-plus-circle mr-1"></i> Nova Entrada
                    </button>
                    <button type="button" class="btn btn-danger mr-1" data-toggle="modal" data-target="#modalNovaSaida">
                        <i class="fas fa-minus-circle mr-1"></i> Nova Saída
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalNovoAjuste">
                        <i class="fas fa-sliders-h mr-1"></i> Ajustar Estoque
                    </button>
                </div>
            </div>

            {{-- Formulário de Filtros --}}
            <form action="{{ route('almoxarifado.movimentacoes.index') }}" method="GET" id="formFiltrosMovimentacoes">
                <div class="form-row align-items-center">
                    {{-- Busca geral --}}
                    <div class="col-12 col-md-3 mb-2">
                        <div class="input-group">
                            <input type="text"
                                   name="busca"
                                   class="form-control text-dark"
                                   placeholder="Buscar item, setor, fornecedor..."
                                   value="{{ $busca ?? '' }}"
                                   aria-label="Buscar movimentações">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit" title="Buscar">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Filtro por Item --}}
                    <div class="col-12 col-sm-6 col-md-3 mb-2">
                        <select name="item_id" class="form-control text-dark" onchange="this.form.submit()">
                            <option value="">Todos os itens</option>
                            @foreach ($itens as $it)
                                <option value="{{ $it->id }}" {{ (string)$itemId === (string)$it->id ? 'selected' : '' }}>
                                    {{ $it->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por Tipo --}}
                    <div class="col-12 col-sm-6 col-md-2 mb-2">
                        <select name="tipo" class="form-control text-dark" onchange="this.form.submit()">
                            <option value="">Todos os tipos</option>
                            <option value="entrada" {{ $tipo === 'entrada' ? 'selected' : '' }}>Entradas (+)</option>
                            <option value="saida" {{ $tipo === 'saida' ? 'selected' : '' }}>Saídas (-)</option>
                            <option value="ajuste" {{ $tipo === 'ajuste' ? 'selected' : '' }}>Ajustes (~)</option>
                        </select>
                    </div>

                    {{-- Data Inicial --}}
                    <div class="col-12 col-sm-6 col-md-2 mb-2">
                        <input type="date"
                               name="data_inicio"
                               class="form-control text-dark"
                               value="{{ $dataInicio ?? '' }}"
                               title="Data inicial"
                               onchange="this.form.submit()">
                    </div>

                    {{-- Data Final --}}
                    <div class="col-12 col-sm-6 col-md-2 mb-2">
                        <input type="date"
                               name="data_fim"
                               class="form-control text-dark"
                               value="{{ $dataFim ?? '' }}"
                               title="Data final"
                               onchange="this.form.submit()">
                    </div>
                </div>

                @if (!empty($busca) || !empty($itemId) || !empty($tipo) || !empty($dataInicio) || !empty($dataFim) || !empty($setor))
                    <div class="mt-2 d-flex align-items-center">
                        <a href="{{ route('almoxarifado.movimentacoes.index') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times mr-1"></i> Limpar filtros
                        </a>
                        <span class="badge badge-light border text-muted ml-2 py-1 px-2">
                            Filtros ativos
                        </span>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Card com a Tabela de Movimentações --}}
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h3 class="card-title text-bold mb-0">
                <i class="fas fa-list-alt mr-2 text-success"></i>Histórico de Movimentações ({{ $movimentacoes->total() }})
            </h3>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th style="width: 140px;">Data / Hora</th>
                        <th style="width: 110px;" class="text-center">Tipo</th>
                        <th>Item do Almoxarifado</th>
                        <th style="width: 140px;" class="text-center">Quantidade</th>
                        <th style="width: 160px;" class="text-center">Saldo Físico</th>
                        <th>Origem / Destino / Detalhes</th>
                        <th style="width: 150px;">Registrado por</th>
                        <th style="width: 90px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimentacoes as $mov)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $mov->id }}</td>
                            <td class="text-nowrap text-dark">
                                <i class="far fa-calendar-alt text-secondary mr-1"></i>
                                {{ $mov->data_movimentacao ? $mov->data_movimentacao->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                @if ($mov->tipo === 'entrada')
                                    <span class="badge badge-success px-2 py-1 font-weight-bold">
                                        <i class="fas fa-arrow-down mr-1"></i>Entrada
                                    </span>
                                @elseif ($mov->tipo === 'saida')
                                    <span class="badge badge-danger px-2 py-1 font-weight-bold">
                                        <i class="fas fa-arrow-up mr-1"></i>Saída
                                    </span>
                                @else
                                    <span class="badge badge-info px-2 py-1 font-weight-bold">
                                        <i class="fas fa-sliders-h mr-1"></i>Ajuste
                                    </span>
                                @endif
                            </td>
                            <td class="font-weight-bold text-dark">
                                <div>{{ $mov->item->nome ?? 'Item #' . $mov->item_id }}</div>
                                @if($mov->item?->categoria)
                                    <small class="text-muted">
                                        <i class="fas fa-tag mr-1"></i>{{ $mov->item->categoria->nome }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($mov->tipo === 'entrada')
                                    <span class="text-success font-weight-bold h6 mb-0">
                                        + {{ $mov->quantidade_formatada }} {{ $mov->item->unidade_medida ?? '' }}
                                    </span>
                                @elseif ($mov->tipo === 'saida')
                                    <span class="text-danger font-weight-bold h6 mb-0">
                                        - {{ $mov->quantidade_formatada }} {{ $mov->item->unidade_medida ?? '' }}
                                    </span>
                                @else
                                    <span class="text-info font-weight-bold h6 mb-0">
                                        ~ {{ $mov->quantidade_formatada }} {{ $mov->item->unidade_medida ?? '' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <span class="badge badge-light border text-dark font-weight-normal px-2 py-1" title="Saldo Anterior → Saldo Posterior">
                                    {{ number_format((float)$mov->saldo_anterior, 0, ',', '.') }}
                                    <i class="fas fa-arrow-right mx-1 text-secondary"></i>
                                    <strong>{{ number_format((float)$mov->saldo_posterior, 0, ',', '.') }} {{ $mov->item->unidade_medida ?? '' }}</strong>
                                </span>
                            </td>
                            <td>
                                @if ($mov->tipo === 'entrada')
                                    @if ($mov->fornecedor)
                                        <div><i class="fas fa-truck text-secondary mr-1"></i><strong>Fornecedor:</strong> {{ $mov->fornecedor }}</div>
                                    @endif
                                    @if ($mov->recebido_por)
                                        <div class="small text-muted"><i class="fas fa-user-check text-secondary mr-1"></i>Recebido por: {{ $mov->recebido_por }}</div>
                                    @endif
                                    @if ($mov->numero_documento)
                                        <div class="small text-muted"><i class="fas fa-file-invoice text-secondary mr-1"></i>Doc: {{ $mov->numero_documento }}</div>
                                    @endif
                                @elseif ($mov->tipo === 'saida')
                                    @if ($mov->setor)
                                        <div><i class="fas fa-map-marker-alt text-danger mr-1"></i><strong>Setor:</strong> {{ $mov->setor }}</div>
                                    @endif
                                    @if ($mov->retirado_por)
                                        <div class="small text-muted"><i class="fas fa-user text-secondary mr-1"></i>Retirado por: {{ $mov->retirado_por }}</div>
                                    @endif
                                @else
                                    @if ($mov->motivo_ajuste)
                                        <div><i class="fas fa-clipboard-check text-info mr-1"></i><strong>Motivo:</strong> {{ $mov->motivo_ajuste }}</div>
                                    @endif
                                @endif

                                @if ($mov->observacao)
                                    <div class="small text-muted font-italic mt-1">
                                        "{{ $mov->observacao }}"
                                    </div>
                                @endif
                            </td>
                            <td class="text-dark small">
                                <i class="fas fa-user-circle text-secondary mr-1"></i>
                                {{ $mov->usuario->name ?? 'Sistema' }}
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-outline-danger btn-sm" title="Estornar movimentação"
                                        data-toggle="modal" data-target="#modalEstornarMovimentacao{{ $mov->id }}">
                                    <i class="fas fa-undo-alt"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Modal de Estorno da Linha --}}
                        @include('almoxarifado.movimentacoes.modals._delete', ['mov' => $mov])
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-dark">
                                    <i class="fas fa-exchange-alt fa-3x mb-3 text-secondary"></i>
                                    @if (!empty($busca) || !empty($itemId) || !empty($tipo) || !empty($dataInicio) || !empty($dataFim))
                                        <h5 class="text-dark">Nenhuma movimentação encontrada com os filtros selecionados</h5>
                                        <p class="text-secondary mb-3">Tente ajustar a busca ou limpe os filtros para ver todo o histórico.</p>
                                        <a href="{{ route('almoxarifado.movimentacoes.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times mr-1"></i> Limpar filtros
                                        </a>
                                    @else
                                        <h5 class="text-dark">Nenhuma movimentação registrada no almoxarifado</h5>
                                        <p class="text-secondary mb-3">Comece registrando o recebimento de compras ou retiradas para setores.</p>
                                        <div>
                                            <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#modalNovaEntrada">
                                                <i class="fas fa-arrow-circle-down mr-1"></i> Registrar Primeira Entrada
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalNovaSaida">
                                                <i class="fas fa-arrow-circle-up mr-1"></i> Registrar Primeira Saída
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movimentacoes->hasPages())
            <div class="card-footer bg-light d-flex justify-content-end">
                {{ $movimentacoes->links() }}
            </div>
        @endif
    </div>

    {{-- Modais de Ações Globais --}}
    @include('almoxarifado.movimentacoes.modals._entrada', ['itens' => $itens])
    @include('almoxarifado.movimentacoes.modals._saida', ['itens' => $itens, 'setoresExistentes' => $setoresExistentes])
    @include('almoxarifado.movimentacoes.modals._ajuste', ['itens' => $itens])
@stop

@push('js')
<script>
    // --- LÓGICA DE ENTRADA ---
    function atualizarInfoEntrada(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        var unidade = opt.getAttribute('data-unidade') || 'UN';
        var saldo = parseFloat(opt.getAttribute('data-saldo')) || 0;
        var formatado = opt.getAttribute('data-formatado') || '0';

        $('#entrada_unidade_label').text(unidade);
        $('#txt_saldo_anterior_entrada').text(formatado + ' ' + unidade);

        if (selectEl.value) {
            $('#box_projecao_entrada').show();
            calcularSaldoPosteriorEntrada();
        } else {
            $('#box_projecao_entrada').hide();
        }
    }

    function calcularSaldoPosteriorEntrada() {
        var opt = document.getElementById('entrada_item_id');
        if (!opt || !opt.value) return;

        var selected = opt.options[opt.selectedIndex];
        var saldoAnterior = parseFloat(selected.getAttribute('data-saldo')) || 0;
        var unidade = selected.getAttribute('data-unidade') || 'UN';
        var qtd = parseFloat($('#entrada_quantidade').val()) || 0;

        var novoSaldo = saldoAnterior + qtd;
        $('#txt_qtd_entrada').text(qtd.toLocaleString('pt-BR') + ' ' + unidade);
        $('#txt_saldo_posterior_entrada').text(novoSaldo.toLocaleString('pt-BR') + ' ' + unidade);
    }

    // --- LÓGICA DE SAÍDA ---
    function atualizarInfoSaida(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        var unidade = opt.getAttribute('data-unidade') || 'UN';
        var saldo = parseFloat(opt.getAttribute('data-saldo')) || 0;
        var formatado = opt.getAttribute('data-formatado') || '0';

        $('#saida_unidade_label').text(unidade);
        $('#txt_saldo_anterior_saida').text(formatado + ' ' + unidade);

        if (selectEl.value) {
            $('#box_projecao_saida').show();
            calcularSaldoPosteriorSaida();
        } else {
            $('#box_projecao_saida').hide();
        }
    }

    function calcularSaldoPosteriorSaida() {
        var opt = document.getElementById('saida_item_id');
        if (!opt || !opt.value) return;

        var selected = opt.options[opt.selectedIndex];
        var saldoAnterior = parseFloat(selected.getAttribute('data-saldo')) || 0;
        var unidade = selected.getAttribute('data-unidade') || 'UN';
        var qtd = parseFloat($('#saida_quantidade').val()) || 0;

        var novoSaldo = saldoAnterior - qtd;
        $('#txt_qtd_saida').text(qtd.toLocaleString('pt-BR') + ' ' + unidade);

        if (novoSaldo < 0) {
            $('#txt_saldo_posterior_saida').text(novoSaldo.toLocaleString('pt-BR') + ' ' + unidade).removeClass('text-success').addClass('text-danger');
            $('#alerta_estoque_insuficiente').show();
            $('#btn_confirmar_saida').prop('disabled', true);
        } else {
            $('#txt_saldo_posterior_saida').text(novoSaldo.toLocaleString('pt-BR') + ' ' + unidade).removeClass('text-danger').addClass('text-success');
            $('#alerta_estoque_insuficiente').hide();
            $('#btn_confirmar_saida').prop('disabled', false);
        }
    }

    // --- LÓGICA DE AJUSTE ---
    var modoAjusteAtual = 'saldo_fisico';

    function definirModoAjuste(modo) {
        modoAjusteAtual = modo;
        if (modo === 'saldo_fisico') {
            calcularDiferencaSaldoFisico();
        } else {
            calcularDiferencaQuantidadeDireta();
        }
    }

    function atualizarInfoAjuste(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        var unidade = opt.getAttribute('data-unidade') || 'UN';
        var formatado = opt.getAttribute('data-formatado') || '0';

        $('.ajuste_unidade_label').text(unidade);
        $('#txt_saldo_anterior_ajuste').text(formatado + ' ' + unidade);

        if (selectEl.value) {
            $('#box_projecao_ajuste').show();
            if (modoAjusteAtual === 'saldo_fisico') {
                calcularDiferencaSaldoFisico();
            } else {
                calcularDiferencaQuantidadeDireta();
            }
        } else {
            $('#box_projecao_ajuste').hide();
        }
    }

    function calcularDiferencaSaldoFisico() {
        var opt = document.getElementById('ajuste_item_id');
        if (!opt || !opt.value) return;

        var selected = opt.options[opt.selectedIndex];
        var saldoAnterior = parseFloat(selected.getAttribute('data-saldo')) || 0;
        var unidade = selected.getAttribute('data-unidade') || 'UN';

        var inputNovo = $('#ajuste_novo_estoque').val();
        if (inputNovo === '' || inputNovo === null) {
            $('#txt_diferenca_apurada').text('-');
            $('#txt_variacao_ajuste').text('Variação: 0');
            $('#txt_saldo_posterior_ajuste').text(saldoAnterior.toLocaleString('pt-BR') + ' ' + unidade);
            return;
        }

        var novoSaldo = parseFloat(inputNovo) || 0;
        var diferenca = novoSaldo - saldoAnterior;

        if (diferenca > 0) {
            $('#txt_diferenca_apurada').html('<span class="text-success">+ ' + diferenca.toLocaleString('pt-BR') + ' ' + unidade + ' (Acréscimo / Sobra)</span>');
            $('#txt_variacao_ajuste').html('<span class="text-success">+ ' + diferenca.toLocaleString('pt-BR') + ' ' + unidade + '</span>');
        } else if (diferenca < 0) {
            $('#txt_diferenca_apurada').html('<span class="text-danger">' + diferenca.toLocaleString('pt-BR') + ' ' + unidade + ' (Redução / Quebra)</span>');
            $('#txt_variacao_ajuste').html('<span class="text-danger">' + diferenca.toLocaleString('pt-BR') + ' ' + unidade + '</span>');
        } else {
            $('#txt_diferenca_apurada').html('<span class="text-muted">0 ' + unidade + ' (Sem alteração)</span>');
            $('#txt_variacao_ajuste').html('<span class="text-muted">0 ' + unidade + '</span>');
        }

        $('#txt_saldo_posterior_ajuste').text(novoSaldo.toLocaleString('pt-BR') + ' ' + unidade);
    }

    function calcularDiferencaQuantidadeDireta() {
        var opt = document.getElementById('ajuste_item_id');
        if (!opt || !opt.value) return;

        var selected = opt.options[opt.selectedIndex];
        var saldoAnterior = parseFloat(selected.getAttribute('data-saldo')) || 0;
        var unidade = selected.getAttribute('data-unidade') || 'UN';

        var operacao = $('#ajuste_tipo_operacao').val();
        var qtd = parseFloat($('#ajuste_quantidade_direta').val()) || 0;

        var novoSaldo = (operacao === 'acrescimo') ? (saldoAnterior + qtd) : (saldoAnterior - qtd);

        if (operacao === 'acrescimo') {
            $('#txt_variacao_ajuste').html('<span class="text-success">+ ' + qtd.toLocaleString('pt-BR') + ' ' + unidade + '</span>');
        } else {
            $('#txt_variacao_ajuste').html('<span class="text-danger">- ' + qtd.toLocaleString('pt-BR') + ' ' + unidade + '</span>');
        }

        if (novoSaldo < 0) {
            $('#txt_saldo_posterior_ajuste').text(novoSaldo.toLocaleString('pt-BR') + ' ' + unidade + ' (Inválido)').removeClass('text-info').addClass('text-danger');
            $('#btn_confirmar_ajuste').prop('disabled', true);
        } else {
            $('#txt_saldo_posterior_ajuste').text(novoSaldo.toLocaleString('pt-BR') + ' ' + unidade).removeClass('text-danger').addClass('text-info');
            $('#btn_confirmar_ajuste').prop('disabled', false);
        }
    }

    $(document).ready(function () {
        // Prevenção de double-submit nos formulários de movimentação
        $('.btn-submit-movimentacao').closest('form').on('submit', function () {
            if (!this.checkValidity()) {
                return;
            }
            var $btn = $(this).find('.btn-submit-movimentacao');
            $btn.prop('disabled', true).addClass('disabled');
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Processando...');
        });

        // Restaura botões caso os modais sejam reabertos
        $('#modalNovaEntrada, #modalNovaSaida, #modalNovoAjuste').on('show.bs.modal', function () {
            var $btn = $(this).find('.btn-submit-movimentacao');
            $btn.prop('disabled', false).removeClass('disabled');
        });

        // Prevenção de double-click no estorno de movimentação
        $(document).on('submit', '.form-estornar-movimentacao', function (e) {
            var $form = $(this);
            if ($form.data('submitting')) {
                e.preventDefault();
                return false;
            }
            $form.data('submitting', true);

            var $btn = $form.find('.btn-confirmar-estorno');
            $btn.addClass('disabled').css('pointer-events', 'none').prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Estornando...');
            $form.closest('.modal').find('button').addClass('disabled').css('pointer-events', 'none').prop('disabled', true);
        });

        $(document).on('click', '.btn-confirmar-estorno', function (e) {
            var $form = $(this).closest('form');
            if ($form.data('submitting')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endpush

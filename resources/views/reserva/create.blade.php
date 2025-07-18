@extends('adminlte::page')

@section('title', isset($reserva) ? 'Editar Reserva' : 'Cadastrar Reserva')

@section('content_header')
    <h1>{{ isset($reserva) ? 'Editar Reserva' : 'Cadastrar Reserva' }}</h1>
@stop

@section('content')
    <div class='container-fluid'>
        <div class='row'>
            <!-- Coluna Principal (Formulário) -->
            <div class='col-lg-8'>
                <div class='card'>
                    <div class='card-header green bg-primary text-white'>
                        <h3 class='card-title'>
                            {{ isset($reserva) ? 'Editar informações da Reserva' : 'Preencha os dados da nova Reserva' }}
                        </h3>
                    </div>
                    <div class='card-body'>
                        <form id='createReservaForm'
                            action='{{ isset($reserva) ? route('reserva.update', $reserva->id) : route('reserva.store') }}'
                            method='POST' enctype='multipart/form-data'>
                            @csrf
                            @if (isset($reserva))
                                @method('PUT')
                            @endif

                            <div class='form-group row' id='campoSituacao'>
                                <label class='col-md-3 label-control'><strong>* Situação</strong></label>
                                <div class='situacao-options'>
                                    @php
                                        $situacaoAtual = old('situacao', $reserva->situacao ?? '');
                                        $isEdicao = isset($reserva);
                                        $podeAlterarSituacao = true;

                                        // Regras para edição
                                        if ($isEdicao) {
                                            if (in_array($situacaoAtual, ['hospedado', 'finalizada', 'cancelado'])) {
                                                $podeAlterarSituacao = false;
                                            }
                                        }
                                    @endphp

                                    <label class='radio-option'>
                                        <input type='radio' name='situacao' value='pre-reserva'
                                            @checked($situacaoAtual === 'pre-reserva') @if ($isEdicao && !in_array($situacaoAtual, ['pre-reserva', 'reserva']) && $podeAlterarSituacao) disabled @endif
                                            @if (!$podeAlterarSituacao) disabled @endif required>
                                        <span class='badge badge-warning'>pré-reservar</span>
                                    </label>

                                    <label class='radio-option'>
                                        <input type='radio' name='situacao' value='reserva' @checked($situacaoAtual === 'reserva')
                                            @if ($isEdicao && !in_array($situacaoAtual, ['pre-reserva', 'reserva']) && $podeAlterarSituacao) disabled @endif
                                            @if (!$podeAlterarSituacao) disabled @endif>
                                        <span class='badge badge-primary'>reservar</span>
                                    </label>

                                    @if (!$isEdicao)
                                        <label class='radio-option'>
                                            <input type='radio' name='situacao' value='hospedado'
                                                @checked($situacaoAtual === 'hospedado')>
                                            <span class='badge badge-danger'>hospedar</span>
                                        </label>

                                        <label class='radio-option'>
                                            <input type='radio' name='situacao' value='bloqueado'
                                                @checked($situacaoAtual === 'bloqueado')>
                                            <span class='badge badge-dark'>bloquear datas</span>
                                        </label>
                                    @else
                                        @if ($situacaoAtual === 'hospedado')
                                            <label class='radio-option'>
                                                <input type='radio' name='situacao' value='hospedado' checked disabled>
                                                <span class='badge badge-danger'>hospedado</span>
                                            </label>
                                        @endif

                                        @if ($situacaoAtual === 'bloqueado')
                                            <label class='radio-option'>
                                                <input type='radio' name='situacao' value='bloqueado'
                                                    @checked($situacaoAtual === 'bloqueado')>
                                                <span class='badge badge-dark'>bloquear datas</span>
                                            </label>
                                        @endif

                                        @if ($situacaoAtual === 'finalizada')
                                            <label class='radio-option'>
                                                <input type='radio' name='situacao' value='finalizada' checked disabled>
                                                <span class='badge badge-success'>finalizada</span>
                                            </label>
                                        @endif

                                        @if ($situacaoAtual === 'cancelado')
                                            <label class='radio-option'>
                                                <input type='radio' name='situacao' value='cancelado' checked disabled>
                                                <span class='badge badge-secondary'>cancelado</span>
                                            </label>
                                        @endif
                                    @endif

                                    <!-- Input hidden para situações não editáveis -->
                                    @if ($isEdicao && !$podeAlterarSituacao)
                                        <input type='hidden' name='situacao' value='{{ $situacaoAtual }}'>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <hr>
                            </div>

                            <div class='form-group row'>
                                <label for='hospede_id' class='col-md-3 label-control'>* Hóspede</label>
                                <div class='col-sm-4'>
                                    @php
                                        $hospedeSelecionado = old('hospede_id', $reserva->hospede_id ?? '');
                                    @endphp

                                    @if ($hospedeSelecionado)
                                        <select class='form-control select2' name='hospede_id_disabled' id='hospede_id'
                                            disabled>
                                            <option value=''>Selecione um hóspede</option>
                                            @foreach ($hospedes as $hospede)
                                                @if ($hospede->nome !== 'Bloqueado')
                                                    <option value="{{ $hospede->id }}"
                                                        {{ $hospedeSelecionado == $hospede->id ? 'selected' : '' }}>
                                                        {{ $hospede->nome }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type='hidden' name='hospede_id' value="{{ $hospedeSelecionado }}">
                                    @else
                                        <select class='form-control select2' name='hospede_id' id='hospede_id'>
                                            <option value="">Selecione um hóspede</option>
                                            @foreach ($hospedes as $hospede)
                                                @if ($hospede->nome !== 'Bloqueado')
                                                    <option value="{{ $hospede->id }}"
                                                        {{ old('hospede_id') == $hospede->id ? 'selected' : '' }}>
                                                        {{ $hospede->nome }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <div class="col-sm-2">
                                    <button type="button" id="btn-addhospede" class="btn btn-primary" data-toggle="modal"
                                        data-target="#modalCadastrarHospede">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Campo do período -->
                            <div class="form-group row" id="campoPeriodo">
                                <label class="col-md-3 label-control" for="periodo">* Período</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="periodo" name="periodo"
                                        value="{{ old('periodo', isset($reserva) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '') }}" />
                                    <small style="color: red">Selecione um período antes de escolher o quarto</small>
                                </div>
                            </div>

                            <input type="hidden" name="data_checkin" id="data_checkin"
                                value="{{ old('data_checkin', $reserva->data_checkin ?? '') }}">
                            <input type="hidden" name="data_checkout" id="data_checkout"
                                value="{{ old('data_checkout', $reserva->data_checkout ?? '') }}">

                            <!-- Select de quartos agrupados por categoria -->
                            <div class="form-group row" id="campoQuarto">
                                <label class="col-md-3 label-control" for="quarto">* Quarto</label>
                                <div class="col-md-4">
                                    <select class="form-control select2" id="quarto" name="quarto_id" disabled>
                                        <option value="">Selecione um quarto</option>
                                        @if (isset($quartosAgrupados))
                                            @foreach ($quartosAgrupados as $categoria => $quartos)
                                                <optgroup label="{{ $categoria }}">
                                                    @foreach ($quartos as $quarto)
                                                        <option value="{{ $quarto->id }}"
                                                            {{ old('quarto_id', $reserva->quarto_id ?? '') == $quarto->id ? 'selected' : '' }}>
                                                            {{ $quarto->nome }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            @if (isset($reserva))
                                <input type="hidden" id="reserva_id" value="{{ $reserva->id }}">
                                <input type="hidden" id="quarto_selecionado" value="{{ $reserva->quarto_id }}">
                            @endif

                            <div class="form-group row">
                                <label class="col-md-3 label-control" for="valor_diaria">* Valor da diária:</label>
                                <div class="col-md-4">
                                    <div><input class="form-control" type="text" name="valor_diaria"
                                            id="valor_diaria"
                                            value="{{ old('valor_diaria', isset($reserva->valor_diaria) ? number_format($reserva->valor_diaria, 2, ',', '.') : '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control"><strong>Nº hóspedes</strong></label>
                                <div class="row col-md-6">
                                    <div class="col-md-3">
                                        <label for="n_adultos">* Nº adultos</label>
                                        <input type="number" name="n_adultos" id="n_adultos" class="form-control"
                                            value="{{ old('n_adultos', $reserva->n_adultos ?? 1) }}" min="1">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="n_criancas">* Nº crianças</label>
                                        <input type="number" name="n_criancas" id="n_criancas" class="form-control"
                                            value="{{ old('n_criancas', $reserva->n_criancas ?? 0) }}" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row" id="campoObservacoes">
                                <label for="observacao" class="col-md-3 label-control">Observações</label>
                                <div class="col-md-9">
                                    <textarea class="form-control" name="observacao" rows="3" id="observacoes">{{ old('observacao', $reserva->observacao ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="card-footer">
                                @if (isset($reserva))
                                    <!-- Botão Gerar Voucher (apenas para reserva ou pre-reserva) -->
                                    @if (isset($reserva) && in_array($reserva->situacao, ['reserva', 'pre-reserva']))
                                        <a href="{{ route('reservas.voucher', $reserva->id) }}" class="btn btn-info"
                                            target="_blank">
                                            <i class="fa fa-file-pdf-o"></i> Gerar Voucher
                                        </a>
                                    @endif
                                    <!-- Botão Cancelar (substitui o Excluir) -->
                                    @if (in_array($reserva->situacao, ['pre-reserva']))
                                        <button type="button" class="btn" id="btn-cancelar-reserva"
                                            data-reserva-id="{{ $reserva->id }}">
                                            <i class="fas fa-ban"></i> Cancelar
                                        </button>
                                    @endif

                                    <!-- Botão Hospedar (aparece quando é o dia do check-in) -->
                                    @if (isset($podeHospedar) && $podeHospedar)
                                        <button type="button" class="btn btn-info" id="btn-hospedar"
                                            data-reserva-id="{{ $reserva->id }}">
                                            <i class="fas fa-bed"></i> Hospedar
                                        </button>
                                    @endif

                                    <!-- Botão Finalizar (aparece quando hospedado) -->
                                    @if ($reserva->situacao === 'hospedado')
                                        <button type="button" class="btn btn-success" id="btn-finalizar"
                                            data-reserva-id="{{ $reserva->id }}">
                                            <i class="fa-solid fa-right-from-bracket"></i> Finalizar
                                        </button>
                                    @endif

                                    <!-- Mostrar status se finalizada ou cancelada -->
                                    @if ($reserva->situacao === 'finalizada')
                                        <span class="mr-3">
                                            <i class="fas fa-check"></i> Reserva finalizada
                                        </span>
                                    @endif

                                    @if ($reserva->situacao === 'cancelado')
                                        <span class="badge badge-secondary badge-lg">
                                            <i class="fas fa-ban"></i> RESERVA CANCELADA
                                        </span>
                                    @endif
                                @endif

                                <a href="{{ route('reserva.index') }}" class="btn btn-secondary"
                                    id="btn-voltar">Voltar</a>

                                @if (!isset($reserva) || !in_array($reserva->situacao ?? '', ['finalizada', 'cancelado']))
                                    <button type="submit" class="btn"
                                        id="btn-atualizar-criar">{{ isset($reserva) ? 'Atualizar Reserva' : 'Adicionar Reserva' }}</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral (Resumo e Pagamentos) -->
            @if (isset($reserva))
                <div class='col-lg-4'>
                    <!-- Card de Resumo -->
                    <div class='card shadow-sm mb-4'>
                        <div class='card-header bg-light'>
                            <h5 class='mb-0 text-uppercase text-muted' style='letter-spacing: 1px;'>FALTA LANÇAR</h5>
                            <h2 class='text-danger mb-0' id='falta-lancar'>R$ 0,00</h2>
                            <input type='hidden' id='valor-falta-lancar' name='falta_lancar' value='0.00'>
                        </div>
                    </div>

                    <!-- Card de Resumo Detalhado -->
                    <div class='card shadow-sm mb-4'>
                        <div class='card-header bg-light'>
                            <h5 class='mb-0 text-uppercase text-muted' style='letter-spacing: 1px;'>RESUMO</h5>
                        </div>
                        <div class='card-body'>
                            <div class='d-flex justify-content-between mb-2'>
                                <span>Nº diárias</span>
                                <span id='num-diarias'>1</span>
                            </div>
                            <div class='d-flex justify-content-between mb-2'>
                                <span>Diária Média</span>
                                <span id='diaria-media'>R$ 0,00</span>
                            </div>
                            <div class='d-flex justify-content-between mb-2'>
                                <span>Diárias</span>
                                <span id='total-diarias'>R$ 0,00</span>
                            </div>
                            <div class='d-flex justify-content-between mb-2'>
                                <span>Produtos</span>
                                <span id='total-produtos'>R$ 0,00</span>
                            </div>
                            <hr>
                            <div class='d-flex justify-content-between mb-2'>
                                <strong>Total</strong>
                                <strong id='total-geral'>R$ 0,00</strong>
                            </div>
                            <div class='d-flex justify-content-between text-success'>
                                <span>Recebido</span>
                                <span id='total-recebido'>R$ 0,00</span>
                                <input type='hidden' id='valor-recebido' name='recebido' value='0.00'>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Atividades na Reserva -->
                    @if (!in_array($reserva->situacao, ['finalizada', 'cancelado']))
                        <div class='card shadow-sm'>
                            <div class='card-header bg-light d-flex justify-content-between align-items-center'>
                                <h5 class='mb-0 text-uppercase text-muted' style='letter-spacing: 1px;'>ATIVIDADES NA
                                    RESERVA
                                </h5>
                                <div class='dropdown'>
                                    <button class='btn btn-sm btn-outline-primary dropdown-toggle' type='button'
                                        id='dropdownMenuButton' data-toggle='dropdown' aria-haspopup='true'
                                        aria-expanded='false'>
                                        <i class='fas fa-plus'></i>
                                    </button>
                                    <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
                                        <a class='dropdown-item' href='#'
                                            onclick="mostrarFormulario('pagamento')">Adicionar Pagamento</a>
                                        <a class='dropdown-item' href='#'
                                            onclick="mostrarFormulario('produto')">Adicionar Produto</a>
                                    </div>
                                </div>
                            </div>
                            <div class='card-body'>
                                <div id='lista-atividades'>
                                    <p class='text-muted text-center'>Nenhuma atividade adicionada</p>
                                </div>

                                <!-- Formulário para adicionar transação (pagamento) -->
                                <div id='form-transacao-pagamento' style='display: none;'>
                                    <hr>
                                    <h6 id='titulo-form-pagamento'>Adicionar Pagamento</h6>
                                    <div class='form-group'>
                                        <label for='descricao_transacao'>Descrição</label>
                                        <input type='text' class='form-control' id='descricao_transacao'
                                            placeholder='Ex: Pagamento da diária'>
                                    </div>
                                    <div class='form-group'>
                                        <label for='valor_transacao'>Valor</label>
                                        <input type='text' class='form-control' id='valor_transacao'
                                            placeholder='0,00'>
                                    </div>
                                    <div class='form-group'>
                                        <label for='forma_pagamento_transacao'>Forma de Pagamento</label>
                                        <select class='form-control' id='forma_pagamento_transacao'>
                                            <option value=''>Selecione...</option>
                                            @if (isset($formasPagamento))
                                                @foreach ($formasPagamento as $forma)
                                                    <option value='{{ $forma->id }}'
                                                        {{ old('forma_pagamento_id') == $forma->id ? 'selected' : '' }}>
                                                        {{ $forma->descricao }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="data_transacao">Data</label>
                                        <input type="date" class="form-control" id="data_transacao"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="observacoes_transacao">Observações</label>
                                        <textarea class="form-control" id="observacoes_transacao" rows="2" placeholder="Observações opcionais"></textarea>
                                    </div>
                                    <input type="hidden" id="categoria_transacao" value="hospedagem">
                                    <input type="hidden" id="tipo_transacao" value="pagamento">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success btn-sm"
                                            id="btn-salvar-transacao">Salvar</button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            id="btn-cancelar-transacao">Cancelar</button>
                                    </div>
                                </div>

                                <!-- Formulário para adicionar produto -->
                                <div id="form-transacao-produto" style="display: none;">
                                    <hr>
                                    <h6 id="titulo-form-produto">Adicionar Produto</h6>
                                    <div class="form-group">
                                        <label for="produto_id">Produto</label>
                                        <select class="form-control select2" id="produto_id">
                                            <option value="">Selecione um produto</option>
                                            @if (isset($produtos))
                                                @foreach ($produtos as $produto)
                                                    <option value="{{ $produto->id }}"
                                                        data-valor="{{ $produto->preco_venda }}"
                                                        {{ old('produto_id') == $produto->id ? 'selected' : '' }}>
                                                        {{ $produto->descricao }} - R$
                                                        {{ number_format($produto->preco_venda, 2, ',', '.') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-4">
                                            <label for="quantidade_produto">Quantidade</label>
                                            <input type="number" class="form-control" id="quantidade_produto"
                                                value="1" min="1">
                                        </div>
                                        <div class="col-md-8">
                                            <label for="total_item_produto">Total do Item</label>
                                            <input type="text" class="form-control" id="total_item_produto" readonly
                                                value="0,00">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm mb-3"
                                        id="btn-adicionar-item-produto">Adicionar Item</button>

                                    <div id="lista-itens-produto">
                                        <!-- Itens de produto adicionados dinamicamente aqui -->
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="total_produtos_adicionados">Total de Produtos Adicionados</label>
                                        <input type="text" class="form-control" id="total_produtos_adicionados"
                                            readonly value="0,00">
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success btn-sm"
                                            id="btn-salvar-produtos">Salvar
                                            Produtos</button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            id="btn-cancelar-produtos">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Card apenas de visualização para reservas finalizadas/canceladas -->
                        <div class='card shadow-sm'>
                            <div class='card-header bg-light'>
                                <h5 class='mb-0 text-uppercase text-muted' style='letter-spacing: 1px;'>ATIVIDADES DA
                                    RESERVA</h5>
                            </div>
                            <div class='card-body'>
                                <div id='lista-atividades'>
                                    <p class='text-muted text-center'>Carregando atividades...</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Dicas -->
                    <div class="dicas mt-4">
                        <div id="quadroDicas" class="card shadow-sm transition-all">
                            <div class="card-body pb-2">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">DICAS</h6>
                                    <button id="btnToggleDicas" class="btn btn-sm text-muted p-0"
                                        style="font-size: 18px;">
                                        <i id="iconeToggleDicas" class="fas fa-minus"></i>
                                    </button>
                                </div>

                                <div id="conteudoDicas">
                                    <div id="textoDica" class="text-dark mb-3">
                                    </div>

                                    <div class="d-flex justify-content-center mb-2">
                                        <div id="indicadores" class="d-flex gap-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if (!isset($reserva))
        <div class="dicas mt-4">
            <div id="quadroDicas" class="card shadow-sm transition-all">
                <div class="card-body pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">DICAS</h6>
                        <button id="btnToggleDicas" class="btn btn-sm text-muted p-0" style="font-size: 18px;">
                            <i id="iconeToggleDicas" class="fas fa-minus"></i>
                        </button>
                    </div>

                    <div id="conteudoDicas">
                        <div id="textoDica" class="text-dark mb-3">
                        </div>

                        <div class="d-flex justify-content-center mb-2">
                            <div id="indicadores" class="d-flex gap-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Cadastro de Hóspede -->
    <div class="modal fade" id="modalCadastrarHospede" tabindex="-1" role="dialog"
        aria-labelledby="modalHospedeLabel" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <form method="POST" action="{{ route('hospede.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHospedeLabel">Cadastrar Hóspede</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-md-2 label-control" for="nome">* Nome completo:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" required="required" type="text" name="nome"
                                        id="nome" autocomplete="off"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2 label-control" for="email">Email:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="email" name="email" id="email"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2 label-control" for="telefone">Telefone:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="tel" name="telefone" id="telefone"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('hospede.create') }}" class="btn btn-secondary">Cadastro Completo</a>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet' />
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <link href='https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css' rel='stylesheet' />
    <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css' />
    <style>
        .label-control {
            text-align: right
        }

        .card-footer {
            text-align: right
        }

        .situacao-options {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 1rem;
        }

        .radio-option input[type='radio'] {
            cursor: pointer;
        }

        .radio-option input[type='radio']:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .radio-option input[type='radio']:disabled+.badge {
            opacity: 0.6;
        }

        input[type='number'] {
            max-width: 100%;
            padding: 6px 10px;
        }

        .form-group .row>div {
            margin-right: 10px;
        }

        .indicador {
            transition: background-color 0.3s ease;
        }

        #conteudoDicas {
            overflow: hidden;
            transition: max-height 0.5s ease;
        }

        #conteudoDicas.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
        }

        #delbtn {
            t: left
        }

        /* Estilos para o layout lateral */
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        .d-flex {
            display: flex !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .mb-3 {
            margin-bottom: 1rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        #form-transacao-pagamento,
        #form-transacao-produto {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
        }

        .btn-outline-primary:hover {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .atividade-item {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            background-color: #f8f9fa;
        }

        .atividade-item .atividade-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        .atividade-item .atividade-details {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Estilos para optgroup no select2 */
        .select2-container--default .select2-results__group {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .select2-container--default .select2-results__option[aria-selected] {
            padding-left: 20px;
        }

        /* Estilos para badges de categoria */
        .badge-hospedagem {
            background-color: #007bff;
            color: #fff;
        }

        .badge-produtos {
            background-color: #28a745;
            color: #fff;
        }

        .badge-desconto {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        #btn-atualizar-criar {
            background-color: #26C0C3;
            color: #fff
        }

        #btn-atualizar-criar:hover {
            background-color: #229fa1;
        }

        #btn-cancelar-reserva {
            background-color: #6A1B9A;
            color: #fff
        }

        #btn-cancelar-reserva:hover {
            background-color: #4c0a7a;
        }
    </style>
    <style>
        @media (max-width: 768px) {
            .label-control {
                text-align: start
            }

            .col-lg-8,
            .col-lg-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://cdn.jsdelivr.net/jquery/3.6.0/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'></script>
    <script src='https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js'></script>
    <script src='https://cdn.jsdelivr.net/momentjs/latest/moment.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src='{{ asset('js/endereco.js') }}'></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'selecione...',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $("#telefone").mask("(00) 00000-0000");
            $("#valor_diaria").mask("#.##0,00", {
                reverse: true
            });
            $("#valor_transacao").mask("#.##0,00", {
                reverse: true
            });
        })
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalHospede = document.getElementById("modalHospede");
            modalHospede.addEventListener("hidden.bs.modal", function() {
                location.reload();
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dicas = [{
                    texto: "<strong>Situação:</strong> É o momento em que a reserva se encontra, ela pode estar como: <i>pré-reservado, reservado, hospedado, ou com bloqueio de data.</i> "
                },
                {
                    texto: "<strong>Nº hóspedes:</strong> É o número de hóspedes da reserva, além de informar quantas pessoas estão no quarto, também serve para calcular o valor das diárias"
                },
                {
                    texto: "<strong>Período:</strong> Campo que informa o período da reserva, basta escolher a data de entrada e saída."
                },
                {
                    texto: "<strong>Hóspede:</strong> Este é o campo onde você escolhe ou adiciona o hóspede responsável pela reserva. <i>OBS: Você poderá adicionar outros hóspedes após criar a reserva.</i>"
                },
                {
                    texto: "<strong> Observação:</strong> Campo onde você pode colocar qualquer informação sobre a reserva."
                },
                {
                    texto: "<strong>Quartos por Categoria:</strong> Os quartos estão organizados por categoria para facilitar a seleção. Cada categoria agrupa quartos com características similares."
                },
                {
                    texto: "<strong>Atividades na Reserva:</strong> Adicione pagamentos e produtos à reserva. O resumo é atualizado automaticamente."
                },
                {
                    texto: "<strong>Botões de Ação:</strong> Use 'Hospedar' no dia do check-in, 'Finalizar' quando todos os valores forem recebidos, ou 'Cancelar' a qualquer momento."
                },
            ];

            let dicaAtual = 0;
            const textoDica = document.getElementById("textoDica");
            const indicadores = document.getElementById("indicadores");
            const conteudoDicas = document.getElementById("conteudoDicas");
            const btnToggle = document.getElementById("btnToggleDicas");
            const iconeToggle = document.getElementById("iconeToggleDicas");

            dicas.forEach((_, index) => {
                const span = document.createElement("span");
                span.classList.add("indicador");
                span.style.width = "20px";
                span.style.height = "4px";
                span.style.margin = "0 3px";
                span.style.borderRadius = "2px";
                span.style.backgroundColor = index === 0 ? "#333" : "#e0e0e0";
                indicadores.appendChild(span);
            });

            function trocarDica() {
                $(textoDica).fadeOut(300, () => {
                    textoDica.innerHTML = dicas[dicaAtual].texto;
                    atualizarIndicadores();
                    $(textoDica).fadeIn(300);
                });
                dicaAtual = (dicaAtual + 1) % dicas.length;
            }

            function atualizarIndicadores() {
                [...indicadores.children].forEach((el, idx) => {
                    el.style.backgroundColor = idx === dicaAtual ? "#333" : "#e0e0e0";
                });
            }

            trocarDica();
            setInterval(trocarDica, 8000);

            let expandido = true;

            btnToggle.addEventListener("click", function() {
                expandido = !expandido;

                if (expandido) {
                    conteudoDicas.classList.remove("collapsed");
                    iconeToggle.classList = "fas fa-minus";
                } else {
                    conteudoDicas.classList.add("collapsed");
                    iconeToggle.classList = "fas fa-plus";
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const hospedeBloqueadoId = "{{ $hospedeBloqueado->id ?? '' }}";
            const diaria = $("#valor_diaria");
            const valorDiariaArmazenado = diaria.val();

            function atualizarCampos() {
                const situacao = $('input[name="situacao"]:checked').val();
                if (!situacao) {
                    $('.form-group').each(function() {
                        const id = $(this).attr('id');
                        if (['campoSituacao'].includes(
                                id)) {
                            $(this).slideDown(200);
                        } else {
                            $(this).slideUp(200);
                        }
                    });
                    return
                }

                if (situacao === 'bloqueado') {
                    $('.form-group').each(function() {
                        const id = $(this).attr('id');
                        if (['campoPeriodo', 'campoObservacoes', 'campoSituacao'].includes(
                                id)) {
                            $(this).slideDown(200);
                        } else {
                            $(this).slideUp(200);
                        }
                    });

                    $('#hospede_id').val(hospedeBloqueadoId).prop('readonly', true);
                    $('#valor_diaria').val(0);
                } else if (situacao === 'finalizada' || situacao === 'cancelado') {
                    // Desabilitar campos para reservas finalizadas ou canceladas
                    $('#periodo, #hospede_id, #valor_diaria, #n_adultos, #n_criancas, #observacoes, #btn-addhospede, #dropdownMenuButton')
                        .attr('disabled', true);
                    $('#btn-atualizar-criar').hide();
                    $(':radio:not(:checked)').attr('disabled', true);
                    $('#btn-voltar').removeClass('btn-secondary').addClass('btn-primary');
                } else {
                    $('.form-group').slideDown(200);
                    $('#campoQuarto').hide()
                    $('#hospede_id').prop('disabled', false).val('');
                    if (valorDiariaArmazenado) {
                        diaria.val(valorDiariaArmazenado)
                    } else {
                        $('#valor_diaria').val('');
                    }
                }
            }

            $('input[name="situacao"]').on('change', atualizarCampos);
            atualizarCampos();
        });
    </script>

    <!-- Scripts para funcionalidade de transações -->
    <script>
        $(document).ready(function() {
            let transacoes = [];
            let produtosAdicionados = []; // Array para armazenar os produtos adicionados
            const reservaId = $('#reserva_id').val();

            // Carregar transações existentes se estiver editando
            if (reservaId) {
                carregarTransacoes();
                carregarResumo();
                carregarReservaItens(); // Carregar produtos já adicionados à reserva
            }

            function carregarTransacoes() {
                $.ajax({
                    url: '/transacoes/reserva/' + reservaId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            transacoes = response.transacoes;
                            atualizarListaAtividades();
                        }
                    },
                    error: function() {
                        console.log('Erro ao carregar transações');
                    }
                });
            }

            function carregarReservaItens() {
                $.ajax({
                    url: '/reserva-itens/reserva/' + reservaId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.itens.length > 0) {
                            // Adicionar os itens existentes à lista de atividades
                            response.itens.forEach(function(item) {
                                const atividade = {
                                    id: 'item_' + item.id,
                                    descricao: 'Produto: ' + item.produto.descricao,
                                    valor: item.produto.preco_venda * item.quantidade,
                                    categoria: 'produtos',
                                    tipo: 'item',
                                    data_pagamento: new Date().toISOString().split('T')[0],
                                    status: true,
                                    quantidade: item.quantidade,
                                    produto_nome: item.produto.descricao
                                };
                                transacoes.push(atividade);
                            });
                            atualizarListaAtividades();
                            atualizarResumo(); // Atualizar resumo após carregar itens
                        }
                    },
                    error: function() {
                        console.log('Erro ao carregar itens da reserva');
                    }
                });
            }

            function atualizarInputFaltaLancar(valorNumerico) {
                const valorFormatado = valorNumerico.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                });
                $('#falta-lancar').text('R$ ' + valorFormatado);
                $('#valor-falta-lancar').val(valorNumerico.toFixed(2));
            }

            function atualizarInputRecebido(valor) {
                const valorFormatadoR = valor.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                });
                $('#total-recebido').text('R$ ' + valorFormatadoR);
                $('#valor-recebido').val(valor.toFixed(2));
            }

            function carregarResumo() {
                $.ajax({
                    url: '/transacoes/resumo/' + reservaId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const resumo = response.resumo;
                            if (resumo.valor_diaria != null && !isNaN(resumo.valor_diaria)) {
                                let valorFormatado = parseFloat(resumo.valor_diaria).toFixed(2).replace(
                                    '.', ',');
                                $('#valor_diaria').val(valorFormatado);
                            } else {
                                console.warn('valor_diaria inválido:', resumo.valor_diaria);
                                $('#valor_diaria').val('');
                            }
                            $('#num-diarias').text(resumo.num_diarias);
                            $('#diaria-media').text('R$ ' + resumo.valor_diaria.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                            $('#total-diarias').text('R$ ' + resumo.total_diarias.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                            $('#total-produtos').text('R$ ' + resumo.total_produtos.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                            $('#total-geral').text('R$ ' + resumo.total_geral.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            }));
                            atualizarInputRecebido(resumo.total_recebido);
                            atualizarInputFaltaLancar(resumo.falta_lancar);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Erro ao carregar resumo');
                        console.log('Status:', textStatus);
                        console.log('Erro retornado:', errorThrown);
                        console.log('Resposta completa:', jqXHR.responseText);
                    }
                });
            }

            // Função para atualizar o resumo (para reservas novas)
            function atualizarResumo() {
                const checkin = $('#data_checkin').val();
                const checkout = $('#data_checkout').val();
                const valorDiaria = parseFloat($('#valor_diaria').val().replace(/\./g, '').replace(',', '.')) || 0;
                let numDiarias = 0;
                if (checkin && checkout) {
                    const inicio = moment(checkin);
                    const fim = moment(checkout);
                    numDiarias = fim.diff(inicio, 'days');
                }

                const totalDiarias = valorDiaria * numDiarias;
                const totalProdutos = transacoes.filter(t => (t.categoria === 'produtos' || t.tipo === 'item') && t
                    .status).reduce((sum, t) => sum + parseFloat(t.valor), 0);

                const totalGeral = totalDiarias + totalProdutos;

                const totalRecebido = transacoes.filter(t => t.tipo === 'pagamento' && t.status).reduce((sum, t) =>
                    sum + parseFloat(t.valor), 0);
                const totalDescontos = transacoes.filter(t => t.tipo === 'desconto' && t.status).reduce((sum, t) =>
                    sum + parseFloat(t.valor), 0);
                const faltaLancar = totalGeral - totalRecebido - totalDescontos;

                $('#num-diarias').text(numDiarias);
                $('#diaria-media').text('R$ ' + valorDiaria.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#total-diarias').text('R$ ' + totalDiarias.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#total-produtos').text('R$ ' + totalProdutos.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#total-geral').text('R$ ' + totalGeral.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                atualizarInputRecebido(totalRecebido)
                atualizarInputFaltaLancar(faltaLancar);
            }

            // Atualizar resumo quando valor da diária mudar
            $('#valor_diaria').on('input', atualizarResumo);

            // Mostrar formulário baseado no tipo
            window.mostrarFormulario = function(tipo) {
                // Esconder todos os formulários de transação primeiro
                $('#form-transacao-pagamento').slideUp();
                $('#form-transacao-produto').slideUp();

                if (tipo === 'pagamento') {
                    $('#titulo-form-pagamento').text('Adicionar Pagamento');
                    $('#categoria_transacao').val('hospedagem');
                    $('#tipo_transacao').val('pagamento');
                    $('#form-transacao-pagamento').slideDown();
                } else if (tipo === 'produto') {
                    $('#titulo-form-produto').text('Adicionar Produto');
                    $('#form-transacao-produto').slideDown();
                    // Limpar e inicializar campos do produto
                    $('#produto_id').val('').trigger('change');
                    $('#quantidade_produto').val(1);
                    $('#total_item_produto').val('0,00');
                    produtosAdicionados = [];
                    atualizarListaItensProduto();
                    atualizarTotalProdutosAdicionados();
                }
            };

            $('#btn-cancelar-transacao').click(function() {
                $('#form-transacao-pagamento').slideUp();
                limparFormularioTransacao();
            });

            $('#btn-cancelar-produtos').click(function() {
                $('#form-transacao-produto').slideUp();
                produtosAdicionados = [];
                atualizarListaItensProduto();
                atualizarTotalProdutosAdicionados();
            });

            // Salvar transação (pagamento)
            $('#btn-salvar-transacao').click(function() {
                const descricao = $('#descricao_transacao').val();
                const valorStr = $('#valor_transacao').val();
                const formaPagamentoId = $('#forma_pagamento_transacao').val();
                const dataTransacao = $('#data_transacao').val();
                const observacoes = $('#observacoes_transacao').val();
                const categoria = $('#categoria_transacao').val();
                const tipo = $('#tipo_transacao').val();
                const restante = parseFloat($('#valor-falta-lancar').val());

                if (valorStr > restante) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Valor inválido',
                        text: 'O valor não pode ser maior do que o restante.',
                        confirmButtonText: 'Entendi'
                    });
                    return
                }

                if (!descricao || !valorStr || !formaPagamentoId || !dataTransacao) {
                    alert('Preencha todos os campos obrigatórios');
                    return;
                }

                const valor = parseFloat(valorStr.replace(/\./g, '').replace(',', '.'));

                const dados = {
                    descricao: descricao,
                    valor: valor,
                    forma_pagamento_id: formaPagamentoId,
                    data_pagamento: dataTransacao,
                    observacoes: observacoes,
                    categoria: categoria,
                    tipo: tipo,
                    reserva_id: reservaId
                };

                $.ajax({
                    url: '/transacoes',
                    method: 'POST',
                    data: dados,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            transacoes.push(response.transacao);
                            atualizarListaAtividades();
                            atualizarResumo();
                            $('#form-transacao-pagamento').slideUp();
                            limparFormularioTransacao();

                            // Verificar mudança de situação após adicionar pagamento
                            verificarMudancaSituacao();

                            Swal.fire({
                                title: 'Sucesso!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000
                            });
                        } else {
                            alert('Erro: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Erro ao salvar transação: ' + xhr.responseJSON.message);
                    }
                });
            });

            // Função para verificar mudança de situação
            function verificarMudancaSituacao() {
                const situacaoAtual = $('input[name="situacao"]:checked').val();
                if (situacaoAtual === 'pre-reserva') {
                    // Alterar automaticamente para 'reserva' se houver pagamentos
                    $('input[name="situacao"][value="reserva"]').prop('checked', true);

                    Swal.fire({
                        title: 'Situação Atualizada!',
                        text: 'A situação da reserva foi alterada para "Reserva" devido ao pagamento adicionado.',
                        icon: 'info',
                        timer: 3000
                    });
                }
            }

            function limparFormularioTransacao() {
                $('#descricao_transacao').val('');
                $('#valor_transacao').val('');
                $('#forma_pagamento_transacao').val('');
                $('#data_transacao').val('{{ date('Y-m-d') }}');
                $('#observacoes_transacao').val('');
            }

            function atualizarListaAtividades() {
                const $lista = $('#lista-atividades');

                if (transacoes.length === 0) {
                    $lista.html('<p class="text-muted text-center">Nenhuma atividade adicionada</p>');
                    return;
                }

                $lista.empty();

                transacoes.forEach(function(transacao) {
                    if (!transacao.status) return; // Pular transações inativas

                    const dataFormatada = moment(transacao.data_pagamento).format('DD/MM/YYYY');
                    const valorFormatado = 'R$ ' + parseFloat(transacao.valor).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2
                    });
                    const badgeClass = 'badge-' + transacao.categoria;
                    const formaPagamento = transacao.forma_pagamento ? transacao.forma_pagamento.descricao :
                        'N/A';

                    let html = '';
                    if (transacao.tipo === 'item') {
                        // Para itens de produto (ReservaItem)
                        html = `
                            <div class="atividade-item" data-id="${transacao.id}">
                                <div class="atividade-header">
                                    <span>${transacao.descricao} (x${transacao.quantidade})</span>
                                    <span class="text-success">${valorFormatado}</span>
                                </div>
                                <div class="atividade-details">
                                    <span class="badge ${badgeClass}">produtos</span>
                                    Item da reserva • ${dataFormatada}
                                    @if (!isset($reserva) || !in_array($reserva->situacao ?? '', ['finalizada', 'cancelado']))
                                        <button class="btn btn-sm btn-outline-danger float-right btn-remover-item" data-id="${transacao.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        `;
                    } else {
                        // Para transações normais
                        html = `
                            <div class="atividade-item" data-id="${transacao.id}">
                                <div class="atividade-header">
                                    <span>${transacao.descricao}</span>
                                    <span class="text-${transacao.tipo === 'desconto' ? 'danger' : 'success'}">${transacao.tipo === 'desconto' ? '-' : ''}${valorFormatado}</span>
                                </div>
                                <div class="atividade-details">
                                    <span class="badge ${badgeClass}">${transacao.categoria}</span>
                                    ${formaPagamento} • ${dataFormatada}
                                    @if (!isset($reserva) || !in_array($reserva->situacao ?? '', ['finalizada', 'cancelado']))
                                        <button class="btn btn-sm btn-outline-danger float-right btn-remover-transacao" data-id="${transacao.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        `;
                    }

                    $lista.append(html);
                });
            }

            // Remover transação
            $(document).on('click', '.btn-remover-transacao', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: 'Esta ação não pode ser desfeita!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, remover!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const situacao = $('input[name="situacao"]:checked').val();

                        if (situacao === 'finalizada') {
                            Swal.fire(
                                'Reserva Finalizada',
                                'Essa ação não pode ser realizada',
                                'info'
                            );
                            return;
                        }

                        $.ajax({
                            url: '/transacoes/' + id,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    transacoes = transacoes.filter(t => t.id !== id);
                                    atualizarListaAtividades();
                                    atualizarResumo();

                                    Swal.fire(
                                        'Removido!',
                                        response.message,
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'Erro!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Erro!',
                                    'Erro ao remover transação: ' + xhr.responseJSON
                                    .message,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Remover item de produto (ReservaItem)
            $(document).on('click', '.btn-remover-item', function() {
                const id = $(this).data('id');
                const itemId = id.replace('item_', ''); // Remove o prefixo 'item_'

                Swal.fire({
                    title: 'Tem certeza?',
                    text: 'Este produto será removido da reserva!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, remover!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const situacao = $('input[name="situacao"]:checked').val();

                        if (situacao === 'finalizada') {
                            Swal.fire(
                                'Reserva Finalizada',
                                'Essa ação não pode ser realizada',
                                'info'
                            );
                            return;
                        }
                        $.ajax({
                            url: '/reserva-itens/' + itemId,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    transacoes = transacoes.filter(t => t.id !== id);
                                    atualizarListaAtividades();
                                    atualizarResumo();

                                    Swal.fire(
                                        'Removido!',
                                        'Produto removido da reserva com sucesso!',
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'Erro!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Erro!',
                                    'Erro ao remover produto: ' + (xhr
                                        .responseJSON ? xhr.responseJSON.message :
                                        'Erro desconhecido'),
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Lógica para adicionar produtos
            $('#produto_id').on('change', function() {
                const valorProduto = parseFloat($('#produto_id option:selected').data('valor')) || 0;
                const quantidade = parseInt($('#quantidade_produto').val()) || 0;
                $('#total_item_produto').val((valorProduto * quantidade).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
            });

            $('#quantidade_produto').on('input', function() {
                const valorProduto = parseFloat($('#produto_id option:selected').data('valor')) || 0;
                const quantidade = parseInt($(this).val()) || 0;
                $('#total_item_produto').val((valorProduto * quantidade).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
            });

            $('#btn-adicionar-item-produto').click(function() {
                const produtoId = $('#produto_id').val();
                const produtoNome = $('#produto_id option:selected').text().split(' - R$')[0];
                const quantidade = parseInt($('#quantidade_produto').val());
                const valorUnitario = parseFloat($('#produto_id option:selected').data('valor'));
                const totalItem = valorUnitario * quantidade;

                if (!produtoId || quantidade <= 0 || isNaN(valorUnitario)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Selecione um produto e uma quantidade válida.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                produtosAdicionados.push({
                    produto_id: produtoId,
                    nome: produtoNome,
                    quantidade: quantidade,
                    valor_unitario: valorUnitario,
                    total: totalItem
                });

                atualizarListaItensProduto();
                atualizarTotalProdutosAdicionados();

                // Limpar campos após adicionar
                $('#produto_id').val('').trigger('change');
                $('#quantidade_produto').val(1);
                $('#total_item_produto').val('0,00');
            });

            function atualizarListaItensProduto() {
                const $listaItens = $('#lista-itens-produto');
                $listaItens.empty();

                if (produtosAdicionados.length === 0) {
                    $listaItens.html('<p class="text-muted">Nenhum produto adicionado ainda.</p>');
                    return;
                }

                produtosAdicionados.forEach((item, index) => {
                    const html = `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <span>${item.nome} (x${item.quantidade}) - R$ ${item.total.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
                            <button type="button" class="btn btn-danger btn-sm btn-remover-item-produto" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    $listaItens.append(html);
                });
            }

            $(document).on('click', '.btn-remover-item-produto', function() {
                const index = $(this).data('index');
                produtosAdicionados.splice(index, 1);
                atualizarListaItensProduto();
                atualizarTotalProdutosAdicionados();
            });

            function atualizarTotalProdutosAdicionados() {
                const totalGeralProdutos = produtosAdicionados.reduce((sum, item) => sum + item.total, 0);
                $('#total_produtos_adicionados').val(totalGeralProdutos.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
            }

            $('#btn-salvar-produtos').click(function() {
                if (produtosAdicionados.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nenhum Produto',
                        text: 'Adicione pelo menos um produto para salvar.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirmar adição de produtos?',
                    text: 'Os produtos serão adicionados à reserva.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, adicionar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const dadosProdutos = {
                            reserva_id: reservaId,
                            itens: produtosAdicionados,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        };

                        $.ajax({
                            url: '/reserva-itens', // Nova rota para salvar ReservaItens
                            method: 'POST',
                            data: JSON.stringify(dadosProdutos),
                            contentType: 'application/json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Adicionar os itens salvos à lista de atividades
                                    response.itens.forEach(function(item) {
                                        const atividade = {
                                            id: 'item_' + item.id,
                                            descricao: 'Produto: ' + item
                                                .produto.descricao,
                                            valor: item.produto
                                                .preco_venda * item
                                                .quantidade,
                                            categoria: 'produtos',
                                            tipo: 'item',
                                            data_pagamento: new Date()
                                                .toISOString().split('T')[
                                                    0],
                                            status: true,
                                            quantidade: item.quantidade,
                                            produto_nome: item.produto
                                                .descricao
                                        };
                                        transacoes.push(atividade);
                                    });

                                    atualizarListaAtividades();
                                    atualizarResumo();
                                    $('#form-transacao-produto').slideUp();
                                    produtosAdicionados = [];
                                    atualizarListaItensProduto();
                                    atualizarTotalProdutosAdicionados();

                                    Swal.fire({
                                        title: 'Sucesso!',
                                        text: response.message,
                                        icon: 'success',
                                        timer: 2000
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Erro!',
                                        text: response.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: 'Erro ao salvar produtos: ' + (xhr
                                        .responseJSON ? xhr.responseJSON
                                        .message : xhr.responseText),
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

            // Botões de ação da reserva
            $('#btn-finalizar').click(function(e) {
                e.preventDefault();
                const restante = parseFloat($('#valor-falta-lancar').val());
                const recebido = parseFloat($('#valor-recebido').val());
                const reservaId = $('#btn-finalizar').data('reserva-id');

                if (recebido < restante) {
                    Swal.fire({
                        title: 'Ops!',
                        text: 'Para finalizar a Reserva/Hospedagem você deve concluir o recebimento de todos os valores restantes.',
                        icon: 'error',
                        confirmButtonText: 'entendido',
                        timer: 5000
                    });
                } else {
                    $.ajax({
                        url: `/reserva/${reservaId}/finalizar`,
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({}),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Sucesso!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location
                                .reload(); // Recarregar para atualizar a interface
                                });
                            } else {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Não foi possível finalizar a reserva.',
                                icon: 'error',
                                confirmButtonText: 'Tentar novamente'
                            });
                            console.error("Erro:", error);
                        }
                    });
                }
            });

            $('#btn-cancelar-reserva').click(function(e) {
                e.preventDefault();
                const reservaId = $(this).data('reserva-id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: 'Esta ação cancelará a reserva permanentemente!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, cancelar reserva!',
                    cancelButtonText: 'Não cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/reserva/${reservaId}/cancelar`,
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            contentType: 'application/json',
                            data: JSON.stringify({}),
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Cancelado!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location
                                    .reload(); // Recarregar para atualizar a interface
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Erro!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: 'Não foi possível cancelar a reserva.',
                                    icon: 'error',
                                    confirmButtonText: 'Tentar novamente'
                                });
                                console.error("Erro:", error);
                                console.log(status)
                                console.log(xhr)
                            }
                        });
                    }
                });
            });

            $('#btn-hospedar').click(function(e) {
                e.preventDefault();
                const reservaId = $(this).data('reserva-id');

                Swal.fire({
                    title: 'Realizar Check-in?',
                    text: 'O hóspede será registrado como hospedado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, hospedar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/reserva/${reservaId}/hospedar`,
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            contentType: 'application/json',
                            data: JSON.stringify({}),
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Check-in Realizado!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location
                                    .reload(); // Recarregar para atualizar a interface
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Erro!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: 'Não foi possível realizar o check-in.',
                                    icon: 'error',
                                    confirmButtonText: 'Tentar novamente'
                                });
                                console.error("Erro:", error);
                            }
                        });
                    }
                });
            });

            // Inicializar
            atualizarResumo();

            $('#periodo').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Início',
                    toLabel: 'Fim',
                    customRangeLabel: 'Personalizado'
                },
                opens: 'center',
            }, function(start, end) {

                $('input[name="data_checkin"]').val(start.format('YYYY-MM-DD'));
                $('input[name="data_checkout"]').val(end.format('YYYY-MM-DD'));

                $('#periodo').val(start.format('DD/MM/YYYY') + ' a ' + end.format('DD/MM/YYYY'));

                // Atualizar resumo
                atualizarResumo();
            });

            let initialStartDate = $('input[name="data_checkin"]').val();
            let initialEndDate = $('input[name="data_checkout"]').val();

            if (initialStartDate && initialEndDate) {
                $('#periodo').data('daterangepicker').setStartDate(moment(initialStartDate, 'YYYY-MM-DD'));
                $('#periodo').data('daterangepicker').setEndDate(moment(initialEndDate, 'YYYY-MM-DD'));
                $('#periodo').val(moment(initialStartDate, 'YYYY-MM-DD').format('DD/MM/YYYY') + ' a ' + moment(
                    initialEndDate, 'YYYY-MM-DD').format('DD/MM/YYYY'));
                atualizarResumo();
            }

            const quartoSelecionado = $('#quarto_selecionado').val(); // Pega o quarto já selecionado (em edição)
            if (quartoSelecionado) {
                const quarto = $('#quarto')
                quarto.val(quartoSelecionado);
                quarto.prop('disabled', false);
                $('#campoQuarto').hide()
            }
            $('#periodo').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' a ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                    monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
                        'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                    ],
                    firstDay: 0
                },
                opens: 'center'
            }, function(start, end) {
                // Atualiza os inputs hidden
                $('#data_checkin').val(start.format('YYYY-MM-DD'));
                $('#data_checkout').val(end.format('YYYY-MM-DD'));

                // Faz requisição para buscar quartos disponíveis agrupados por categoria
                $.ajax({
                    url: '{{ route('quartos.disponiveis') }}',
                    method: 'GET',
                    data: {
                        checkin: start.format('DD/MM/YYYY'),
                        checkout: end.format('DD/MM/YYYY'),
                        reserva_id: reservaId
                    },
                    success: function(response) {
                        $('#campoQuarto').show();
                        let $select = $('#quarto');
                        $select.prop('disabled', false);
                        $select.empty().append('<option value="">Selecione um quarto</option>');

                        // Adicionar quartos agrupados por categoria
                        response.forEach(function(grupo) {
                            let $optgroup = $('<optgroup label="' + grupo.categoria +
                                '">');

                            grupo.quartos.forEach(function(quarto) {
                                $optgroup.append('<option value="' + quarto.id +
                                    '">' + quarto.nome + '</option>');
                            });

                            $select.append($optgroup);
                        });

                        if (quartoSelecionado) {
                            $select.val(quartoSelecionado);
                        }

                        // Reinicializar o select2 para aplicar os novos optgroups
                        $select.select2({
                            placeholder: 'Selecione um quarto...',
                            allowClear: true,
                            width: '100%'
                        });
                    },
                    error: function() {
                        alert('Erro ao buscar quartos disponíveis.');
                    }
                });
            });

            let checkin = $('#data_checkin').val();
            let checkout = $('#data_checkout').val();

            if (checkin && checkout) {
                let start = moment(checkin, 'YYYY-MM-DD');
                let end = moment(checkout, 'YYYY-MM-DD');
                $('#periodo').data('daterangepicker').setStartDate(start);
                $('#periodo').data('daterangepicker').setEndDate(end);
                $('#periodo').val(start.format('DD/MM/YYYY') + ' a ' + end.format('DD/MM/YYYY'));
            }
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Sucesso!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                title: 'Erro!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

@stop

@extends('adminlte::page')

@section('title', isset($reserva) ? 'Editar Reserva' : 'Cadastrar Reserva')

@section('content_header')
    <h1>{{ isset($reserva) ? 'Editar Reserva' : 'Cadastrar Reserva' }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Coluna Principal (Formulário) -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header green bg-primary text-white">
                        <h3 class="card-title">
                            {{ isset($reserva) ? 'Editar informações da Reserva' : 'Preencha os dados da nova Reserva' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="createReservaForm"
                            action="{{ isset($reserva) ? route('reserva.update', $reserva->id) : route('reserva.store') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @if (isset($reserva))
                                @method('PUT')
                            @endif

                            <div class="form-group row" id="campoSituacao">
                                <label class="col-md-3 label-control"><strong>* Situação</strong></label>
                                <div class="situacao-options">

                                    <label class="radio-option">
                                        <input type="radio" name="situacao" value="pre-reserva"
                                            @checked(old('situacao', $reserva->situacao ?? '') === 'pre-reserva') required>
                                        <span class="badge badge-warning">pré-reservar</span>
                                    </label>

                                    <label class="radio-option">
                                        <input type="radio" name="situacao" value="reserva" @checked(old('situacao', $reserva->situacao ?? '') === 'reserva')>
                                        <span class="badge badge-primary">reservar</span>
                                    </label>

                                    <label class="radio-option">
                                        <input type="radio" name="situacao" value="hospedado"
                                            @checked(old('situacao', $reserva->situacao ?? '') === 'hospedado')>
                                        <span class="badge badge-danger">hospedar</span>
                                    </label>

                                    <label class="radio-option">
                                        <input type="radio" name="situacao" value="bloqueado"
                                            @checked(old('situacao', $reserva->situacao ?? '') === 'bloqueado')>
                                        <span class="badge badge-dark">bloquear datas</span>
                                    </label>
                                </div>
                            </div>



                            <div>
                                <hr>
                            </div>

                            <div class="form-group row">
                                <label for="hospede_id" class="col-md-3 label-control">* Hóspede</label>
                                <div class="col-sm-4">
                                    @php
                                        $hospedeSelecionado = old('hospede_id', $reserva->hospede_id ?? '');
                                    @endphp

                                    @if ($hospedeSelecionado)
                                        <select class="form-control select2" name="hospede_id_disabled" id="hospede_id"
                                            disabled>
                                            <option value="">Selecione um hóspede</option>
                                            @foreach ($hospedes as $hospede)
                                                @if ($hospede->nome !== 'Bloqueado')
                                                    <option value="{{ $hospede->id }}"
                                                        {{ $hospedeSelecionado == $hospede->id ? 'selected' : '' }}>
                                                        {{ $hospede->nome }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="hospede_id" value="{{ $hospedeSelecionado }}">
                                    @else
                                        <select class="form-control select2" name="hospede_id" id="hospede_id">
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
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
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
                                    <div><input class="form-control" type="text" name="valor_diaria" id="valor_diaria"
                                            value="{{ old('valor_diaria', $reserva->valor_diaria ?? '') }}">
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
                                    <textarea class="form-control" name="observacao" rows="3">{{ old('observacao', $reserva->observacao ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="card-footer">
                                @if (isset($reserva))
                                    <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#deleteReservaModal{{ $reserva->id }}">
                                        Excluir 🗑️
                                    </button>
                                    @include('reserva.modals._delete')
                                @endif
                                <a href="{{ route('reserva.index') }}" class="btn btn-secondary">Voltar</a>
                                <button type="submit"
                                    class="btn btn-primary">{{ isset($reserva) ? 'Atualizar Reserva' : 'Adicionar Reserva' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral (Resumo e Pagamentos) -->
            @if (isset($reserva))
                <div class="col-lg-4">
                    <!-- Card de Resumo -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">FALTA LANÇAR</h5>
                            <h2 class="text-danger mb-0" id="falta-lancar">R$ 0,00</h2>
                        </div>
                    </div>

                    <!-- Card de Resumo Detalhado -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">RESUMO</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Nº diárias</span>
                                <span id="num-diarias">1</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Diária Média</span>
                                <span id="diaria-media">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Diárias</span>
                                <span id="total-diarias">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Produtos</span>
                                <span id="total-produtos">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Serviços</span>
                                <span id="total-servicos">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Alimentos</span>
                                <span id="total-alimentos">R$ 0,00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Total</strong>
                                <strong id="total-geral">R$ 0,00</strong>
                            </div>
                            <div class="d-flex justify-content-between text-success">
                                <span>Recebido</span>
                                <span id="total-recebido">R$ 0,00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Atividades na Reserva -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">ATIVIDADES NA RESERVA
                            </h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#"
                                        onclick="mostrarFormulario('pagamento')">Adicionar Pagamento</a>
                                    <a class="dropdown-item" href="#"
                                        onclick="mostrarFormulario('produto')">Adicionar Produto</a>
                                    <a class="dropdown-item" href="#"
                                        onclick="mostrarFormulario('servico')">Adicionar Serviço</a>
                                    <a class="dropdown-item" href="#"
                                        onclick="mostrarFormulario('alimento')">Adicionar Alimento</a>
                                    <a class="dropdown-item" href="#"
                                        onclick="mostrarFormulario('desconto')">Adicionar Desconto</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="lista-atividades">
                                <p class="text-muted text-center">Nenhuma atividade adicionada</p>
                            </div>

                            <!-- Formulário para adicionar transação -->
                            <div id="form-transacao" style="display: none;">
                                <hr>
                                <h6 id="titulo-form">Adicionar Transação</h6>
                                <div class="form-group">
                                    <label for="descricao_transacao">Descrição</label>
                                    <input type="text" class="form-control" id="descricao_transacao"
                                        placeholder="Ex: Pagamento da diária">
                                </div>
                                <div class="form-group">
                                    <label for="valor_transacao">Valor</label>
                                    <input type="text" class="form-control" id="valor_transacao" placeholder="0,00">
                                </div>
                                <div class="form-group">
                                    <label for="forma_pagamento_transacao">Forma de Pagamento</label>
                                    <select class="form-control" id="forma_pagamento_transacao">
                                        <option value="">Selecione...</option>
                                        @if (isset($formasPagamento))
                                            @foreach ($formasPagamento as $forma)
                                                <option value="{{ $forma->id }}">{{ $forma->descricao }}</option>
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
                                <input type="hidden" id="categoria_transacao" value="">
                                <input type="hidden" id="tipo_transacao" value="">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm"
                                        id="btn-salvar-transacao">Salvar</button>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        id="btn-cancelar-transacao">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                        <a href="{{ route('hospede.create') }}">Cadastro Completo</a>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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

        .radio-option input[type="radio"] {
            cursor: pointer;
        }

        input[type="number"] {
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

        #form-transacao {
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

        .badge-servicos {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-alimentos {
            background-color: #fd7e14;
            color: #fff;
        }

        .badge-desconto {
            background-color: #dc3545;
            color: #fff;
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/endereco.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "selecione...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#telefone').mask('(00) 00000-0000');
            $('#valor_diaria').mask('#.##0,00', {
                reverse: true
            });
            $('#valor_transacao').mask('#.##0,00', {
                reverse: true
            });
        })
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalHospede = document.getElementById('modalHospede');
            modalHospede.addEventListener('hidden.bs.modal', function() {
                location.reload();
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    texto: "<strong>Atividades na Reserva:</strong> Adicione pagamentos, produtos, serviços e alimentos à reserva. O resumo é atualizado automaticamente."
                },
            ];

            let dicaAtual = 0;
            const textoDica = document.getElementById('textoDica');
            const indicadores = document.getElementById('indicadores');
            const conteudoDicas = document.getElementById('conteudoDicas');
            const btnToggle = document.getElementById('btnToggleDicas');
            const iconeToggle = document.getElementById('iconeToggleDicas');

            dicas.forEach((_, index) => {
                const span = document.createElement('span');
                span.classList.add('indicador');
                span.style.width = '20px';
                span.style.height = '4px';
                span.style.margin = '0 3px';
                span.style.borderRadius = '2px';
                span.style.backgroundColor = index === 0 ? '#333' : '#e0e0e0';
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
                    el.style.backgroundColor = idx === dicaAtual ? '#333' : '#e0e0e0';
                });
            }

            trocarDica();
            setInterval(trocarDica, 8000);

            let expandido = true;

            btnToggle.addEventListener('click', function() {
                expandido = !expandido;

                if (expandido) {
                    conteudoDicas.classList.remove('collapsed');
                    iconeToggle.classList = 'fas fa-minus';
                } else {
                    conteudoDicas.classList.add('collapsed');
                    iconeToggle.classList = 'fas fa-plus';
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const hospedeBloqueadoId = "{{ $hospedeBloqueado->id ?? '' }}";

            function atualizarCampos() {
                const situacao = $('input[name="situacao"]:checked').val();

                if (situacao === 'bloqueado') {
                    $('.form-group').each(function() {
                        const id = $(this).attr('id');
                        if (['campoPeriodo', 'campoQuarto', 'campoObservacoes', 'campoSituacao'].includes(
                                id)) {
                            $(this).slideDown(200);
                        } else {
                            $(this).slideUp(200);
                        }
                    });

                    $('#hospede_id').val(hospedeBloqueadoId).prop('readonly', true);

                    $('#valor_diaria').val(0);
                } else {
                    $('.form-group').slideDown(200);
                    $('#hospede_id').prop('disabled', false).val('');
                    $('#valor_diaria').val('');
                }
            }

            $('input[name="situacao"]').on('change', atualizarCampos);

            atualizarCampos();
        });
    </script>

    <script>
        $(document).ready(function() {
            const reservaId = $('#reserva_id').val(); // Pega o ID da reserva se estiver editando
            const quartoSelecionado = $('#quarto_selecionado').val(); // Pega o quarto já selecionado (em edição)

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
                            placeholder: "Selecione um quarto...",
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

    <!-- Scripts para funcionalidade de transações -->
    <script>
        $(document).ready(function() {
            let transacoes = [];
            const reservaId = $('#reserva_id').val();

            // Carregar transações existentes se estiver editando
            if (reservaId) {
                carregarTransacoes();
                carregarResumo();
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
                            $('#total-servicos').text('R$ ' + resumo.total_servicos.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                            $('#total-alimentos').text('R$ ' + resumo.total_alimentos.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                            $('#total-geral').text('R$ ' + resumo.total_geral.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            }));
                            $('#total-recebido').text('R$ ' + resumo.total_recebido.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                            $('#falta-lancar').text('R$ ' + resumo.falta_lancar.toLocaleString(
                                'pt-BR', {
                                    minimumFractionDigits: 2
                                }));
                        }
                    },
                    error: function() {
                        console.log('Erro ao carregar resumo');
                    }
                });
            }
            // Função para atualizar o resumo (para reservas novas)
            function atualizarResumo() {

                // if (reservaId) {
                //     carregarResumo();
                //     return;
                // }

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
                const totalProdutos = transacoes.filter(t => t.categoria === 'produtos' && t.status).reduce((sum,
                    t) => sum + parseFloat(t.valor), 0);
                const totalServicos = transacoes.filter(t => t.categoria === 'servicos' && t.status).reduce((sum,
                    t) => sum + parseFloat(t.valor), 0);
                const totalAlimentos = transacoes.filter(t => t.categoria === 'alimentos' && t.status).reduce((sum,
                    t) => sum + parseFloat(t.valor), 0);
                const totalGeral = totalDiarias + totalProdutos + totalServicos + totalAlimentos;

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
                $('#total-servicos').text('R$ ' + totalServicos.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#total-alimentos').text('R$ ' + totalAlimentos.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#total-geral').text('R$ ' + totalGeral.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#total-recebido').text('R$ ' + totalRecebido.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $('#falta-lancar').text('R$ ' + faltaLancar.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
            }
            // Atualizar resumo quando valor da diária mudar
            $('#valor_diaria').on('input', atualizarResumo);

            // Mostrar formulário baseado no tipo
            window.mostrarFormulario = function(tipo) {
                let titulo, categoria, tipoTransacao;

                switch (tipo) {
                    case 'pagamento':
                        titulo = 'Adicionar Pagamento';
                        categoria = 'hospedagem';
                        tipoTransacao = 'pagamento';
                        break;
                    case 'produto':
                        titulo = 'Adicionar Produto';
                        categoria = 'produtos';
                        tipoTransacao = 'pagamento';
                        break;
                    case 'servico':
                        titulo = 'Adicionar Serviço';
                        categoria = 'servicos';
                        tipoTransacao = 'pagamento';
                        break;
                    case 'alimento':
                        titulo = 'Adicionar Alimento';
                        categoria = 'alimentos';
                        tipoTransacao = 'pagamento';
                        break;
                    case 'desconto':
                        titulo = 'Adicionar Desconto';
                        categoria = 'hospedagem';
                        tipoTransacao = 'desconto';
                        break;
                }

                $('#titulo-form').text(titulo);
                $('#categoria_transacao').val(categoria);
                $('#tipo_transacao').val(tipoTransacao);
                $('#form-transacao').slideDown();
            };

            $('#btn-cancelar-transacao').click(function() {
                $('#form-transacao').slideUp();
                limparFormularioTransacao();
            });

            // Salvar transação
            $('#btn-salvar-transacao').click(function() {
                if (!reservaId) {
                    alert('Salve a reserva primeiro antes de adicionar transações.');
                    return;
                }

                const descricao = $('#descricao_transacao').val();
                const valorStr = $('#valor_transacao').val();
                const formaPagamentoId = $('#forma_pagamento_transacao').val();
                const dataTransacao = $('#data_transacao').val();
                const observacoes = $('#observacoes_transacao').val();
                const categoria = $('#categoria_transacao').val();
                const tipo = $('#tipo_transacao').val();

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
                            $('#form-transacao').slideUp();
                            limparFormularioTransacao();

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

                    const html = `
                <div class="atividade-item" data-id="${transacao.id}">
                    <div class="atividade-header">
                        <span>${transacao.descricao}</span>
                        <span class="text-${transacao.tipo === 'desconto' ? 'danger' : 'success'}">${transacao.tipo === 'desconto' ? '-' : ''}${valorFormatado}</span>
                    </div>
                    <div class="atividade-details">
                        <span class="badge ${badgeClass}">${transacao.categoria}</span>
                        ${formaPagamento} • ${dataFormatada}
                        <button class="btn btn-sm btn-outline-danger float-right btn-remover-transacao" data-id="${transacao.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

                    $lista.append(html);
                });
            }

            // Remover transação
            $(document).on('click', '.btn-remover-transacao', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Esta ação não pode ser desfeita!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, remover!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
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
                                }
                            },
                            error: function(xhr) {
                                alert('Erro ao remover transação: ' + xhr.responseJSON
                                    .message);
                            }
                        });
                    }
                });
            });

            // Inicializar resumo
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
                console.log('Callback do daterangepicker chamado');

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
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                title: 'Erro!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

@stop

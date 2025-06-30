@extends('adminlte::page')

@section('title', isset($aluguel) ? 'Atualizar Aluguel' : 'Novo Aluguel')

@section('content_header')
    <h5>{{ isset($aluguel) ? 'Atualizar Aluguel' : 'Novo Aluguel' }}</h5>
    <hr>
@endsection

@section('content')
    <form action="{{ isset($aluguel) ? route('aluguel.update', $aluguel->id) : route('aluguel.store') }}" method="POST"
        id="aluguelform">
        @csrf
        @if (isset($aluguel))
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body mt-3">
                <ul class="nav nav-tabs" id="aluguelTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active editlink" id="info-tab" data-toggle="tab" href="#info"
                            role="tab">Reserva</a>
                    </li>
                    <li class="nav-item" id="buffetAba" style="display: none">
                        <a class="nav-link editlink" id="buffet-tab" data-toggle="tab" href="#tab-buffet"
                            role="tab">Buffet</a>
                    </li>
                    <li class="nav-item" id="adicionalAba" >
                        <a class="nav-link editlink" id="buffet-tab" data-toggle="tab" href="#tab-adicional"
                            role="tab">Mobília</a>
                    </li>
                    <li class="nav-item" id="pagamentoTab">
                        <a class="nav-link editlink" id="buffet-tab" data-toggle="tab" href="#tab-pagamento"
                            role="tab">Pagamento</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="aluguelTabsContent">

                    <div class="tab-pane fade show active" id="info" role="tabpanel">


                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" id="btnProximo" class="btn btn-primary w-25">Próximo</button>
                        </div>

                        {{-- Busca do cliente --}}
                        @php
                            $clienteSelecionadoId = old('cliente_id', $aluguel->cliente_id ?? '');
                            $clienteSelecionadoNome = '';

                            if ($clienteSelecionadoId) {
                                // Se vier via old (ex: após validação), você pode salvar o nome no request também, ou pegar do relacionamento
                                $clienteSelecionadoNome =
                                    old('cliente_nome') ?? optional($aluguel->cliente)->nome_razao_social;
                            }
                        @endphp

                        <div class="form-group row">
                            <label for="cliente_id" class="col-md-3 label-control">* Cliente</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="cliente_id" id="cliente_id" style="width: 100%;">
                                    @if ($clienteSelecionadoId && $clienteSelecionadoNome)
                                        <option value="{{ $clienteSelecionadoId }}" selected>{{ $clienteSelecionadoNome }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modalCadastrarCliente">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        </div>




                        <div class="form-group row">
                            <label class="col-md-3 label-control form-lab d-block">* Buffet?</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="ativo" value="0">
                                <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo"
                                    value="1" {{ old('ativo', isset($aluguel->cardapio_id) ? 1 : 0) ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                                    {{ old('ativo', isset($aluguel->cardapio_id) ? 1 : 0) ? 'Sim' : 'Não' }}

                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tipo_evento" class="form-label col-md-3 label-control">* Tipo de Evento:</label>
                            <div class="col-md-3">
                                <select name="tipo" id="tipo_evento" class="form-control" required>
                                    <option value="" disabled {{ old('tipo', $aluguel->tipo ?? '') == '' ? 'selected' : '' }}>Selecione o tipo</option>
                                    <option value="casamento" {{ old('tipo', $aluguel->tipo ?? '') == 'casamento' ? 'selected' : '' }}>Casamento</option>
                                    <option value="aniversario" {{ old('tipo', $aluguel->tipo ?? '') == 'aniversario' ? 'selected' : '' }}>Aniversário</option>
                                    <option value="batizado" {{ old('tipo', $aluguel->tipo ?? '') == 'batizado' ? 'selected' : '' }}>Batizado</option>
                                    <option value="confraternizacao" {{ old('tipo', $aluguel->tipo ?? '') == 'confraternizacao' ? 'selected' : '' }}>Confraternização</option>
                                </select>
                            </div>
                        </div>





                        {{-- Aba 1: Informações da Reserva --}}
                        <div class="alert alert-secondary">
                            <strong>DICA:</strong> Para selecionar a data da reserva/aluguel, basta clicar na data referente
                            ao espaço desejado. <br>
                            <strong>ATENÇÃO:</strong> <u>Ao selecionar a capela, escolher o tipo <strong>Casamento</strong> ou <strong>Batizado</strong>! <br></u>
                            <em>Para selecionar apenas 1 dia, basta clicar na data escolhida 2 vezes.</em>
                        </div>
                        <hr>
                        {{-- ====================================================== --}}
                        {{-- INÍCIO: Mapa de Reservas Integrado --}}
                        {{-- ====================================================== --}}
                        <div class="card card-success card-outline mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Selecionar Período e Espaço</h3>
                            </div>
                            <div class="card-body">
                                <!-- Filtros de Data -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="map_start_date">Data Início:</label>
                                        <input type="date" id="map_start_date" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="map_end_date">Data Fim:</label>
                                        <input type="date" id="map_end_date" class="form-control">
                                    </div>
                                    <div class="col-md-4 align-self-end">
                                        <button type="button" id="filter_button" class="btn btn-primary">Atualizar
                                            Mapa</button>
                                    </div>
                                </div>

                                <!-- Mapa de Reservas -->
                                <div id="reservation_map_container" class="table-responsive">
                                    <p>Carregando mapa...</p>
                                </div>

                                <div id="selection_feedback" class="mt-2 text-success font-weight-bold"></div>

                                <input type="hidden" id="data_inicio" name="data_inicio"
                                    value="{{ old('data_inicio', $aluguel->data_inicio ?? '') }}">
                                <input type="hidden" id="data_fim" name="data_fim"
                                    value="{{ old('data_fim', $aluguel->data_fim ?? '') }}">

                                <input type="hidden" id="espaco_id_hidden" name="espaco_id"
                                    value="{{ old('espaco_id', $aluguel->espaco_id ?? '') }}">


                                <input type="hidden" id="data_inicio_atual" value="{{ $aluguel->data_inicio ?? '' }}">
                                <input type="hidden" id="data_fim_atual" value="{{ $aluguel->data_fim ?? '' }}">
                                <input type="hidden" id="espaco_id_atual" value="{{ $aluguel->espaco_id ?? '' }}">
                                <input type="hidden" id="reserva_id_atual" value="{{ $aluguel->id ?? '' }}">


                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <label for="valor_total" class="mb-0 mr-2">Valor Total:</label>
                                    <input type="text" id="valor_total" name="valor_total"
                                        style="border: none; background: transparent; outline: none;" readonly>
                                </div>




                            </div>
                            <!-- /.card-body -->
                        </div>
                        {{-- ====================================================== --}}
                        {{-- FIM: Mapa de Reservas Integrado --}}
                        {{-- ====================================================== --}}
                        <hr>
                        <div class="form-group row">
                            <label for="observacoes" class="col-md-3 label-control">Observações extras:</label>
                            <div class="col-md-6">
                                <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $tarifa->observacoes ?? '') }}</textarea>
                            </div>
                        </div>

                    </div>


                    {{-- Aba 2: Buffet --}}
                    <div class="tab-pane fade" id="tab-buffet">
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" id="btnProximoBuffet" class="btn btn-primary w-25">Próximo</button>
                        </div>
                        <div class="form-group row">
                            <label for="numero_pessoas_buffet" class="col-md-3 label-control">* Número de Pessoas:</label>
                            <div class="col-md-3">
                                <input type="number" name="numero_pessoas_buffet" id="numero_pessoas_buffet"
                                    class="form-control"
                                    value="{{ old('numero_pessoas_buffet', $aluguel->numero_pessoas_buffet ?? '') }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cardapio_id" class="col-md-3 label-control">* Cardápio:</label>
                            <div class="col-md-6">
                                <select name="cardapio_id" id="cardapio_id" class="form-control">
                                    <option value="">Selecione um cardápio</option>
                                    @foreach ($cardapios as $cardapio)
                                        <option value="{{ $cardapio->id }}"
                                            {{ old('cardapio_id', $aluguel->cardapio_id ?? '') == $cardapio->id ? 'selected' : '' }}>
                                            {{ $cardapio->NomeCardapio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Container principal do buffet com responsividade melhorada -->
                        <div class="row">
                            <div class="col-12">
                                <div id="categoriasContainer" class="buffet-container">
                                    <!-- As categorias e itens serão carregados aqui via JavaScript -->
                                </div>

                                <hr>

                                <div id="opcoesContainer" class="buffet-container">
                                    <!-- As opções de jantar e itens serão carregados aqui via JavaScript -->
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control">Total Buffet Estimado:</label>
                            <div class="col-md-3">
                                <input type="text" id="total_buffet" class="form-control" readonly>
                            </div>
                        </div>

                        <!-- Campos hidden para armazenar as escolhas do buffet -->
                        <input type="hidden" id="buffet_categorias_escolhidas" name="buffet_categorias_escolhidas"
                            value="">
                        <input type="hidden" id="buffet_opcao_escolhida" name="buffet_opcao_escolhida" value="">
                    </div>

                    {{-- Aba 3: Adicionais --}}
                    {{-- @php dd($adicionais) @endphp --}}
                    <div class="tab-pane fade" id="tab-adicional">
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" id="btnProximoAdicional" class="btn btn-primary w-25">Próximo</button>
                        </div>
                        @php
                            $adicionaisSelecionadosArray = collect($adicionaisSelecionados)->keyBy('adicional_id');
                        @endphp

                       <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <strong>Adicionais</strong>
                            </div>
                            <div class="card-body">
                                @foreach ($adicionais as $adicional)
                                    @php
                                        $selecionado = $adicionaisSelecionadosArray->get($adicional->id);
                                        $quantidade = $selecionado->quantidade ?? 0;
                                        $observacao = $selecionado->observacao ?? '';
                                        $valorTotal = $selecionado->valor_total ?? 0;
                                    @endphp
                                    <div class="row mb-3 align-items-center border-bottom pb-2">
                                        <div class="col-md-4">
                                            <strong>{{ $adicional->descricao }}</strong><br>
                                            <small>Valor unitário: R$ {{ number_format($adicional->valor, 2, ',', '.') }}</small>
                                        </div>

                                        <div class="col-md-2">
                                            <label>Quantidade:</label>
                                            <input type="number" min="0" 
                                                class="form-control adicional-quantidade"
                                                data-valor="{{ $adicional->valor }}"
                                                name="adicionais[{{ $adicional->id }}][quantidade]"
                                                value="{{ $quantidade }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label>Observação:</label>
                                            <input type="text" class="form-control" 
                                                name="adicionais[{{ $adicional->id }}][observacao]"
                                                value="{{ $observacao }}">
                                        </div>

                                        <div class="col-md-2">
                                            <label>Total:</label>
                                            <input type="text" readonly class="form-control adicional-total"
                                                value="{{ $quantidade > 0 ? 'R$ '.number_format($valorTotal, 2, ',', '.') : '' }}">
                                        </div>
                                    </div>
                                @endforeach


                                <div class="form-group row">
                                    <label class="col-md-3 label-control">Total dos Adicionais Estimado:</label>
                                    <div class="col-md-3">
                                        <input type="text" id="totalAdicionais" class="form-control" readonly>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>


                    {{-- Aba 4: Pagamento --}}
                    <div class="tab-pane fade" id="tab-pagamento">
                        <!-- Resumo Financeiro -->
                        <div class="card card-info card-outline mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Resumo Financeiro</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Valor do Aluguel:</label>
                                            <input type="text" id="valor_aluguel_display" class="form-control"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Valor do Buffet:</label>
                                            <input type="text" id="valor_buffet_display" class="form-control"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Valor da Mobília:</label>
                                            <input type="text" id="valor_adicional_display" class="form-control"
                                                readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Subtotal:</label>
                                            <input type="text" id="subtotal_display" class="form-control" readonly>
                                            <input type="hidden" id="subtotal" name="subtotal">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Acréscimo (R$):</label>
                                            <input type="number" id="acrescimo" name="acrescimo" class="form-control"
                                                step="0.01" min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label id="labelDesconto">Desconto (R$):</label>
                                            {{-- <input type="number" id="desconto" name="desconto" class="form-control" step="0.01" min="0" value="0"> --}}
                                            <div class="input-group">
                                                <input type="number" id="desconto" class="form-control"
                                                    placeholder="Desconto">
                                                <button type="button" id="toggleTipoDesconto"
                                                    class="btn btn-outline-secondary">%</button>

                                            </div>
                                            <span id="previewDesconto" style="margin-left: 8px; color: gray;"></span>

                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Valor Total:</strong></label>
                                            <input type="text" id="valor_total_display"
                                                class="form-control font-weight-bold" readonly>
                                            <input type="hidden" id="total" name="total">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Valor Restante:</strong></label>
                                            <input type="text" id="valor_restante_display"
                                                class="form-control font-weight-bold text-danger" readonly>
                                            <input type="hidden" id="valor_restante">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formas de Pagamento -->
                        <div class="card card-success card-outline mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Formas de Pagamento</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Forma de Pagamento:</label>
                                            <select id="forma_pagamento_select" class="form-control">
                                                <option value="">Selecione uma forma</option>
                                                @foreach ($formasPagamento as $forma)
                                                    <option value="{{ $forma->id }}">{{ $forma->descricao }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Valor (R$):</label>
                                            <input type="number" id="valor_pagamento" class="form-control"
                                                step="0.01" min="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-4 align-self-end">
                                        <div class="form-group">
                                            <button type="button" id="adicionar_pagamento" class="btn btn-success">
                                                <i class="fas fa-plus"></i> Adicionar
                                            </button>
                                            <button type="button" id="pagar_restante" class="btn btn-info">
                                                <i class="fas fa-money-bill"></i> Pagar Restante
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista de Pagamentos Adicionados -->
                                <div class="mt-4">
                                    <h5>Pagamentos Adicionados:</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="tabela_pagamentos">
                                            <thead>
                                                <tr>
                                                    <th>Forma de Pagamento</th>
                                                    <th>Valor</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody id="lista_pagamentos">
                                                <!-- Pagamentos serão adicionados aqui via JavaScript -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-info">
                                                    <th>Total Pago:</th>
                                                    <th id="total_pago_display">R$ 0,00</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <!-- Campo hidden para armazenar os pagamentos -->
                                <input type="hidden" id="pagamentos_json" name="pagamentos_json" value="">
                            </div>
                        </div>

                        <!-- Alertas de Validação -->
                        <div id="alerta_pagamento" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="mensagem_alerta"></span>
                        </div>

                        <div class="card-footer text-end">
                            <button type="submit" id="btn-submit-aluguel"
                                class="w-100 btn new btn-{{ isset($aluguel) ? 'info' : 'success' }}">
                                {{ isset($aluguel) ? 'Atualizar Aluguel' : 'Criar Aluguel' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Infos finais --}}
            @if (isset($aluguel))
                <p class="text-muted mt-3">
                    Criado em: {{ $aluguel->created_at->format('d/m/Y H:i:s') }}<br>
                    Alterado em: {{ $aluguel->updated_at->format('d/m/Y H:i:s') }}<br>
                    Alterado por: {{ Auth::user()->name }}
                </p>
            @endif



        </div>
    </form>

    <!-- Modal de Cadastro de Cliente -->
    <div class="modal fade" id="modalCadastrarCliente" tabindex="-1" role="dialog"
        aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <form method="POST" action="{{ route('cliente.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalClienteLabel">Cadastrar Cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-md-4 label-control" for="nomeRazaoSocial">* Nome/Razão Social:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="text" name="nome_razao_social"
                                        id="nomeRazaoSocial" autocomplete="off"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 label-control" for="apelidoNomeFantasia">* Apelido/Nome
                                fantasia:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="text" name="apelido_nome_fantasia"
                                        id="apelidoNomeFantasia"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 label-control" for="dataNascimento">* Data de Nascimento:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="date" name="data_nascimento"
                                        id="dataNascimento"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 label-control" for="cpfCnpj">* Documentos:</label>
                            <div class=" row col-md-8">
                                <div class="col-md-4">
                                    <label for="cpfCnpj">CPF/CNPJ</label>
                                    <input type="text" class="form-control" id="cpfCnpj" name="cpf_cnpj">
                                </div>
                                <div class="col-md-8">
                                    <label for="rgIe">RG/Inscrição Estadual:</label>
                                    <input type="text" class="form-control" id="rgIe" name="rg_ie">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 label-control" for="tipo">* Tipo:</label>
                            <div class="col-md-4">
                                <select class="form-control select2" id="tipo" name="tipo" required>
                                    <option value="PF">Pessoa Física</option>
                                    <option value="PJ">Pessoa Jurídica</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <a href="{{ route('cliente.create') }}">Cadastro Completo</a>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if (isset($aluguel) && $aluguel->pagamentos)
        <script>
            window.pagamentosExistentes = @json($aluguel->pagamentos);
        </script>
    @endif

@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/reservation_map.js') }}"></script>

    <script>
        // Sistema de Pagamento
        document.addEventListener('DOMContentLoaded', function() {
            // Variáveis globais para o sistema de pagamento
            let pagamentosAdicionados = [];
            let valorTotalCalculado = 0;
            let valorRestante = 0;

            // Elementos do DOM

            const valorAluguelDisplay = document.getElementById('valor_aluguel_display');
            const valorBuffetDisplay = document.getElementById('valor_buffet_display');
            const valorAdicionalDisplay = document.getElementById('valor_adicional_display');
            const subtotalDisplay = document.getElementById('subtotal_display');
            const subtotalHidden = document.getElementById('subtotal');
            const acrescimoInput = document.getElementById('acrescimo');
            const descontoInput = document.getElementById('desconto');
            const valorTotalDisplay = document.getElementById('valor_total_display');
            const totalHidden = document.getElementById('total');
            const valorRestanteDisplay = document.getElementById('valor_restante_display');
            const valorRestanteHidden = document.getElementById('valor_restante');

            const formaPagamentoSelect = document.getElementById('forma_pagamento_select');
            const valorPagamentoInput = document.getElementById('valor_pagamento');
            const adicionarPagamentoBtn = document.getElementById('adicionar_pagamento');
            const pagarRestanteBtn = document.getElementById('pagar_restante');
            const listaPagamentos = document.getElementById('lista_pagamentos');
            const totalPagoDisplay = document.getElementById('total_pago_display');
            const pagamentosJsonInput = document.getElementById('pagamentos_json');
            const alertaPagamento = document.getElementById('alerta_pagamento');
            const mensagemAlerta = document.getElementById('mensagem_alerta');

            // Se tiver pagamentos existentes vindos do Laravel
            if (window.pagamentosExistentes) {
                window.pagamentosExistentes.forEach(function(pag) {
                    pagamentosAdicionados.push({
                        id: pag
                        .id, // Importante manter o ID real para permitir edição/exclusão depois
                        forma_pagamento_id: pag.forma_pagamento_id,
                        forma_pagamento_nome: pag.forma_pagamento ? pag.forma_pagamento.descricao :
                            '',
                        valor: parseFloat(pag.valor)
                    });
                });

                atualizarListaPagamentos();
                calcularValorRestante();
            }
            // Função para formatar valor em moeda brasileira
            function formatarMoeda(valor) {
                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(valor);
            }

            // Validação antes do envio do formulário


            // Função para calcular subtotal
            function calcularSubtotal() {
                const valorAluguel = parseFloat(document.getElementById('valor_total').value.replace(/[^\d,]/g, '')
                    .replace(',', '.')) || 0;
                const valorBuffet = parseFloat(document.getElementById('total_buffet').value.replace(/[^\d,]/g, '')
                    .replace(',', '.')) || 0;
                const valorAdicional = parseFloat(document.getElementById('totalAdicionais').value.replace(/[^\d,]/g, '')
                    .replace(',', '.')) || 0;
            
                valorAluguelDisplay.value = formatarMoeda(valorAluguel);
                valorBuffetDisplay.value = formatarMoeda(valorBuffet);
                valorAdicionalDisplay.value = formatarMoeda(valorAdicional);

                const subtotal = valorAluguel + valorBuffet + valorAdicional;
                subtotalDisplay.value = formatarMoeda(subtotal);
                subtotalHidden.value = subtotal.toFixed(2);

                calcularValorTotal();
            }

            // Função para calcular valor total
            function calcularValorTotal() {
                const subtotal = parseFloat(subtotalHidden.value) || 0;
                let acrescimo = parseFloat(acrescimoInput.value) || 0;
                let desconto = parseFloat(descontoInput.value.replace(',', '.')) || 0;

                // Impedir valores negativos no acrescimo
                if (acrescimo < 0) {
                    acrescimo = 0;
                    acrescimoInput.value = '0';
                    mostrarAlerta('O acréscimo não pode ser negativo.', 'warning');
                }

                // Se estiver no modo percentual, converte
                if (descontoEmPercentual) {
                    if (desconto < 0) {
                        desconto = 0;
                        descontoInput.value = '0';
                        mostrarAlerta('O desconto percentual não pode ser negativo.', 'warning');
                    }

                    if (desconto > 100) {
                        desconto = 100;
                        descontoInput.value = '100';
                        mostrarAlerta('O desconto percentual não pode passar de 100%.', 'warning');
                    }

                    desconto = (subtotal * desconto) / 100;
                } else {
                    // Modo valor fixo: impedir negativos
                    if (desconto < 0) {
                        desconto = 0;
                        descontoInput.value = '0';
                        mostrarAlerta('O desconto não pode ser negativo.', 'warning');
                    }
                }

                const totalParcial = subtotal + acrescimo;

                if (desconto > totalParcial) {
                    desconto = totalParcial;
                    descontoInput.value = desconto.toFixed(2);
                    mostrarAlerta('O desconto não pode ser maior que o total.', 'warning');
                }

                valorTotalCalculado = totalParcial - desconto;

                valorTotalDisplay.value = formatarMoeda(valorTotalCalculado);
                totalHidden.value = valorTotalCalculado.toFixed(2);

                calcularValorRestante();
            }

            // Função para calcular valor restante
            function calcularValorRestante() {
                const totalPago = pagamentosAdicionados.reduce((sum, pagamento) => sum + pagamento.valor, 0);
                valorRestante = valorTotalCalculado - totalPago;

                valorRestanteDisplay.value = formatarMoeda(valorRestante);
                valorRestanteHidden.value = valorRestante.toFixed(2);

                // Atualizar cor do campo restante
                if (valorRestante > 0) {
                    valorRestanteDisplay.classList.remove('text-success');
                    valorRestanteDisplay.classList.add('text-danger');
                } else {
                    valorRestanteDisplay.classList.remove('text-danger');
                    valorRestanteDisplay.classList.add('text-success');
                }

                atualizarTotalPago();
            }

            // Função para atualizar total pago
            function atualizarTotalPago() {
                const totalPago = pagamentosAdicionados.reduce((sum, pagamento) => sum + pagamento.valor, 0);
                totalPagoDisplay.textContent = formatarMoeda(totalPago);
            }

            // Função para mostrar alerta
            function mostrarAlerta(mensagem, tipo = 'warning') {
                mensagemAlerta.textContent = mensagem;
                alertaPagamento.className = `alert alert-${tipo}`;
                alertaPagamento.style.display = 'block';

                setTimeout(() => {
                    alertaPagamento.style.display = 'none';
                }, 5000);
            }

            // Função para adicionar pagamento
            function adicionarPagamento() {
                const formaPagamentoId = formaPagamentoSelect.value;
                const formaPagamentoNome = formaPagamentoSelect.options[formaPagamentoSelect.selectedIndex].text;
                const valor = parseFloat(valorPagamentoInput.value);

                // Validações
                if (!formaPagamentoId) {
                    mostrarAlerta('Selecione uma forma de pagamento.');
                    return;
                }

                if (!valor || valor <= 0) {
                    mostrarAlerta('Informe um valor válido para o pagamento.');
                    return;
                }

                if (valor > valorRestante) {
                    mostrarAlerta('O valor do pagamento não pode ser maior que o valor restante.');
                    return;
                }

                // Adicionar pagamento à lista
                const pagamento = {
                    id: Date.now(), // ID único baseado em timestamp
                    forma_pagamento_id: formaPagamentoId,
                    forma_pagamento_nome: formaPagamentoNome,
                    valor: valor
                };

                pagamentosAdicionados.push(pagamento);
                atualizarListaPagamentos();
                calcularValorRestante();

                // Limpar campos
                formaPagamentoSelect.value = '';
                valorPagamentoInput.value = '';
            }

            // Função para pagar valor restante
            function pagarRestante() {
                if (valorRestante <= 0) {
                    mostrarAlerta('Não há valor restante para pagar.', 'info');
                    return;
                }

                if (!formaPagamentoSelect.value) {
                    mostrarAlerta('Selecione uma forma de pagamento.');
                    return;
                }

                valorPagamentoInput.value = valorRestante.toFixed(2);
            }

            // Função para atualizar lista de pagamentos
            function atualizarListaPagamentos() {
                //seta acrescimo e desconto como readonly se tiver já algum pag
                const btnToggle = document.getElementById('toggleTipoDesconto')
                if (pagamentosAdicionados.length > 0) {
                    btnToggle.disabled = true
                    acrescimoInput.setAttribute('readonly', true);
                    descontoInput.setAttribute('readonly', true);
                } else {
                    btnToggle.disabled = false
                    acrescimoInput.removeAttribute('readonly');
                    descontoInput.removeAttribute('readonly');
                }

                listaPagamentos.innerHTML = '';

                pagamentosAdicionados.forEach(pagamento => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${pagamento.forma_pagamento_nome}</td>
                <td>${formatarMoeda(pagamento.valor)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removerPagamento(${pagamento.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
                    listaPagamentos.appendChild(row);
                });

                // Atualizar campo hidden com JSON dos pagamentos
                pagamentosJsonInput.value = JSON.stringify(pagamentosAdicionados);
            }

            // Função global para remover pagamento (chamada pelo onclick)
            window.removerPagamento = function(id) {
                pagamentosAdicionados = pagamentosAdicionados.filter(pagamento => pagamento.id !== id);
                atualizarListaPagamentos();
                calcularValorRestante();
            };

            // Event listeners
            acrescimoInput.addEventListener('input', calcularValorTotal);
            descontoInput.addEventListener('input', calcularValorTotal);
            adicionarPagamentoBtn.addEventListener('click', adicionarPagamento);
            pagarRestanteBtn.addEventListener('click', pagarRestante);

            //mostra o valor do desconto em R$ se for colocado em %
            descontoInput.addEventListener('input', function() {
                const subtotal = parseFloat(subtotalHidden.value) || 0;
                const valorDigitado = parseFloat(descontoInput.value.replace(',', '.')) || 0;
                const spanPreview = document.getElementById('previewDesconto');

                if (descontoEmPercentual) {
                    const descontoReais = (subtotal * valorDigitado) / 100;
                    spanPreview.textContent = `= R$ ${descontoReais.toFixed(2)}`;
                } else {
                    spanPreview.textContent = '';
                }
            });

            //botão para alternar entre % e R$
            let descontoEmPercentual = false;

            document.getElementById('toggleTipoDesconto').addEventListener('click', function() {
                descontoEmPercentual = !descontoEmPercentual;

                const btn = document.getElementById('toggleTipoDesconto');
                btn.textContent = descontoEmPercentual ? 'R$' : '%';

                const label = document.getElementById('labelDesconto');
                label.textContent = descontoEmPercentual ? 'Desconto (%):' : 'Desconto (R$):';
                descontoInput.value = ''



                calcularValorTotal(); // Atualiza pra refletir o modo
            });


            // Monitorar mudanças nos valores de aluguel e buffet
            let ultimoValorAluguel = '';
            let ultimoValorBuffet = '';

            setInterval(() => {
                const valorAluguelAtual = document.getElementById('valor_total')?.value || '';
                const valorBuffetAtual = document.getElementById('total_buffet')?.value || '';

                if (valorAluguelAtual !== ultimoValorAluguel || valorBuffetAtual !== ultimoValorBuffet) {
                    ultimoValorAluguel = valorAluguelAtual;
                    ultimoValorBuffet = valorBuffetAtual;
                    calcularSubtotal();
                }
            }, 500); // verifica a cada meio segundo



              function calcularTotalAdicionais() {
        let totalGeral = 0;

        document.querySelectorAll('.adicional-quantidade').forEach(input => {
            const quantidade = parseFloat(input.value.replace(',', '.')) || 0;
            const valorUnitario = parseFloat(input.dataset.valor) || 0;
            const totalItem = quantidade * valorUnitario;

            const totalField = input.closest('.row').querySelector('.adicional-total');
            totalField.value = totalItem > 0 ? totalItem.toFixed(2) : '';

            totalGeral += totalItem;
        });

        const totalInput = document.getElementById('totalAdicionais');

        if (totalInput) {
            totalInput.value = `R$ ${totalGeral.toFixed(2).replace('.', ',')}`;  
        }
        calcularSubtotal()
    }

    // Atualiza ao alterar qualquer quantidade
    document.querySelectorAll('.adicional-quantidade').forEach(input => {
        input.addEventListener('input', calcularTotalAdicionais);
    });

    // Cálculo inicial
    calcularTotalAdicionais();
            



            // Calcular subtotal inicial
            calcularSubtotal();

            // Validação antes do envio do formulário
            document.getElementById('aluguelform').addEventListener('submit', function(e) {
                const valorRestanteValue = parseFloat(valorRestanteHidden.value) || 0;
                const clienteSelect = document.getElementById('cliente_id');
                const clienteSelecionado = clienteSelect && clienteSelect.value;

                if (!clienteSelecionado) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cliente obrigatório',
                            text: 'Por favor, selecione um cliente para continuar.',
                            confirmButtonText: 'Ok',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Por favor, selecione um cliente para continuar.');
                    }
                    const dadosTab = document.querySelector('a[href="#info"]');
                    if (dadosTab) dadosTab.click();


                    return false;
                }

                if (valorRestanteValue > 0.01) { // Usando 0.01 para evitar problemas de precisão decimal
                    e.preventDefault();

                    // Usar SweetAlert2 se disponível, senão usar alert padrão
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pagamento Incompleto',
                            text: 'Para prosseguir, o pagamento deve ser realizado completamente. Valor restante: ' +
                                formatarMoeda(valorRestanteValue),
                            confirmButtonText: 'Entendi',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Para prosseguir, o pagamento deve ser realizado completamente. Valor restante: ' +
                            formatarMoeda(valorRestanteValue));
                    }

                    mostrarAlerta(
                        'Para prosseguir, o pagamento deve ser realizado completamente. Valor restante: ' +
                        formatarMoeda(valorRestanteValue), 'danger');

                    // Focar na aba de pagamento se não estiver ativa
                    const pagamentoTab = document.querySelector('a[href="#tab-pagamento"]');
                    if (pagamentoTab) {
                        pagamentoTab.click();
                    }

                    return false;
                }

                // Se chegou até aqui, o pagamento está completo
                // Opcional: mostrar loading no botão
                const submitBtn = document.getElementById('btn-submit-aluguel');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switchInput = document.getElementById('ativoSwitch');
            const label = document.getElementById('ativoLabel');

            function updateLabel() {
                label.textContent = switchInput.checked ? 'Sim' : 'Não';
            }

            // Atualiza ao carregar a página (caso o browser recarregue com checked vindo do servidor)
            updateLabel();

            // Atualiza sempre que o usuário mudar o switch
            switchInput.addEventListener('change', updateLabel);
        });
    </script>

    <script>
        //habilita a aba buffet pelo checkbox
        document.addEventListener("DOMContentLoaded", function() {
            const checkbox = document.getElementById("ativoSwitch");
            const abaBuffet = document.getElementById("buffetAba");

            function atualizarEstilo() {
                if (checkbox.checked) {
                    abaBuffet.removeAttribute("style");
                } else {
                    abaBuffet.style.display = "none";
                }
            }
            // Atualiza o estilo ao carregar a página
            atualizarEstilo();

            // Monitorando mudanças no checkbox em tempo real
            checkbox.addEventListener("change", atualizarEstilo);
        });
    </script>
    <script>
       const teste12 = window.itensSelecionadosBuffet = {!! json_encode($itensSelecionados ?? []) !!};
       const teste13 = window.opcaoSelecionadaBuffet = {{ $opcaoSelecionada ?? 'null' }};
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cardapioSelect = document.getElementById('cardapio_id');
            const categoriasContainer = document.getElementById('categoriasContainer');
            const opcoesContainer = document.getElementById('opcoesContainer');
            const numeroPessoasInput = document.getElementById('numero_pessoas_buffet');
            const totalFinalInput = document.getElementById('total_buffet');

            // Campos hidden para armazenar as escolhas
            const buffetCategoriasEscolhidas = document.getElementById('buffet_categorias_escolhidas');
            const buffetOpcaoEscolhida = document.getElementById('buffet_opcao_escolhida');

            let opcoesData = [];

            cardapioSelect.addEventListener('change', function() {
                const cardapioId = this.value;
                categoriasContainer.innerHTML = '';
                opcoesContainer.innerHTML = '';
                totalFinalInput.value = '';

                // Limpar campos hidden
                buffetCategoriasEscolhidas.value = '';
                buffetOpcaoEscolhida.value = '';

                if (cardapioId) {
                    fetch(`/cardapios/${cardapioId}/dados`)
                        .then(response => response.json())
                        .then(data => {
                            opcoesData = data.opcoes; // Armazena para uso no cálculo depois

                            // ---- CATEGORIAS DAS SEÇÕES ----
                            data.secoes.forEach(secao => {
                                secao.categorias.forEach(categoria => {
                                    const categoriaDiv = document.createElement('div');
                                    categoriaDiv.classList.add('card', 'mb-3',
                                        'border');

                                    const header = document.createElement('div');
                                    header.classList.add('card-header', 'green',
                                        'pb-0');
                                    header.innerHTML =
                                        `<strong>${categoria.nome_categoria_item}</strong> (${secao.nome_secao_cardapio})<br><small>Máximo de ${categoria.numero_escolhas_permitidas} escolha(s)</small>`;

                                    const body = document.createElement('div');
                                    body.classList.add('card-body');
                                    const itensDiv = document.createElement('div');
                                    itensDiv.classList.add('row');


                                    categoria.itens.forEach(disp => {
                                        const item = disp.item;
                                        const checkboxDiv = document
                                            .createElement('div');
                                        checkboxDiv.classList.add('form-check',
                                            'col-md-6', 'col-lg-4', 'mb-2');

                                        const checkbox = document.createElement(
                                            'input');
                                        checkbox.type = 'checkbox';
                                        checkbox.name =
                                            `categorias[${categoria.id}][itens][]`;
                                        checkbox.value = item.id;
                                        checkbox.classList.add(
                                            'form-check-input');
                                        checkbox.dataset.categoriaId = categoria
                                            .id;
                                        checkbox.id =
                                            `checkbox-${categoria.id}-${item.id}`;

                                        const label = document.createElement(
                                            'label');
                                        label.classList.add('form-check-label',
                                            'ms-1');
                                        label.textContent = item.nome_item;
                                        label.setAttribute('for', checkbox.id);

                                        checkboxDiv.appendChild(checkbox);
                                        checkboxDiv.appendChild(label);
                                        itensDiv.appendChild(checkboxDiv);
                                    });

                                    body.appendChild(itensDiv);
                                    categoriaDiv.appendChild(header);
                                    categoriaDiv.appendChild(body);
                                    categoriasContainer.appendChild(categoriaDiv);
                                });
                            });

                            // Limitar número de seleções por categoria e atualizar campos hidden
                            const checkboxes = categoriasContainer.querySelectorAll(
                                'input[type="checkbox"]');
                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    const categoriaId = this.dataset.categoriaId;
                                    const categoriaCheckboxes = categoriasContainer
                                        .querySelectorAll(
                                            `input[data-categoria-id="${categoriaId}"]`
                                        );

                                    // Buscar dados da categoria na estrutura aninhada
                                    let categoriaData = null;
                                    data.secoes.forEach(secao => {
                                        const categoria = secao.categorias.find(
                                            cat => cat.id == categoriaId);
                                        if (categoria) {
                                            categoriaData = categoria;
                                        }
                                    });

                                    const selecionados = Array.from(categoriaCheckboxes)
                                        .filter(cb => cb.checked);

                                    if (categoriaData && selecionados.length >
                                        categoriaData.numero_escolhas_permitidas) {
                                        this.checked = false;
                                        Swal.fire({
                                            icon: "error",
                                            title: "Limite excedido",
                                            text: `Você só pode escolher até ${categoriaData.numero_escolhas_permitidas} item(ns) para a categoria "${categoriaData.nome_categoria_item}".`,
                                        });
                                    } else {
                                        // Atualizar campo hidden com as escolhas das categorias
                                        atualizarCategoriasEscolhidas();
                                    }
                                });
                            });

                            // ---- OPÇÕES DE REFEIÇÃO (COM ITENS INTERNOS) ----
                            if (data.opcoes.length > 0) {
                                const opcoesTitle = document.createElement('h5');
                                opcoesTitle.textContent = "Escolha uma Opção de Refeição:";
                                opcoesContainer.appendChild(opcoesTitle);

                                data.opcoes.forEach(opcao => {
                                    const card = document.createElement('div');
                                    card.classList.add('card', 'mb-3');

                                    const cardHeader = document.createElement('div');
                                    cardHeader.classList.add('card-header', 'green', 'pb-0');

                                    const inputRadio = document.createElement('input');
                                    inputRadio.classList.add('form-check-input');
                                    inputRadio.type = 'radio';
                                    inputRadio.name = 'opcao_escolhida';
                                    inputRadio.value = opcao.id;
                                    inputRadio.dataset.preco = opcao.PrecoPorPessoa;
                                    inputRadio.id = `radio-${opcao.id}`; // ID único

                                    const label = document.createElement('label');
                                    label.classList.add('form-check-label');
                                    label.setAttribute('for', inputRadio
                                        .id); // Vinculando ao input
                                    label.textContent =
                                        `${opcao.NomeOpcaoRefeicao} - R$ ${parseFloat(opcao.PrecoPorPessoa).toFixed(2)}`;

                                    // Criando a div do form-check e adicionando os elementos
                                    const formCheckDiv = document.createElement('div');
                                    formCheckDiv.classList.add('form-check');
                                    formCheckDiv.appendChild(inputRadio);
                                    formCheckDiv.appendChild(label);

                                    cardHeader.appendChild(formCheckDiv);
                                    card.appendChild(cardHeader);

                                    const cardBody = document.createElement('div');
                                    cardBody.classList.add('card-body', 'p-2', 'h6');

                                    opcao.categorias.forEach(categoria => {
                                        const catTitle = document.createElement(
                                            'strong');
                                        catTitle.textContent = categoria
                                            .nome_categoria_item;
                                        cardBody.appendChild(catTitle);

                                        categoria.itens.forEach(disp => {
                                            const item = disp.item
                                            const itemDiv = document
                                                .createElement('div');
                                            itemDiv.classList.add('ms-3');
                                            itemDiv.textContent =
                                                `- ${item.nome_item}`;
                                            cardBody.appendChild(itemDiv);
                                        });

                                        cardBody.appendChild(document.createElement(
                                            'hr'));
                                    });

                                    card.appendChild(cardBody);
                                    opcoesContainer.appendChild(card);
                                });

                                // Evento de mudança para recalcular valor ao escolher uma opção
                                opcoesContainer.querySelectorAll('input[name="opcao_escolhida"]')
                                    .forEach(radio => {
                                        radio.addEventListener('change', function() {
                                            calcularValorFinal();
                                            // Atualizar campo hidden com a opção escolhida
                                            buffetOpcaoEscolhida.value = this.value;
                                        });
                                    });
                            }

                            // Marcar itens já selecionados (se vieram do backend)
                            if (window.itensSelecionadosBuffet.length > 0) {
                                window.itensSelecionadosBuffet.forEach(itemId => {
                                    const checkbox = categoriasContainer.querySelector(`input[type="checkbox"][value="${itemId}"]`);
                                    if (checkbox) {
                                        checkbox.checked = true;
                                    }
                                });
                                // Atualiza os campos hidden também
                                atualizarCategoriasEscolhidas();
                            }

                            // Marcar opção de refeição já escolhida
                            if (window.opcaoSelecionadaBuffet !== null) {
                                const radio = opcoesContainer.querySelector(`input[name="opcao_escolhida"][value="${window.opcaoSelecionadaBuffet}"]`);
                                if (radio) {
                                    radio.checked = true;
                                    calcularValorFinal();
                                    buffetOpcaoEscolhida.value = radio.value;
                                }
                            }

                        })
                        .catch(error => {
                            console.error('Erro ao carregar dados do cardápio:', error);
                        });
                }
                
            });

            if (cardapioSelect.value) {
                cardapioSelect.dispatchEvent(new Event('change'));
            }

            // Função para atualizar o campo hidden com as categorias escolhidas
            function atualizarCategoriasEscolhidas() {
                const categoriasEscolhidas = {};
                const checkboxes = categoriasContainer.querySelectorAll('input[type="checkbox"]:checked');

                checkboxes.forEach(checkbox => {
                    const categoriaId = checkbox.dataset.categoriaId;
                    const itemId = checkbox.value;

                    if (!categoriasEscolhidas[categoriaId]) {
                        categoriasEscolhidas[categoriaId] = [];
                    }
                    categoriasEscolhidas[categoriaId].push(itemId);
                });

                buffetCategoriasEscolhidas.value = JSON.stringify(categoriasEscolhidas);
            }

            // Calcular Total Buffet (Baseado no número de pessoas e na opção selecionada)
            function calcularValorFinal() {
                const numeroPessoas = parseInt(numeroPessoasInput.value) || 0;
                const opcaoSelecionada = document.querySelector('input[name="opcao_escolhida"]:checked');
                let total = 0;

                if (opcaoSelecionada) {
                    const precoPorPessoa = parseFloat(opcaoSelecionada.dataset.preco);
                    total = numeroPessoas * precoPorPessoa;
                }

                totalFinalInput.value = "R$ " + total.toFixed(2).replace('.', ',');
            }

            numeroPessoasInput.addEventListener('input', calcularValorFinal);
        });
    </script>


    <script>
        // calcula o valor total do espaço
        document.addEventListener('DOMContentLoaded', function() {
            const espacoSelect = document.getElementById('espaco_id_hidden');
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const valorTotal = document.getElementById('valor_total');
            const tipoEvento = document.getElementById('tipo_evento')

            function calcularValor() {
                console.log('mudouuuuuu')
                const inicio = dataInicio.value;
                const fim = dataFim.value;
                const espaco = espacoSelect.value;
                const evento = tipoEvento.value;

                if (!inicio || !fim || !espaco) return;

                fetch('/calcular-valor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            data_inicio: inicio,
                            data_fim: fim,
                            espaco_id: espaco,
                            tipo_evento: evento
                        })
                    })


                    .then(response => response.json())
                    .then(data => {
                        console.log(data.total)
                        valorTotal.value = `R$ ${parseFloat(data.total).toFixed(2).replace('.', ',')}`;
                    });
            }

            espacoSelect.addEventListener('change', calcularValor);
            dataInicio.addEventListener('change', calcularValor);
            dataFim.addEventListener('change', calcularValor);
            $('#tipo_evento').on('change', function () {
                calcularValor();
            });
        });
    </script>

    <script>
        document.getElementById('btnProximo').addEventListener('click', function() {
            const buffetAtivo = document.getElementById('ativoSwitch').checked;

            if (buffetAtivo) {
                // vai para a aba buffet
                const abaBuffet = document.querySelector('a[href="#tab-buffet"]');
                abaBuffet.click();
            } else {
                // vai para a aba adicional
                const abaAdicional = document.querySelector('a[href="#tab-adicional"]');
                abaAdicional.click();
            }
        });

        document.getElementById('btnProximoBuffet').addEventListener('click', function() {
            const abaAdicional = document.querySelector('a[href="#tab-adicional"]');
            if (abaAdicional) {
                abaAdicional.click();
            }
        });
        document.getElementById('btnProximoAdicional').addEventListener('click', function() {
            const abaPagamentos = document.querySelector('a[href="#tab-pagamento"]');
            if (abaPagamentos) {
                abaPagamentos.click();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#cliente_id').select2({
                placeholder: 'Selecione um Cliente',
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route('clientes.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(cliente) {
                                return {
                                    id: cliente.id,
                                    text: cliente.nome_razao_social
                                };
                            })
                        };
                    },
                    cache: true
                },
                width: '100%',
                language: {
                    noResults: function() {
                        return "Nenhum resultado encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    inputTooShort: function(args) {
                        var remainingChars = args.minimum - args.input.length;
                        return 'Digite mais ' + remainingChars + ' caractere' + (remainingChars !== 1 ?
                            's' : '') + ' para buscar';
                    },
                    loadingMore: function() {
                        return "Carregando mais resultados...";
                    }
                }
            });
            $('#tipo_evento').select2({
                    placeholder: 'Selecione um tipo',
                    width: '100%'
                });
        });
    </script>


<script> //calcula o total do custo daquela mobília (linha)
document.addEventListener('DOMContentLoaded', function () {
    function atualizarTotais() {
        document.querySelectorAll('.row').forEach(row => {
            const input = row.querySelector('.adicional-quantidade');
            const totalInput = row.querySelector('.adicional-total');
            if (input && totalInput && input.dataset.valor) {
                const valorUnitario = parseFloat(input.dataset.valor);
                const quantidade = parseInt(input.value) || 0;
                const total = valorUnitario * quantidade;
                totalInput.value = "R$ " + total.toFixed(2).replace('.', ',');
            }
        });
    }

    document.querySelectorAll('.adicional-quantidade').forEach(input => {
        input.addEventListener('input', atualizarTotais);
    });

    atualizarTotais();
});
</script>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos para o container do mapa */
        #reservation_map_container {
            overflow-x: auto;
            /* Permite rolagem horizontal */
            -webkit-overflow-scrolling: touch;
            /* Melhora scroll em iOS */
            margin-top: 20px;
            padding-bottom: 10px;
            /* Espaço para a barra de rolagem não cobrir conteúdo */
            border: 1px solid #dee2e6;
            /* Borda sutil no container */
            border-radius: 0.25rem;
            /* Cantos arredondados */
            max-width: 100%;
            /* Garante que não ultrapasse o container pai */
        }

        /* Estilos básicos para a tabela do mapa */
        .reservation-map-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Colunas de data com largura igual, melhor performance */
            min-width: 700px;
            /* Ajuste conforme necessário, baseado no número de dias e padding */
            /* border: 1px solid #dee2e6; */
            /* Borda externa movida para o container */
        }

        .reservation-map-table th,
        .reservation-map-table td {
            border: 1px solid #dee2e6;
            padding: 0.6rem 0.4rem;
            /* Ajuste padding (vertical / horizontal) */
            text-align: center;
            vertical-align: middle;
            font-size: 0.8rem;
            /* Tamanho base da fonte */
            height: 45px;
            /* Altura base da célula */
            position: relative;
            box-sizing: border-box;
            /* Inclui padding e borda na largura/altura */
        }

        /* Cabeçalhos */
        .reservation-map-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            /* Um pouco mais forte */
            white-space: nowrap;
            /* Não quebrar cabeçalhos de data/espaço */
            font-size: 0.75rem;
            /* Fonte ligeiramente menor para cabecalhos */
            position: sticky;
            /* Fixar cabeçalho ao rolar verticalmente */
            top: 0;
            z-index: 10;
        }

        /* Cabeçalho da coluna de Espaços */
        .reservation-map-table th.space-header {
            text-align: left;
            min-width: 130px;
            /* Largura mínima para nome do espaço */
            width: 130px;
            /* Largura fixa pode ajudar no layout fixo */
            padding-left: 0.8rem;
            /* Mais espaço à esquerda */
            position: sticky;
            /* Fixar coluna de espaços ao rolar horizontalmente */
            left: 0;
            background-color: #f8f9fa;
            /* Manter fundo igual ao header */
            z-index: 11;
            /* Acima do header de data */
            border-right: 2px solid #ced4da;
            /* Separador mais visível */
        }

        /* Células de dados (nome do espaço) */
        .reservation-map-table td.space-header {
            text-align: left;
            font-weight: 500;
            background-color: #ffffff;
            /* Fundo branco para diferenciar do header */
            position: sticky;
            left: 0;
            z-index: 5;
            /* Abaixo dos headers, mas acima das células de data */
            border-right: 2px solid #ced4da;
            /* Separador mais visível */
            /* Herdar min-width e width do cabeçalho para consistência */
            min-width: 130px;
            width: 130px;
            padding-left: 0.8rem;
        }


        /* Estilos para as células de data */
        .date-cell {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            width: 45px;
        }

        .date-cell.available:hover {
            background-color: #e9f5e9;
        }

        .date-cell.booked {
            background-color: #f8d7da;
            color: #721c24;
            cursor: not-allowed;
            font-style: italic;
        }

        /* Adicionar um estilo visual mais claro para ocupado */
        .date-cell.booked span {
            font-weight: bold;
            color: #dc3545;
        }


        .date-cell.selected {
            background-color: var(--green-1);
            color: white;
            font-weight: bold;
            box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.1);
            /* Contorno sutil */
        }

        .date-cell.selecting {
            background-color: #b8ffb8;
        }

        /* Estilos para os filtros */
        #filter_button {
            margin-top: 5px;
            /* Pequeno ajuste de alinhamento */
        }

        /* Placeholder de feedback */
        #selection_feedback {
            min-height: 1.5em;
            /* Evita que o layout salte quando a mensagem aparece/desaparece */
        }

        /* Estilos para o container do buffet */
        .buffet-container {
            max-width: 100%;
            overflow-x: hidden;
            /* Evita overflow horizontal */
        }

        /* Melhorias para os cards do buffet */
        .buffet-container .card {
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .buffet-container .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }

        .buffet-container .card-body {
            padding: 1rem;
        }

        /* Responsividade para os checkboxes do buffet */
        .buffet-container .form-check {
            margin-bottom: 0.5rem;
            word-wrap: break-word;
        }

        .buffet-container .form-check-label {
            font-size: 0.9rem;
            line-height: 1.4;
            margin-left: 0.25rem;
        }

        /* --- Media Queries para Responsividade --- */

        /* Telas Médias (Tablets) */
        @media (max-width: 992px) {
            .reservation-map-table {
                min-width: 600px;
                /* Reduzir largura mínima */
            }

            .reservation-map-table th,
            .reservation-map-table td {
                padding: 0.5rem 0.3rem;
                font-size: 0.75rem;
                height: 40px;
            }

            .reservation-map-table th.space-header,
            .reservation-map-table td.space-header {
                min-width: 110px;
                width: 110px;
            }

            .date-cell {
                min-width: 35px;
            }

            /* Ajustes para o buffet em tablets */
            .buffet-container .form-check {
                margin-bottom: 0.75rem;
            }
        }

        /* Telas Pequenas (Celulares) */
        @media (max-width: 767px) {
            .reservation-map-table {
                min-width: 500px;
                /* Reduzir mais a largura mínima */
            }

            .reservation-map-table th,
            .reservation-map-table td {
                padding: 0.4rem 0.2rem;
                /* Menos padding */
                font-size: 0.7rem;
                /* Fonte ainda menor */
                height: 35px;
                /* Células mais baixas */
            }

            .reservation-map-table th {
                font-size: 0.65rem;
                /* Cabeçalhos ainda menores */
            }

            .reservation-map-table th.space-header,
            .reservation-map-table td.space-header {
                min-width: 90px;
                /* Coluna de espaço mais estreita */
                width: 90px;
                font-size: 0.7rem;
                /* Ajustar fonte do nome do espaço */
                padding-left: 0.5rem;
            }

            .date-cell {
                min-width: 30px;
                /* Células de data mais estreitas */
            }

            /* Ajustes para o buffet em celulares */
            .buffet-container .card-header {
                padding: 0.5rem 0.75rem;
            }

            .buffet-container .card-body {
                padding: 0.75rem;
            }

            .buffet-container .form-check {
                margin-bottom: 0.5rem;
            }

            .buffet-container .form-check-label {
                font-size: 0.85rem;
            }

            /* Opcional: Esconder o texto 'X' e usar só fundo em telas muito pequenas */
            /*
                    .date-cell.booked span {
                        display: none;
                    }
                    */
        }

        /* Ajustes finos para telas muito pequenas (opcional) */
        @media (max-width: 480px) {
            .reservation-map-table {
                min-width: 400px;
            }

            .reservation-map-table th.space-header,
            .reservation-map-table td.space-header {
                min-width: 75px;
                width: 75px;
                font-size: 0.65rem;
            }

            .date-cell {
                min-width: 28px;
            }

            .reservation-map-table th,
            .reservation-map-table td {
                height: 30px;
                padding: 0.3rem 0.1rem;
            }

            /* Ajustes para o buffet em telas muito pequenas */
            .buffet-container .card-header {
                padding: 0.5rem;
            }

            .buffet-container .card-body {
                padding: 0.5rem;
            }

            .buffet-container .form-check-label {
                font-size: 0.8rem;
            }
        }
    </style>
@endsection

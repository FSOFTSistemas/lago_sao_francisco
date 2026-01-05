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
                        <!-- Abas de navegação -->
                        <ul class="nav nav-tabs mb-3" id="reservaTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="dados-tab" data-toggle="tab" href="#dados" role="tab"
                                    aria-controls="dados" aria-selected="true">Dados da Reserva</a>
                            </li>
                            <?php if (isset($reserva) && !in_array($reserva->situacao, ['cancelado', 'noshow'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" id="produtos-tab" data-toggle="tab" href="#produtos" role="tab"
                                    aria-controls="produtos" aria-selected="false">Produtos</a>
                            </li>
                            <?php endif; ?>



                        </ul>

                        <!-- Conteúdo das abas -->
                        <div class="tab-content" id="reservaTabsContent">
                            <!-- Aba 1: Dados da Reserva -->
                            <div class="tab-pane fade show active" id="dados" role="tabpanel"
                                aria-labelledby="dados-tab">
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
                                                    if (
                                                        in_array($situacaoAtual, [
                                                            'hospedado',
                                                            'finalizada',
                                                            'cancelado',
                                                            'noshow',
                                                        ])
                                                    ) {
                                                        $podeAlterarSituacao = false;
                                                    }
                                                }
                                            @endphp

                                            <label class='radio-option'>
                                                <input type='radio' name='situacao' value='pre-reserva'
                                                    @checked($situacaoAtual === 'pre-reserva')
                                                    @if ($isEdicao && !in_array($situacaoAtual, ['pre-reserva', 'reserva']) && $podeAlterarSituacao) disabled @endif
                                                    @if (!$podeAlterarSituacao) disabled @endif required>
                                                <span class='badge badge-warning'>pré-reservar</span>
                                            </label>

                                            <label class='radio-option'>
                                                <input type='radio' name='situacao' value='reserva'
                                                    @checked($situacaoAtual === 'reserva')
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
                                                        <input type='radio' name='situacao' value='hospedado' checked
                                                            disabled>
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
                                                        <input type='radio' name='situacao' value='finalizada' checked
                                                            disabled>
                                                        <span class='badge badge-success'>finalizada</span>
                                                    </label>
                                                @endif

                                                @if ($situacaoAtual === 'cancelado')
                                                    <label class='radio-option'>
                                                        <input type='radio' name='situacao' value='cancelado' checked
                                                            disabled>
                                                        <span class='badge badge-secondary'>cancelado</span>
                                                    </label>
                                                @endif

                                                @if ($situacaoAtual === 'noshow')
                                                    <label class='radio-option'>
                                                        <input type='radio' name='situacao' value='cancelado' checked
                                                            disabled>
                                                        <span class='badge badge-noshow'>No Show</span>
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
                                                <select class='form-control select2' name='hospede_id_disabled'
                                                    id='hospede_id' disabled>
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
                                            <button type="button" id="btn-addhospede" class="btn btn-primary"
                                                data-toggle="modal" data-target="#modalCadastrarHospede">
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
                                            <small style="color: red">Selecione um período antes de escolher o
                                                quarto</small>
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
                                            <select class="form-control select2" id="quarto" name="quarto_id"
                                                disabled>
                                                <option value="">Selecione um quarto</option>
                                                @if (isset($quartosAgrupados))
                                                    @foreach ($quartosAgrupados as $categoria => $quartos)
                                                        <optgroup label="{{ $categoria }}">
                                                            @foreach ($quartos as $quarto)
                                                                <option value="{{ $quarto->id }}"
                                                                    data-ocupantes="{{ $quarto->categoria->ocupantes }}"
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
                                        <label class="col-md-3 label-control" for="valor_diaria">* Valor da
                                            diária:</label>
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
                                            <div class="col-md-5">
                                                <label for="n_adultos">* Nº adultos/adolescentes</label>
                                                <input type="number" name="n_adultos" id="n_adultos"
                                                    class="form-control"
                                                    value="{{ old('n_adultos', isset($reserva) ? $reserva->n_adultos : 1) }}"
                                                    min="1">
                                            </div>

                                            <div class="col-md-5">
                                                <label for="n_criancas">* Nº crianças (6 a 12 anos)</label>
                                                <input type="number" name="n_criancas" id="n_criancas"
                                                    class="form-control"
                                                    value="{{ old('n_criancas', $reserva->n_criancas ?? 0) }}"
                                                    min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4" id="container-hospedes-secundarios"
                                        style="display: none;">
                                        <label for="hospedes_secundarios"
                                            class="col-md-3 col-form-label text-md-right font-weight-bold">Hóspedes
                                            Secundários:</label>
                                        <div class="col-md-4">
                                            <div class="hospedes-sec-select-container">
                                                <select class="form-control select2-hospedes-sec"
                                                    id="hospedes_secundarios" name="hospedes_secundarios[]"
                                                    multiple="multiple" style="width: 100%;">
                                                    @php
                                                        // 1. Tenta pegar do 'old' (se houve erro de validação)
                                                        $selectedHospedesSec = old('hospedes_secundarios');

                                                        // 2. Se não tem old, e estamos editando uma reserva
                                                        if (
                                                            !$selectedHospedesSec &&
                                                            isset($reserva) &&
                                                            !empty($reserva->hospedes_secundarios)
                                                        ) {
                                                            $dados = $reserva->hospedes_secundarios;

                                                            // Verifica se é uma Collection do Laravel (Relacionamento many-to-many)
                                                            if ($dados instanceof \Illuminate\Support\Collection) {
                                                                $selectedHospedesSec = $dados->pluck('id')->toArray();
                                                            }
                                                            // Verifica se é um Array nativo (Cast 'array' ou JSON)
                                                            elseif (is_array($dados)) {
                                                                // Pega o primeiro item pra saber se é um Objeto/Array ou se já é o ID direto
                                                                $primeiro = reset($dados);

                                                                // Se o array contém objetos ou arrays associativos (ex: [['id'=>1], ['id'=>2]])
                                                                if (
                                                                    is_object($primeiro) ||
                                                                    (is_array($primeiro) && isset($primeiro['id']))
                                                                ) {
                                                                    $selectedHospedesSec = collect($dados)
                                                                        ->pluck('id')
                                                                        ->toArray();
                                                                } else {
                                                                    // Se chegou aqui, já é o array de IDs ["3", "5"]
                                                                    $selectedHospedesSec = $dados;
                                                                }
                                                            }
                                                        }

                                                        // 3. Garante que sempre seja array para o in_array não quebrar
                                                        if (!is_array($selectedHospedesSec)) {
                                                            $selectedHospedesSec = [];
                                                        }
                                                    @endphp

                                                    @foreach ($hospedes as $hospsec)
                                                        @if ($hospsec->nome !== 'Bloqueado')
                                                            <option value="{{ $hospsec->id }}"
                                                                {{ in_array($hospsec->id, $selectedHospedesSec) ? 'selected' : '' }}>
                                                                {{ $hospsec->nome }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row" id="campoPlaca">
                                        <label for="placa_veiculo" class="col-md-3 label-control">Placa do Veículo</label>
                                        <div class="col-md-4">
                                            <input type="text" name="placa_veiculo" id="placa_veiculo"
                                                class="form-control"
                                                value="{{ old('placa_veiculo', $reserva->placa_veiculo ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="form-group row" id="campoCanalVenda">
                                        <label for="canal_venda" class="col-md-3 label-control">Canal de Venda</label>
                                        <div class="col-md-4">
                                            <select class="form-control select2" name="canal_venda" id="canal_venda">
                                                <option value="">Selecione um canal</option>
                                                @php
                                                    // Pegamos a variável passada pelo controller
                                                    $opcoesCanal = $canaisVenda ?? [
                                                        'WhatsApp',
                                                        'Instagram',
                                                        'Telefone',
                                                        'Indicação',
                                                        'Balcão',
                                                        'Facebook',
                                                        'Email',
                                                        'Outros',
                                                    ];
                                                    $canalSelecionado = old('canal_venda', $reserva->canal_venda ?? '');
                                                @endphp
                                                @foreach ($opcoesCanal as $canal)
                                                    <option value="{{ $canal }}" @selected($canalSelecionado == $canal)>
                                                        {{ $canal }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class='form-group row'>
                                        <label for='vendedores' class='col-md-3 label-control'>Vendedor</label>
                                        <div class='col-sm-4'>
                                            <select class='form-control select2' name='vendedor_id' id='vendedores'>
                                                <option value=''>Selecione um vendedor</option>
                                                @foreach ($vendedores as $vendedor)
                                                    <option value="{{ $vendedor->id }}"
                                                        {{ old('vendedor_id', $reserva->vendedor_id ?? '') == $vendedor->id ? 'selected' : '' }}>
                                                        {{ $vendedor->nome }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" id="campoObservacoes">
                                        <label for="observacao" class="col-md-3 label-control">Observações</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="observacoes" rows="3" id="observacoes">{{ old('observacoes', $reserva->observacoes ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Área de Logs -->
                                    <div class="card mt-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">LOGS
                                                DE ATIVIDADES</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Data/Hora</th>
                                                            <th>Usuário</th>
                                                            <th>Ação</th>
                                                            <th>Detalhes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="logs-table-body">
                                                        @isset($logs)
                                                            @php
                                                                $logsDaReserva = collect($logs)->filter(function (
                                                                    $log,
                                                                ) use ($reserva) {
                                                                    return $log->reserva_id == $reserva->id;
                                                                });
                                                            @endphp
                                                        @endisset

                                                        @if (isset($reserva) && $logsDaReserva->isNotEmpty())
                                                            {{-- @dd($logs) --}}
                                                            @foreach ($logs as $log)
                                                                <tr>
                                                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                                                    <td>{{ $log->usuario->name ?? 'Sistema' }}</td>
                                                                    <td>
                                                                        <span
                                                                            class="badge 
                                                                            @if ($log->tipo == 'criacao') badge-success
                                                                            @elseif($log->tipo == 'edicao') badge-info
                                                                            @elseif($log->tipo == 'exclusao') badge-danger
                                                                            @else badge-secondary @endif">
                                                                            {{ ucfirst($log->tipo) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $log->descricao }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="4" class="text-center">Nenhum log
                                                                    disponível</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <a href="{{ route('reserva.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Voltar
                                        </a>
                                        @if (isset($reserva))
                                            <!-- Botão Gerar Voucher (apenas para reserva ou pre-reserva) -->
                                            @if (isset($reserva) && in_array($reserva->situacao, ['reserva', 'pre-reserva']))
                                                <a href="{{ route('reservas.voucher', $reserva->id) }}"
                                                    class="btn btn-info" target="_blank">
                                                    <i class="fa fa-file-pdf-o"></i> Gerar Voucher
                                                </a>

                                                <a href="{{ route('reserva.enviarVoucher', $reserva->id) }}"
                                                    class="btn btn-success">
                                                    <i class="fas fa-envelope"></i> Enviar por Email
                                                </a>
                                            @endif

                                            <a href="{{ route('reserva.fnrh', $reserva->id) }}" class="btn btn-warning"
                                                target="_blank">
                                                <i class="fas fa-address-card"></i> Emitir FNRH
                                            </a>
                                            <!-- Botão No Show -->
                                            @if (isset($reserva) && $reserva->situacao === 'reserva')
                                                <button type="button" class="btn btn-cancelar-noshow" id="btn-noshow"
                                                    data-reserva-id="{{ $reserva->id }}"
                                                    data-url="{{ route('reservas.noshow.supervisor', $reserva->id) }}">
                                                    <i class="fas fa-user-slash"></i> No Show
                                                </button>
                                            @endif
                                            <!-- Botão Cancelar (substitui o Excluir) -->
                                            @if (in_array($reserva->situacao, ['pre-reserva']))
                                                <button type="button" class="btn btn-danger btn-excluir-reserva-super"
                                                    data-url="{{ route('reservas.cancelar.supervisor', $reserva->id) }}"
                                                    data-row="#reserva-{{ $reserva->id }}">
                                                    Cancelar Reserva
                                                </button>
                                            @endif

                                            <!-- Botão Hospedar (aparece quando é o dia do check-in) -->
                                            @if (isset($reserva) && $reserva->situacao === 'reserva' && $reserva->data_checkin <= date('Y-m-d'))
                                                <button type="button" class="btn btn-danger" id="btn-hospedar"
                                                    data-reserva-id="{{ $reserva->id }}">
                                                    <i class="fas fa-bed"></i> Check-in
                                                </button>
                                            @endif

                                            <!-- Botão Finalizar (aparece quando está hospedado) -->
                                            @if (isset($reserva) && $reserva->situacao === 'hospedado')
                                                <button type="button" class="btn btn-success" id="btn-finalizar"
                                                    data-reserva-id="{{ $reserva->id }}">
                                                    <i class="fas fa-check-circle"></i> Check-out
                                                </button>
                                            @endif

                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Atualizar
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Salvar
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <!-- Aba 2: Produtos -->
                            <div class="tab-pane fade" id="produtos" role="tabpanel" aria-labelledby="produtos-tab">
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary" id="btn-adicionar-produto-card"
                                        onclick="mostrarFormulario('produto')">
                                        <i class="fas fa-plus"></i> Adicionar Produto
                                    </button>
                                </div>

                                <div class="row" id="produtos-container">
                                    @if (isset($reserva) && isset($produtos_reserva))
                                        @foreach ($produtos_reserva as $produto)
                                            <div class="col-md-4 mb-3">
                                                <div class="card produto-card">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="produto-icon mr-3">
                                                                <i class="fas fa-box fa-2x text-primary"></i>
                                                            </div>
                                                            <h5 class="card-title mb-0">{{ $produto->descricao }}</h5>
                                                        </div>
                                                        <div class="produto-info">
                                                            <p class="card-text mb-1">
                                                                <strong>Quantidade:</strong> {{ $produto->quantidade }}
                                                            </p>
                                                            <p class="card-text mb-1">
                                                                <strong>Valor unitário:</strong> R$
                                                                {{ number_format($produto->valor_unitario, 2, ',', '.') }}
                                                            </p>
                                                            <p class="card-text mb-1">
                                                                <strong>Total:</strong> R$
                                                                {{ number_format($produto->valor_total, 2, ',', '.') }}
                                                            </p>
                                                            <p class="card-text mb-0 text-muted">
                                                                <small>Adicionado em:
                                                                    {{ $produto->created_at->format('d/m/Y H:i') }}</small>
                                                            </p>
                                                        </div>
                                                        <div class="mt-3 text-right">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger btn-remover-item"
                                                                data-id="item_{{ $produto->id }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Formulário para adicionar produto (movido da coluna lateral para a aba) -->
                                <div id="form-transacao-produto" style="display: none;" class="card mt-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0" id="titulo-form-produto">Adicionar Produto</h5>
                                    </div>
                                    <div class="card-body">
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
                                                            {{ number_format($produto->preco_venda, 2, ',', '.') }}
                                                        </option>
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
                                                <input type="text" class="form-control" id="total_item_produto"
                                                    readonly value="0,00">
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
                                                id="btn-salvar-produtos">Salvar Produtos</button>
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                id="btn-cancelar-produtos">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral (Resumo e Atividades) -->
            <div class='col-lg-4'>
                <!-- Card de Resumo Detalhado -->
                <div class='card shadow-sm mb-4'>
                    <div class='card-header bg-light'>
                        <h5 class='mb-0 text-uppercase text-muted' style='letter-spacing: 1px;'>RESUMO</h5>
                    </div>
                    <div class='card shadow-sm mb-4'>
                        <div class='card-header bg-light'>
                            <h5 class='mb-0 text-uppercase text-muted' style='letter-spacing: 1px;'>FALTA LANÇAR</h5>
                            <h2 class='text-danger mb-0' id='falta-lancar'>R$ 0,00</h2>
                            <input type='hidden' id='valor-falta-lancar' name='falta_lancar' value='0.00'>
                        </div>
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
                @if (isset($reserva) && !in_array($reserva->situacao, ['finalizada', 'cancelado', 'noshow']))
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
                                    <input type='text' class='form-control' id='valor_transacao' placeholder='0,00'>
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
                                    <label for="comprovante_transacao">Anexar Comprovante (Opcional)</label>
                                    <input type="file" class="form-control-file" id="comprovante_transacao">
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
                        </div>
                    </div>
                @elseif (isset($reserva))
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
            </div>
        </div>
    </div>

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

        /* Estilos para os cards de produtos */
        .produto-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }

        .produto-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .produto-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(0, 123, 255, 0.1);
        }

        .produto-info {
            margin-top: 1rem;
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

        .badge-noshow {
            background-color: #F48FB1;
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

        .btn-cancelar-noshow {
            background-color: #6A1B9A;
            color: #fff
        }

        #btn-cancelar-reserva:hover {
            background-color: #4c0a7a;
        }

        #placa_veiculo {
            text-transform: uppercase;
        }

        /* Área de seleção principal - Fundo branco com borda verde */
        .select2-selection--multiple {
            min-height: 38px !important;
            border: 1px solid #679A4C !important;
            border-radius: 4px !important;
            background-color: white !important;
            /* Fundo branco */
            color: #495057 !important;
            /* Texto preto padrão */
            padding: 0 5px !important;
        }

        /* Tags dos itens selecionados - Fundo verde com texto branco */
        .select2-selection--multiple .select2-selection__choice {
            background-color: #679A4C !important;
            border-color: #55853a !important;
            color: white !important;
            padding: 0 8px;
            border-radius: 12px;
            margin-top: 5px;
        }

        /* Texto do placeholder */
        .select2-selection--multiple .select2-search__field {
            color: #495057 !important;
        }

        .select2-selection--multiple .select2-search__field::placeholder {
            color: #6c757d !important;
        }

        /* Botão de remover item (mantém branco) */
        .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.7) !important;
            margin-right: 4px;
        }

        .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white !important;
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
            // 1. Plugins que ESTÃO funcionando (Select2)
            $('.select2').select2({
                placeholder: 'selecione...',
                allowClear: true,
                width: '100%'
            });

            // 2. Máscaras que ESTÃO funcionando (JQuery Mask)
            $("#telefone").mask("(00) 00000-0000");
            $("#valor_diaria").mask("#.##0,00", {
                reverse: true
            });
            $("#valor_transacao").mask("#.##0,00", {
                reverse: true
            });

            function validarCapacidadeQuarto() {
                // Pega o objeto da opção selecionada
                const quartoOption = $('#quarto').find(':selected');

                // Se não tiver quarto selecionado, não valida
                if (!quartoOption.length || !quartoOption.val()) return;

                const maxOcupantes = parseInt(quartoOption.data('ocupantes')) || 0;
                const nAdultos = parseInt($('#n_adultos').val()) || 0;
                const nCriancas = parseInt($('#n_criancas').val()) || 0;
                const totalPessoas = nAdultos + nCriancas;

                if (maxOcupantes > 0 && totalPessoas > (maxOcupantes + 10)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Capacidade Excedida',
                        text: `A capacidade máxima deste quarto (Categoria: ${maxOcupantes} pessoas) foi excedida.`,
                        confirmButtonText: 'Corrigir'
                    });

                    // Opcional: Ajustar automaticamente para o máximo permitido
                    // Se adultos já estourou, reduz adultos. Se foi a soma com criança, zera criança.
                    if (nAdultos > maxOcupantes) {
                        $('#n_adultos').val(maxOcupantes);
                        $('#n_criancas').val(0);
                    } else {
                        // Mantém adultos e reduz crianças para o que sobrar
                        $('#n_criancas').val(maxOcupantes - nAdultos);
                    }
                }
            }

            // Adiciona os ouvintes de evento nos campos relevantes
            $('#n_adultos, #n_criancas').on('change blur', function() {
                validarCapacidadeQuarto();
            });

            // O evento de mudança de quarto já existe no seu código, 
            // mas vamos garantir que a validação rode também
            $('#quarto').on('change', function() {
                // O seu código atual já preenche n_adultos com o padrão.
                // Vamos dar um pequeno delay para garantir que o valor padrão entrou antes de validar
                setTimeout(validarCapacidadeQuarto, 100);
            });

            // --- INÍCIO DA SOLUÇÃO PARA PLACA ---

            // 3. Função de formatação manual para Placa
            // (Baseada na sua função, mas com a lógica de formatação refinada)
            function formatarPlacaManual(valor) {
                let limpa = valor.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

                if (limpa.length > 7) {
                    limpa = limpa.substring(0, 7);
                }

                let resultado = '';
                // Validação caractere a caractere para garantir o formato
                for (let i = 0; i < limpa.length; i++) {
                    const char = limpa[i];

                    if (i < 3) { // Posições 0, 1, 2 (AAA)
                        if (/[A-Z]/.test(char)) {
                            resultado += char;
                        }
                    } else if (i === 3) { // Posição 3 (Número)
                        if (/[0-9]/.test(char)) {
                            resultado += char;
                        }
                    } else if (i === 4) { // Posição 4 (Híbrido - Letra ou Número)
                        if (/[A-Z0-9]/.test(char)) {
                            resultado += char;
                        }
                    } else { // Posições 5, 6 (Números)
                        if (/[0-9]/.test(char)) {
                            resultado += char;
                        }
                    }
                }

                // Adiciona o hífen
                if (resultado.length > 3) {
                    resultado = resultado.slice(0, 3) + '-' + resultado.slice(3);
                }

                return resultado;
            }

            var placaInput = $("#placa_veiculo");

            // 4. Limpa qualquer máscara ou listener conflitante
            // Esta é a parte mais importante!
            try {
                placaInput.unmask(); // Remove o plugin JQuery Mask (importante!)
            } catch (e) {}
            placaInput.off('input'); // Remove qualquer listener 'input' anterior

            // 5. Aplica o novo listener de formatação manual
            placaInput.on('input', function() {
                const valor = $(this).val();
                const formatado = formatarPlacaManual($(this).val());
                $(this).val(formatado);
            });

            // --- FIM DA SOLUÇÃO PARA PLACA ---

        }); // Fim do $(document).ready
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
                } else if (situacao === 'finalizada' || situacao === 'cancelado' || situacao === 'noshow') {
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
                            atualizarProdutosCards(); // Atualizar cards de produtos
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

                    // Mudar para a aba de produtos
                    $('#produtos-tab').tab('show');
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
                const comprovanteFile = $('#comprovante_transacao')[0].files[0];

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

                const formData = new FormData();
                formData.append('descricao', descricao);
                formData.append('valor', valor);
                formData.append('forma_pagamento_id', formaPagamentoId);
                formData.append('data_pagamento', dataTransacao);
                formData.append('observacoes', observacoes);
                formData.append('categoria', categoria);
                formData.append('tipo', tipo);
                formData.append('reserva_id', reservaId);

                if (comprovanteFile) {
                    formData.append('comprovante', comprovanteFile);
                }

                $.ajax({
                    url: '/transacoes',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
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
                $('#comprovante_transacao').val(null);
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

                    let linkComprovante = '';
                    if (transacao.comprovante_path) {
                        // Use a URL do storage
                        let urlComprovante = '{{ asset('storage') }}/' + transacao.comprovante_path;
                        linkComprovante = `
                        <a href="${urlComprovante}" target="_blank" class="btn btn-sm btn-outline-info float-right" style="margin-left: 5px;" title="Ver Comprovante">
                            <i class="fas fa-file-alt"></i>
                        </a>
                    `;
                    }

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
                                <span>${transacao.descricao}</span>
                                <span class="text-${transacao.tipo === 'desconto' ? 'danger' : 'success'}">${transacao.tipo === 'desconto' ? '-' : ''}${valorFormatado}</span>
                            </div>
                            <div class="atividade-details">
                                <span class="badge ${badgeClass}">${transacao.categoria}</span>
                                ${formaPagamento} • ${dataFormatada}

                                @if (!isset($reserva) || !in_array($reserva->situacao ?? '', ['finalizada', 'cancelado', 'noshow']))
                                    ${linkComprovante} 
                                    <button class="btn btn-sm btn-outline-danger float-right btn-remover-transacao" data-id="${transacao.id}">
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
                                    @if (!isset($reserva) || !in_array($reserva->situacao ?? '', ['finalizada', 'cancelado', 'noshow']))
                                        
                                        ${linkComprovante} 
                                        
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

                // Atualizar os cards de produtos na aba de produtos
                atualizarProdutosCards();
            }

            // Função para atualizar os cards de produtos na aba de produtos
            function atualizarProdutosCards() {
                const $produtosContainer = $('#produtos-container');
                $produtosContainer.empty();

                // Filtrar apenas os itens de produto
                const produtosItens = transacoes.filter(t => t.tipo === 'item' && t.status);

                if (produtosItens.length === 0) {
                    $produtosContainer.html(
                        '<div class="col-12"><div class="alert alert-info">Nenhum produto adicionado à reserva.</div></div>'
                    );
                    return;
                }

                produtosItens.forEach(function(item) {
                    const valorUnitario = parseFloat(item.valor) / parseInt(item.quantidade);
                    const valorFormatado = valorUnitario.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2
                    });
                    const totalFormatado = parseFloat(item.valor).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2
                    });
                    const dataFormatada = moment(item.data_pagamento).format('DD/MM/YYYY HH:mm');

                    const cardHtml = `
                        <div class="col-md-4 mb-3">
                            <div class="card produto-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="produto-icon mr-3">
                                            <i class="fas fa-box fa-2x text-primary"></i>
                                        </div>
                                        <h5 class="card-title mb-0">${item.produto_nome || item.descricao.replace('Produto: ', '')}</h5>
                                    </div>
                                    <div class="produto-info">
                                        <p class="card-text mb-1">
                                            <strong>Quantidade:</strong> ${item.quantidade}
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>Valor unitário:</strong> R$ ${valorFormatado}
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>Total:</strong> R$ ${totalFormatado}
                                        </p>
                                        <p class="card-text mb-0 text-muted">
                                            <small>Adicionado em: ${dataFormatada}</small>
                                        </p>
                                    </div>
                                    <div class="mt-3 text-right">
                                        @if (!isset($reserva) || !in_array($reserva->situacao ?? '', ['finalizada', 'cancelado', 'noshow']))
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-item" 
                                                    data-id="${item.id}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $produtosContainer.append(cardHtml);
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
                console.log("valor recebido", recebido)
                console.log("valor restante", restante)

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
                                    timer: 2000
                                }).then(() => {
                                    window.location.href = '/reserva';
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
                                text: 'Erro ao finalizar reserva: ' + xhr.responseJSON
                                    .message,
                                icon: 'error'
                            });
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
                                    '" data-ocupantes="' + quarto
                                    .ocupantes + '">' + quarto.nome +
                                    '</option>');
                            });

                            $('#quarto').on('change', function() {
                                // Pega o atributo data-ocupantes da opção selecionada
                                const ocupantes = $(this).find(':selected')
                                    .data('ocupantes');

                                // Se tiver valor, atualiza o campo n_adultos
                                if (ocupantes) {
                                    $('#n_adultos').val(ocupantes);
                                }
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



        // Inicialização das abas
        $('#reservaTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-excluir-reserva-super');
            if (!btn) return;

            const alvoURL = btn.dataset.url; // ex.: /reservas/123/cancelar-supervisor
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const rowSel = btn.dataset.row || null; // opcional: seletor da linha/card pra remover sem reload

            if (!alvoURL) {
                console.error('data-url ausente no botão .btn-excluir-reserva-super');
                return;
            }

            const result = await Swal.fire({
                title: 'Autorização do Supervisor',
                input: 'password',
                inputLabel: 'Senha do supervisor',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocomplete: 'new-password'
                },
                inputValidator: (value) => {
                    if (!value) return 'Informe a senha do supervisor.';
                },
                showCancelButton: true,
                confirmButtonText: 'Autorizar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: async (senha) => {
                    try {
                        const resp = await fetch(alvoURL, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                senha_supervisor: senha
                            })
                        });

                        // tenta parsear sempre p/ capturar msg do backend
                        let data = {};
                        try {
                            data = await resp.json();
                        } catch (_) {}

                        if (!resp.ok || data.success === false) {
                            const msg = data?.message || (resp.status === 403 ? 'Senha inválida.' :
                                resp.status === 419 ? 'Sessão expirada. Atualize a página.' :
                                'Falha na autorização.');
                            throw new Error(msg);
                        }
                        return data; // passa pro then em result.value
                    } catch (err) {
                        Swal.showValidationMessage(err.message);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed) {
                const okMsg = result.value?.message || 'Operação realizada com sucesso.';
                // sucesso — ou remove a linha/card, ou recarrega
                if (rowSel) {
                    const row = document.querySelector(rowSel);
                    if (row) row.remove();
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelada!',
                        text: okMsg,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                            icon: 'success',
                            title: 'Cancelada!',
                            text: okMsg
                        })
                        .then(() => location.reload());
                }
            }
        });


        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-cancelar-noshow');
            if (!btn) return;

            const alvoURL = btn.dataset.url || `/reservas/${btn.dataset.reservaId}/noshow-supervisor`;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            const result = await Swal.fire({
                title: 'Autorização do Supervisor',
                text: 'Digite a senha do supervisor para marcar No Show.',
                input: 'password',
                inputLabel: 'Senha do supervisor',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocomplete: 'new-password'
                },
                inputValidator: (v) => {
                    if (!v) return 'Informe a senha.';
                },
                showCancelButton: true,
                confirmButtonText: 'Autorizar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: async (senha) => {
                    try {
                        const resp = await fetch(alvoURL, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                senha_supervisor: senha
                            })
                        });

                        let data = {};
                        try {
                            data = await resp.json();
                        } catch (_) {}

                        if (!resp.ok || data.success === false) {
                            const msg = data?.message || (resp.status === 403 ? 'Senha inválida.' :
                                resp.status === 419 ? 'Sessão expirada, atualize a página.' :
                                'Falha na autorização.');
                            throw new Error(msg);
                        }
                        return data;
                    } catch (err) {
                        Swal.showValidationMessage(err.message);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed) {
                const okMsg = result.value?.message || 'Operação realizada com sucesso.';
                Swal.fire({
                        icon: 'success',
                        title: 'No Show!',
                        text: okMsg
                    })
                    .then(() => location.reload());
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Inicializa o Select2 com configurações aprimoradas
            $('.select2-hospedes-sec').select2({
                placeholder: "Selecione hóspedes secundários",
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            const $primarySelect = $('#hospede_id');
            const $secondarySelect = $('#hospedes_secundarios');
            const $containerSecondary = $('#container-hospedes-secundarios');

            function gerenciarHospedesSecundarios() {
                // Pega o valor. Se o select estiver disabled, tenta pegar do input hidden se houver, ou do próprio select
                let primaryId = $primarySelect.val();

                // Caso esteja em modo de edição e o select principal esteja disabled/renomeado
                if (!primaryId && $('input[name="hospede_id"]').length) {
                    primaryId = $('input[name="hospede_id"]').val();
                }

                // 1. Controle de Visibilidade
                if (!primaryId) {
                    $containerSecondary.slideUp();
                    return; // Se não tem hóspede principal, esconde e para por aqui
                } else {
                    $containerSecondary.slideDown();
                }

                // 2. Controle de Opções (Remover o principal da lista secundária)

                // Primeiro, verifica se o principal está selecionado nos secundários e remove se estiver
                let selecionadosSecundarios = $secondarySelect.val() || [];
                if (selecionadosSecundarios.includes(primaryId)) {
                    // Filtra removendo o ID do principal
                    selecionadosSecundarios = selecionadosSecundarios.filter(id => id !== primaryId);
                    // Atualiza o valor do select2 e dispara o evento change
                    $secondarySelect.val(selecionadosSecundarios).trigger('change');
                }

                // Percorre as opções para desabilitar a que corresponde ao hóspede principal
                $secondarySelect.find('option').each(function() {
                    if ($(this).val() == primaryId) {
                        $(this).prop('disabled', true); // Desabilita no HTML
                    } else {
                        $(this).prop('disabled', false); // Habilita os outros
                    }
                });

                // É necessário reinicializar ou notificar o Select2 para renderizar os itens desabilitados corretamente
                // Mas apenas trigger('change.select2') muitas vezes não atualiza a visualização "disabled" na lista aberta
                // O Select2 v4 geralmente detecta a propriedade disabled automaticamente ao abrir o dropdown.
            }

            // Executa ao carregar a página (para casos de Edição ou Validation Errors)
            gerenciarHospedesSecundarios();

            // Executa sempre que o hóspede principal mudar
            $primarySelect.on('change', function() {
                gerenciarHospedesSecundarios();
            });
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

@extends('adminlte::page')

@section('title', 'Excursões')

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h1 class="mb-1">Excursões</h1>
            <p class="text-muted mb-0">Consulte as excursões cadastradas nos eventos.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('eventos.home') }}" class="btn btn-outline-secondary mr-1">
                <i class="fas fa-arrow-left mr-1"></i> Eventos
            </a>
            <a href="{{ route('eventos.excursoes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nova excursão
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Não foi possível registrar o recebimento:</strong>
            <ul class="mb-0 mt-2 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-user-friends"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Média de pessoas</span>
                    <span class="info-box-number">{{ number_format($resumo['media_pessoas_realizadas'], 0, ',', '.') }}</span>
                    <small class="text-muted">por excursão realizada</small>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="far fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Excursões agendadas</span>
                    <span class="info-box-number">{{ number_format($resumo['agendadas'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Excursões realizadas</span>
                    <span class="info-box-number">{{ number_format($resumo['realizadas'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Excursões canceladas</span>
                    <span class="info-box-number">{{ number_format($resumo['canceladas'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de pessoas</span>
                    <span class="info-box-number">{{ number_format($resumo['pessoas'], 0, ',', '.') }}</span>
                    <small class="text-muted">em excursões realizadas</small>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Saldo a receber</span>
                    <span class="info-box-number">R$ {{ number_format($resumo['saldo_a_receber'], 2, ',', '.') }}</span>
                    <small class="text-muted">das excursões não canceladas</small>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total recebido</span>
                    <span class="info-box-number">R$ {{ number_format($resumo['total_recebido'], 2, ',', '.') }}</span>
                    <small class="text-muted">confirmado nas movimentações do caixa</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body">
            <form action="{{ route('eventos.excursoes.index') }}" method="GET">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filtro-data-inicio">Data inicial</label>
                        <input
                            type="date"
                            class="form-control @error('data_inicio') is-invalid @enderror"
                            id="filtro-data-inicio"
                            name="data_inicio"
                            value="{{ $dataInicio }}"
                        >
                        @error('data_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filtro-data-fim">Data final</label>
                        <input
                            type="date"
                            class="form-control @error('data_fim') is-invalid @enderror"
                            id="filtro-data-fim"
                            name="data_fim"
                            value="{{ $dataFim }}"
                        >
                        @error('data_fim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filtro-status">Status</label>
                        <select class="form-control" id="filtro-status" name="status">
                            <option value="">Todos os status</option>
                            <option value="AGENDADO" @selected($status === 'AGENDADO')>Agendado</option>
                            <option value="EM_ANDAMENTO" @selected($status === 'EM_ANDAMENTO')>Em andamento</option>
                            <option value="REALIZADO" @selected($status === 'REALIZADO')>Realizado</option>
                            <option value="CANCELADO" @selected($status === 'CANCELADO')>Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="filtro-busca">Buscar</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input
                                type="search"
                                class="form-control"
                                id="filtro-busca"
                                name="busca"
                                value="{{ $busca }}"
                                placeholder="Código, descrição ou responsável"
                                maxlength="255"
                            >
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12 mb-0 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        @if ($status || $busca !== '' || $dataInicio || $dataFim)
                            <a href="{{ route('eventos.excursoes.index') }}" class="btn btn-outline-secondary ml-1">
                                <i class="fas fa-times mr-1"></i> Limpar filtro
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>Excursões cadastradas
                @if ($status)
                    <span class="badge badge-light ml-2">{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</span>
                @endif
                @if ($busca !== '')
                    <span class="badge badge-light ml-1">Busca: {{ $busca }}</span>
                @endif
                @if ($dataInicio || $dataFim)
                    <span class="badge badge-light ml-1">
                        Período:
                        {{ $dataInicio ? \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') : 'mais antiga' }}
                        a
                        {{ $dataFim ? \Carbon\Carbon::parse($dataFim)->format('d/m/Y') : 'mais futura' }}
                    </span>
                @endif
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Código</th>
                            <th>Data</th>
                            <th class="text-center">Pessoas</th>
                            <th>Responsável</th>
                            <th>Descrição</th>
                            <th class="text-center">Receber</th>
                            <th class="text-center">Status</th>
                            <th class="text-right pr-4">Valor</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($excursoes as $excursao)
                            <tr>
                                <td class="pl-4 align-middle text-muted">#{{ $excursao->id }}</td>
                                <td class="align-middle">
                                    <i class="far fa-calendar-alt text-primary mr-2"></i>
                                    {{ $excursao->data->format('d/m/Y') }}
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-info px-2 py-1">{{ number_format($excursao->qtd_pessoas, 0, ',', '.') }}</span>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-semibold">{{ $excursao->responsavel }}</div>
                                    <small class="text-muted"><i class="fas fa-phone mr-1"></i>{{ $excursao->telefone_responsavel }}</small>
                                </td>
                                <td class="align-middle descricao-excursao">{{ $excursao->descricao }}</td>
                                @php
                                    $statusClass = match ($excursao->status) {
                                        'REALIZADO' => 'badge-success',
                                        'CANCELADO' => 'badge-danger',
                                        'EM_ANDAMENTO' => 'badge-primary',
                                        default => 'badge-warning',
                                    };
                                    $recebidoCaixa = (float) $excursao->recebimentos
                                        ->whereNotNull('fluxo_caixa_id')
                                        ->whereNull('fluxo_cancelamento_id')
                                        ->sum('valor');
                                    $saldoExcursao = max((float) $excursao->total - $recebidoCaixa, 0);
                                @endphp
                                <td class="align-middle text-center text-nowrap">
                                    @if ($excursao->status !== 'CANCELADO' && $saldoExcursao > 0.009)
                                        <button type="button" class="btn btn-sm btn-outline-success btn-receber-excursao"
                                            data-toggle="modal" data-target="#modalReceberExcursao{{ $excursao->id }}"
                                            title="Receber pagamento" aria-label="Receber pagamento da excursão #{{ $excursao->id }}">
                                            <i class="fas fa-hand-holding-usd mr-1"></i>Receber
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge {{ $statusClass }} px-2 py-1">{{ ucfirst(strtolower(str_replace('_', ' ', $excursao->status))) }}</span>
                                </td>
                                <td class="align-middle text-right font-weight-bold pr-4">
                                    R$ {{ number_format($excursao->total, 2, ',', '.') }}
                                </td>
                                <td class="align-middle text-center text-nowrap">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary btn-visualizar-excursao"
                                        data-excursao-id="{{ $excursao->id }}"
                                        data-toggle="modal"
                                        data-target="#modalVisualizarExcursao{{ $excursao->id }}"
                                        title="Visualizar excursão"
                                        aria-label="Visualizar excursão #{{ $excursao->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @unless (in_array($excursao->status, ['REALIZADO', 'CANCELADO'], true))
                                    @if ($excursao->status === 'AGENDADO')
                                        @if (! $excursao->data->isToday())
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                title="A excursão só pode ser iniciada em {{ $excursao->data->format('d/m/Y') }}"
                                                aria-label="Início disponível somente em {{ $excursao->data->format('d/m/Y') }}">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @elseif ($saldoExcursao > 0.009)
                                            <button type="button" class="btn btn-sm btn-warning btn-receber-para-iniciar"
                                                data-toggle="modal" data-target="#modalReceberExcursao{{ $excursao->id }}"
                                                data-saldo="{{ number_format($saldoExcursao, 2, '.', '') }}"
                                                title="Receber saldo e iniciar excursão"
                                                aria-label="Receber saldo e iniciar excursão #{{ $excursao->id }}">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('eventos.excursoes.start', $excursao) }}" method="POST"
                                                class="d-inline form-iniciar-excursao" data-excursao="{{ $excursao->descricao }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" title="Iniciar excursão" aria-label="Iniciar excursão #{{ $excursao->id }}"><i class="fas fa-play"></i></button>
                                            </form>
                                        @endif
                                    @elseif ($excursao->status === 'EM_ANDAMENTO')
                                        <form action="{{ route('eventos.excursoes.finish', $excursao) }}" method="POST"
                                            class="d-inline form-finalizar-excursao" data-excursao="{{ $excursao->descricao }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success" title="Finalizar excursão" aria-label="Finalizar excursão #{{ $excursao->id }}"><i class="fas fa-flag-checkered"></i></button>
                                        </form>
                                    @endif
                                        <a href="{{ route('eventos.excursoes.edit', $excursao) }}" class="btn btn-sm btn-outline-primary" title="Editar excursão" aria-label="Editar excursão #{{ $excursao->id }}">
                                            <i class="fas fa-edit"></i>
                                        </a>


                                        @if ($excursao->status !== 'CANCELADO')
                                            <form
                                                action="{{ route('eventos.excursoes.destroy', $excursao) }}"
                                                method="POST"
                                                class="d-inline form-cancelar-excursao"
                                                data-excursao="{{ $excursao->descricao }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar excursão" aria-label="Cancelar excursão #{{ $excursao->id }}"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-bus fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-3">Nenhuma excursão cadastrada.</p>
                                    <a href="{{ route('eventos.excursoes.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Cadastrar excursão
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($excursoes->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $excursoes->links('pagination::bootstrap-4') }}</div>
            </div>
        @endif
    </div>

    @foreach ($excursoes as $excursao)
        @include('eventos.excursoes.partials.visualizar', ['excursao' => $excursao])
        @php
            $saldoModalReceber = max((float) $excursao->total - (float) $excursao->recebimentos
                ->whereNotNull('fluxo_caixa_id')
                ->whereNull('fluxo_cancelamento_id')
                ->sum('valor'), 0);
        @endphp
        @if ($excursao->status !== 'CANCELADO' && $saldoModalReceber > 0.009)
            @include('eventos.excursoes.partials.receber', ['excursao' => $excursao])
        @endif
    @endforeach
@stop

@section('css')
    <style>
        .descricao-excursao {
            min-width: 220px;
            max-width: 360px;
            white-space: normal;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function exibirValorRecebimento(campo, digitos) {
                const oculto = document.getElementById(campo.dataset.moneyTarget);
                if (!oculto) return;
                if (!digitos) {
                    campo.value = '';
                    oculto.value = '';
                    return;
                }

                const valor = Number(digitos) / 100;
                campo.value = valor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                oculto.value = valor.toFixed(2);
            }

            document.addEventListener('input', function(event) {
                const campo = event.target.closest('.recebimento-excursao-valor-display');
                if (!campo) return;
                const digitos = campo.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').slice(-10);
                exibirValorRecebimento(campo, digitos);
            });

            document.addEventListener('keydown', function(event) {
                const campo = event.target.closest('.recebimento-excursao-valor-display');
                if (!campo) return;
                const oculto = document.getElementById(campo.dataset.moneyTarget);
                let digitos = oculto?.value ? String(Math.round(Number(oculto.value) * 100)) : '';

                if (/^\d$/.test(event.key)) {
                    event.preventDefault();
                    if (campo.selectionStart !== campo.selectionEnd) digitos = '';
                    exibirValorRecebimento(campo, (digitos + event.key).replace(/^0+(?=\d)/, '').slice(-10));
                } else if (event.key === 'Backspace' || event.key === 'Delete') {
                    event.preventDefault();
                    exibirValorRecebimento(campo, campo.selectionStart !== campo.selectionEnd ? '' : digitos.slice(0, -1));
                }
            });

            function atualizarComprovanteRecebimento(select) {
                const form = select.closest('form');
                const arquivo = form.querySelector('input[name="comprovante"]');
                const obrigatorio = select.selectedOptions[0]?.dataset.exigeComprovante === '1';
                arquivo.required = obrigatorio;
                form.querySelector('.comprovante-recebimento-obrigatorio')?.classList.toggle('d-none', !obrigatorio);
            }

            document.querySelectorAll('.forma-pagamento-recebimento').forEach(function(select) {
                select.addEventListener('change', function() { atualizarComprovanteRecebimento(select); });
                atualizarComprovanteRecebimento(select);
            });

            document.querySelectorAll('.btn-receber-excursao').forEach(function(botao) {
                botao.addEventListener('click', function() {
                    const modal = document.querySelector(botao.dataset.target);
                    modal.querySelector('.iniciar-apos-recebimento').value = '0';
                    modal.querySelector('.titulo-modal-recebimento').innerHTML = '<i class="fas fa-hand-holding-usd mr-2"></i>Receber excursão';
                    modal.querySelector('.confirmar-recebimento').innerHTML = '<i class="fas fa-save mr-1"></i>Registrar recebimento';
                });
            });

            document.querySelectorAll('.btn-receber-para-iniciar').forEach(function(botao) {
                botao.addEventListener('click', function() {
                    const modal = document.querySelector(botao.dataset.target);
                    const campo = modal.querySelector('.recebimento-excursao-valor-display');
                    const centavos = String(Math.round(Number(botao.dataset.saldo) * 100));
                    modal.querySelector('.iniciar-apos-recebimento').value = '1';
                    modal.querySelector('.titulo-modal-recebimento').innerHTML = '<i class="fas fa-play mr-2"></i>Receber saldo e iniciar excursão';
                    modal.querySelector('.confirmar-recebimento').innerHTML = '<i class="fas fa-play mr-1"></i>Receber e iniciar';
                    exibirValorRecebimento(campo, centavos);
                });
            });

            @if ($errors->any() && old('_receber_excursao_id'))
                $('#modalReceberExcursao{{ old('_receber_excursao_id') }}').modal('show');
            @endif

            document.querySelectorAll('.form-iniciar-excursao').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Iniciar excursão?',
                        text: form.dataset.excursao
                            ? `A excursão "${form.dataset.excursao}" será iniciada agora.`
                            : 'A excursão será iniciada agora.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-play mr-1"></i> Sim, iniciar',
                        cancelButtonText: 'Voltar',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            document.querySelectorAll('.form-finalizar-excursao').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Finalizar excursão?',
                        text: 'Após finalizar, a excursão não poderá mais ser alterada.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-flag-checkered mr-1"></i> Sim, finalizar',
                        cancelButtonText: 'Voltar',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            document.querySelectorAll('.form-cancelar-excursao').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const descricao = form.dataset.excursao;

                    Swal.fire({
                        title: 'Cancelar excursão?',
                        text: descricao
                            ? `A excursão "${descricao}" será marcada como cancelada.`
                            : 'A excursão será marcada como cancelada.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, cancelar',
                        cancelButtonText: 'Voltar',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@stop

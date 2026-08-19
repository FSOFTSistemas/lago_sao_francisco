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
                    <span class="info-box-text">Receita prevista</span>
                    <span class="info-box-number">R$ {{ number_format($resumo['receita_prevista'], 2, ',', '.') }}</span>
                    <small class="text-muted">em excursões agendadas</small>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Receita realizada</span>
                    <span class="info-box-number">R$ {{ number_format($resumo['receita_realizada'], 2, ',', '.') }}</span>
                    <small class="text-muted">em excursões realizadas</small>
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
                                <td class="align-middle text-center">
                                    @php
                                        $statusClass = match ($excursao->status) {
                                            'REALIZADO' => 'badge-success',
                                            'CANCELADO' => 'badge-danger',
                                            'EM_ANDAMENTO' => 'badge-primary',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-2 py-1">{{ ucfirst(strtolower(str_replace('_', ' ', $excursao->status))) }}</span>
                                </td>
                                <td class="align-middle text-right font-weight-bold pr-4">
                                    R$ {{ number_format($excursao->valor, 2, ',', '.') }}
                                </td>
                                <td class="align-middle text-center text-nowrap">
                                    @if (in_array($excursao->status, ['REALIZADO', 'CANCELADO'], true))
                                        <a href="{{ route('eventos.excursoes.show', $excursao) }}" class="btn btn-sm btn-outline-secondary" title="Visualizar excursão" aria-label="Visualizar excursão #{{ $excursao->id }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @else
                                    @if ($excursao->status === 'AGENDADO')
                                        <form action="{{ route('eventos.excursoes.start', $excursao) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja iniciar esta excursão?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success" title="Iniciar excursão" aria-label="Iniciar excursão #{{ $excursao->id }}"><i class="fas fa-play"></i></button>
                                        </form>
                                    @elseif ($excursao->status === 'EM_ANDAMENTO')
                                        <form action="{{ route('eventos.excursoes.finish', $excursao) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja finalizar esta excursão? Após finalizar, ela não poderá ser alterada.');">
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
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
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

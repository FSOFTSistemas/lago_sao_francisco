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

    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body">
            <form action="{{ route('eventos.excursoes.index') }}" method="GET">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3 mb-md-0">
                        <label for="filtro-status">Status</label>
                        <select class="form-control" id="filtro-status" name="status">
                            <option value="">Todos os status</option>
                            <option value="AGENDADO" @selected($status === 'AGENDADO')>Agendado</option>
                            <option value="EM_ANDAMENTO" @selected($status === 'EM_ANDAMENTO')>Em andamento</option>
                            <option value="REALIZADO" @selected($status === 'REALIZADO')>Realizado</option>
                            <option value="CANCELADO" @selected($status === 'CANCELADO')>Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group col-md-5 mb-md-0">
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
                                placeholder="Descrição ou responsável"
                                maxlength="255"
                            >
                        </div>
                    </div>
                    <div class="form-group col-md-4 mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        @if ($status || $busca !== '')
                            <a href="{{ route('eventos.excursoes.index') }}" class="btn btn-outline-secondary ml-1">
                                <i class="fas fa-times mr-1"></i> Limpar filtro
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-bus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Excursões cadastradas</span>
                    <span class="info-box-number">{{ number_format($resumo['total'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de pessoas</span>
                    <span class="info-box-number">{{ number_format($resumo['pessoas'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor total</span>
                    <span class="info-box-number">R$ {{ number_format($resumo['valor'], 2, ',', '.') }}</span>
                </div>
            </div>
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
                                    @if ($excursao->status === 'REALIZADO')
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


                                        <form action="{{ route('eventos.excursoes.destroy', $excursao) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta excursão? Esta ação não poderá ser desfeita.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir excursão" aria-label="Excluir excursão #{{ $excursao->id }}"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-bus fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-3">Nenhuma excursão cadastrada.</p>
                                    <a href="{{ route('eventos.excursoes.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Cadastrar primeira excursão
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

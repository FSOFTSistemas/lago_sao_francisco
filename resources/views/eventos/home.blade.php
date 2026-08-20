@extends('adminlte::page')

@section('title', 'Eventos')

@section('content_header')
    <h1>Eventos</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h2>Cadastrar</h2>
                </div>
                <div class="icon">
                    <i class="fas fa-plus"></i>
                </div>
                <a href="{{ route('aluguel.create') }}" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h2>Excursão</h2>
                </div>
                <div class="icon">
                    <i class="fas fa-bus"></i>
                </div>
                <a href="{{ route('eventos.excursoes.index') }}" class="small-box-footer" aria-label="Consultar excursões">
                    Consultar <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h2>Planner</h2>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="{{ route('eventos.planner') }}" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h2>Relatórios</h2>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('aluguel.index') }}" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Próximos Eventos</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Espaço</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proximosEventos as $evento)
                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($evento->data_inicio)->format('d/m/Y') }}
                                @if($evento->data_fim && $evento->data_fim->format('Y-m-d') !== $evento->data_inicio->format('Y-m-d'))
                                    a {{ \Carbon\Carbon::parse($evento->data_fim)->format('d/m/Y') }}
                                @endif
                            </td>
                            <td>{{ $evento->espaco->nome ?? '-' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $evento->tipo ?? '-')) }}</td>
                            <td>{{ $evento->cliente->nome_razao_social ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $evento->status === 'pago' ? 'badge-success' : ($evento->status === 'cancelado' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($evento->status) }}
                                </span>
                            </td>
                            <td class="text-right">R$ {{ number_format($evento->total ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Nenhum evento agendado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

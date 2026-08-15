@extends('adminlte::page')

@section('title', 'Logs')

@section('content_header')
    <h1>Logs</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Histórico de ações</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Ação</th>
                            <th>Usuário</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->data_hora)->format('d/m/Y H:i') }}</td>
                                <td>{{ $log->tipo_acao }}</td>
                                <td>{{ $log->usuario->name ?? '-' }}</td>
                                <td>{{ $log->descricao }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Nenhum log encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
@stop

@extends('adminlte::page')

@section('title', 'Logs de Day Use')

@section('content_header')
    <h1>Logs de Exclusão de Day Use</h1>
    <hr>
@stop

@section('content')
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Data e Hora</th>
                    <th>Usuário</th>
                    <th>Supervisor</th>
                    <th>Ação</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->data_hora)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->usuario }}</td>
                        <td>{{ $log->supervisor }}</td>
                        <td>{{ $log->acao }}</td>
                        <td>{{ $log->observacao ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Nenhum log encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
@stop

@extends('adminlte::page')

@section('title', 'Notificações')

@section('content_header')
    <h1>Notificações</h1>
@stop

@section('content')
    <div class="report-card">
        <div class="report-toolbar">
            <p class="mb-0 text-muted">Histórico de notificações recebidas.</p>
            <form action="{{ route('notificacoes.marcar_todas_lidas') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-report-filter">
                    <i class="fas fa-check-double"></i> Marcar todas como lidas
                </button>
            </form>
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Notificação</th>
                        <th>Data</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notificacoes as $notificacao)
                        <tr style="{{ $notificacao->read_at ? '' : 'background-color:#f8faf7;' }}">
                            @php
                                $corHex = ['danger' => '#dc3545', 'warning' => '#e8590c', 'info' => '#1971c2', 'success' => '#3e7222'][$notificacao->data['cor'] ?? ''] ?? '#6c757d';
                            @endphp
                            <td class="text-center" style="width: 40px;">
                                <span class="report-avatar" style="background-color: {{ $corHex }};">
                                    <i class="{{ $notificacao->data['icone'] ?? 'fas fa-bell' }}"></i>
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('notificacoes.abrir', $notificacao->id) }}" style="text-decoration:none; color: inherit;">
                                    <strong>{{ $notificacao->data['titulo'] ?? 'Notificação' }}</strong>
                                    <div class="text-muted" style="font-size: .85rem;">{{ $notificacao->data['mensagem'] ?? '' }}</div>
                                </a>
                            </td>
                            <td class="text-muted" style="font-size: .85rem;">{{ $notificacao->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @if($notificacao->read_at)
                                    <span class="report-badge" style="background-color:#eee; color:#888;">Lida</span>
                                @else
                                    <span class="report-badge" style="background-color:#eef3ea; color:#3e7222;">Não lida</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="report-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <h5>Nenhuma notificação por aqui</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notificacoes->hasPages())
            <div class="p-3">
                {{ $notificacoes->links() }}
            </div>
        @endif
    </div>
@stop

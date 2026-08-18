@extends('adminlte::page')

@section('title', 'Relatórios de Eventos')

@section('content_header')
    <div class="events-report-heading">
        <div class="events-report-title">
            <span class="events-report-title-icon" aria-hidden="true">
                <i class="fas fa-file-alt"></i>
            </span>
            <div>
                <h1>Relatórios de Eventos</h1>
                <p>Consulte e gerencie os eventos cadastrados no sistema.</p>
            </div>
        </div>

        <div class="events-report-actions">
            <a href="{{ route('eventos.home') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Voltar
            </a>
            <a href="{{ route('aluguel.create') }}" class="btn btn-event-primary">
                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Novo evento
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="events-report-card">
        <div class="events-report-card-header">
            <div>
                <h2><i class="fas fa-calendar-check mr-2" aria-hidden="true"></i>Eventos cadastrados</h2>
                <p>Use a busca para localizar um cliente, espaço ou situação.</p>
            </div>
        </div>

        <div class="events-report-table">
            @component('components.data-table', [
                'responsive' => [
                    ['responsivePriority' => 1, 'targets' => 0],
                    ['responsivePriority' => 2, 'targets' => 1],
                    ['responsivePriority' => 3, 'targets' => 2],
                    ['responsivePriority' => 4, 'targets' => 3],
                    ['responsivePriority' => 5, 'targets' => -1],
                ],
                'itemsPerPage' => 10,
                'showTotal' => false,
                'valueColumnIndex' => 0,
            ])
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Espaço</th>
                        <th>Situação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aluguel as $evento)
                        @php
                            $statusClass = match ($evento->status) {
                                'pago' => 'status-paid',
                                'cancelado' => 'status-cancelled',
                                default => 'status-pending',
                            };
                        @endphp
                        <tr>
                            <td><span class="event-id">#{{ $evento->id }}</span></td>
                            <td class="event-client">{{ $evento->cliente->nome_razao_social ?? 'Cliente não informado' }}</td>
                            <td>
                                <span class="event-space">
                                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                    {{ $evento->espaco->nome ?? 'Espaço não informado' }}
                                </span>
                            </td>
                            <td>
                                <span class="event-status {{ $statusClass }}">
                                    <span class="event-status-dot" aria-hidden="true"></span>
                                    {{ ucfirst($evento->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="event-row-actions">
                                    <a href="{{ route('aluguel.edit', $evento->id) }}" class="btn event-action-edit"
                                        title="Editar evento" aria-label="Editar evento #{{ $evento->id }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>

                                    <button type="button" class="btn event-action-delete" data-toggle="modal"
                                        data-target="#deleteAluguelModal{{ $evento->id }}" title="Excluir evento"
                                        aria-label="Excluir evento #{{ $evento->id }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @include('aluguel.modals._delete', ['aluguel' => $evento])
                    @endforeach
                </tbody>
            @endcomponent
        </div>
    </div>
@stop

@section('css')
    <style>
        .events-report-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .25rem 0 .5rem;
        }

        .events-report-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .events-report-title-icon {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: linear-gradient(135deg, #679A4C, #3e7222);
            box-shadow: 0 6px 16px rgba(62, 114, 34, .2);
            color: #fff;
            font-size: 1.25rem;
        }

        .events-report-title h1 {
            margin: 0;
            color: #263238;
            font-size: 1.75rem;
            font-weight: 700;
        }

        .events-report-title p,
        .events-report-card-header p {
            margin: .2rem 0 0;
            color: #6c757d;
            font-size: .9rem;
        }

        .events-report-actions {
            display: flex;
            gap: .6rem;
        }

        .events-report-actions .btn {
            border-radius: 8px;
            padding: .5rem 1rem;
            font-weight: 600;
        }

        .btn-event-primary {
            background: #679A4C;
            border-color: #679A4C;
            color: #fff;
        }

        .btn-event-primary:hover,
        .btn-event-primary:focus {
            background: #3e7222;
            border-color: #3e7222;
            color: #fff;
        }

        .events-report-card {
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, .05);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        }

        .events-report-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #edf0f2;
        }

        .events-report-card-header h2 {
            margin: 0;
            color: #343a40;
            font-size: 1rem;
            font-weight: 700;
        }

        .events-report-card-header h2 i {
            color: #679A4C;
        }

        .events-report-table {
            padding: 1rem 1.25rem 1.25rem;
        }

        .event-id {
            color: #6c757d;
            font-weight: 700;
        }

        .event-client {
            color: #343a40;
            font-weight: 600;
        }

        .event-space {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .event-space i {
            color: #8a959e;
        }

        .event-status {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 20px;
            padding: .3rem .7rem;
            font-size: .78rem;
            font-weight: 700;
        }

        .event-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-paid {
            background: #eaf6e5;
            color: #3e7222;
        }

        .status-pending {
            background: #fff4dc;
            color: #a96600;
        }

        .status-cancelled {
            background: #fdebec;
            color: #c92a2a;
        }

        .event-row-actions {
            display: flex;
            justify-content: center;
            gap: .4rem;
        }

        .event-row-actions .btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 0;
        }

        .event-action-edit {
            border: 1px solid #e2c067;
            background: #fff8e5;
            color: #9a6700;
        }

        .event-action-edit:hover {
            background: #f0ad4e;
            color: #fff;
        }

        .event-action-delete {
            border: 1px solid #e6a5aa;
            background: #fff1f2;
            color: #c92a2a;
        }

        .event-action-delete:hover {
            background: #dc3545;
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .events-report-heading {
                align-items: stretch;
                flex-direction: column;
            }

            .events-report-title {
                align-items: flex-start;
            }

            .events-report-title h1 {
                font-size: 1.45rem;
            }

            .events-report-title-icon {
                width: 44px;
                height: 44px;
                flex-basis: 44px;
            }

            .events-report-actions .btn {
                flex: 1;
            }

            .events-report-table {
                padding: .75rem;
            }
        }
    </style>
@stop

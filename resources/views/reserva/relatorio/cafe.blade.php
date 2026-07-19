@extends('adminlte::page')

@section('title', 'Lista de Café da Manhã')

@section('content_header')
    <h1>Lista de Café da Manhã</h1>
@stop

@section('content')
    {{-- Cálculo dos Totais --}}
    @php
        $totalAdultos = $reservas->sum('n_adultos');
        // Soma crianças pagantes + não pagantes
        $totalCriancas = $reservas->sum('n_criancas') + $reservas->sum('n_criancas_nao_pagantes');
        $totalGeral = $totalAdultos + $totalCriancas;
        $totalAcomodacoes = $reservas->count();

        // Paleta de cores por categoria (ciclo estável por hash do nome, sem precisar cadastrar cor)
        $paletaCategorias = ['#3e7222', '#0d6efd', '#b8860b', '#6f42c1', '#0dcaf0', '#dc3545'];
    @endphp

    {{-- Cards de Resumo --}}
    <div class="stat-cards-row">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #679A4C, #3e7222);">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totalAcomodacoes }}</span>
                <span class="stat-label">Acomodações</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4dabf7, #1971c2);">
                <i class="fas fa-user"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totalAdultos }}</span>
                <span class="stat-label">Adultos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ffc078, #e8590c);">
                <i class="fas fa-child"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totalCriancas }}</span>
                <span class="stat-label">Crianças</span>
            </div>
        </div>
        <div class="stat-card stat-card-dark">
            <div class="stat-icon" style="background: linear-gradient(135deg, #495057, #212529);">
                <i class="fas fa-mug-hot"></i>
            </div>
            <div class="stat-body">
                <span class="stat-number">{{ $totalGeral }}</span>
                <span class="stat-label">Total de pessoas</span>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-toolbar">
            <form action="{{ route('relatorios.cafe') }}" method="GET" class="report-filter-form">
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" name="data_inicio" value="{{ $dataInicio }}">
                </div>
                <span class="report-date-sep">até</span>
                <div class="report-date-field">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" class="form-control" name="data_fim" value="{{ $dataFim }}">
                </div>
                <button type="submit" class="btn btn-report-filter">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </form>

            <a href="{{ route('relatorios.cafe.pdf', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}"
               target="_blank"
               class="btn btn-report-pdf">
                <i class="fas fa-file-pdf"></i> Imprimir PDF
            </a>
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Acomodação</th>
                        <th>Hóspede Principal</th>
                        <th>Hóspedes Secundários</th>
                        <th class="text-center">Pessoas</th>
                        <th>Período</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        @php
                            $totalCriancasReserva = $reserva->n_criancas + ($reserva->n_criancas_nao_pagantes ?? 0);
                            $totalPessoasReserva = $reserva->n_adultos + $totalCriancasReserva;
                            $categoriaNome = $reserva->quarto->categoria->titulo ?? 'Sem categoria';
                            $corCategoria = $paletaCategorias[crc32($categoriaNome) % count($paletaCategorias)];
                            $inicial = mb_strtoupper(mb_substr($reserva->hospede->nome ?? '?', 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column align-items-start" style="gap: .25rem;">
                                    <strong>{{ $reserva->quarto->nome ?? 'N/D' }}</strong>
                                    <span class="report-badge" style="background-color: {{ $corCategoria }}1a; color: {{ $corCategoria }};">
                                        {{ $categoriaNome }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center" style="gap: .6rem;">
                                    <span class="report-avatar" style="background-color: {{ $corCategoria }};">{{ $inicial }}</span>
                                    {{ $reserva->hospede->nome ?? 'Hóspede Removido' }}
                                </div>
                            </td>
                            <td class="text-muted">
                                {{ $reserva->nomes_hospedes_secundarios ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="report-number-badge">{{ $totalPessoasReserva }}</span>
                                <div class="report-subtext">{{ $reserva->n_adultos }} adt · {{ $totalCriancasReserva }} cri</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column" style="gap: .2rem; font-size: .82rem; color: #555;">
                                    <span><i class="fas fa-sign-in-alt text-success"></i> {{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</span>
                                    <span><i class="fas fa-sign-out-alt text-danger"></i> {{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="report-empty">
                                    <i class="fas fa-mug-hot"></i>
                                    <h5>Nenhuma reserva encontrada para este período</h5>
                                    <p>Ajuste o filtro de datas acima para ver outras diárias.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($reservas->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold">Total geral de pessoas</td>
                            <td class="text-center font-weight-bold">{{ $totalGeral }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@stop

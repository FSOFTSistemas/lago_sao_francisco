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
    @endphp

    {{-- Cards de Resumo --}}
    <div class="row">
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Adultos</span>
                    <span class="info-box-number" style="font-size: 1.5rem;">{{ $totalAdultos }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-child"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Crianças</span>
                    <span class="info-box-number" style="font-size: 1.5rem;">{{ $totalCriancas }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Geral</span>
                    <span class="info-box-number" style="font-size: 1.5rem;">{{ $totalGeral }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form action="{{ route('relatorios.cafe') }}" method="GET" class="form-inline">
                <div class="form-group mb-2">
                    <label for="data_inicio" class="sr-only">Data Início</label>
                    <input type="date" class="form-control" name="data_inicio" value="{{ $dataInicio }}">
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <label for="data_fim" class="sr-only">Data Fim</label>
                    <input type="date" class="form-control" name="data_fim" value="{{ $dataFim }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                
                <a href="{{ route('relatorios.cafe.pdf', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" 
                   target="_blank" 
                   class="btn btn-danger mb-2 ml-3">
                    <i class="fas fa-file-pdf"></i> Imprimir PDF
                </a>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Quarto</th>
                            <th>Hóspede Principal</th>
                            <th>Hóspedes Secundários</th>
                            <th class="text-center">Total Pessoas</th>
                            <th>Período</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservas as $reserva)
                            @php
                                // Soma local para exibição na linha
                                $totalCriancasReserva = $reserva->n_criancas + ($reserva->n_criancas_nao_pagantes ?? 0);
                                $totalPessoasReserva = $reserva->n_adultos + $totalCriancasReserva;
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <strong>{{ $reserva->quarto->nome ?? 'N/D' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $reserva->quarto->categoria->titulo ?? '' }}</small>
                                </td>
                                <td class="align-middle">
                                    {{ $reserva->hospede->nome ?? 'Hóspede Removido' }}
                                </td>
                                <td class="align-middle">
                                    {{ $reserva->nomes_hospedes_secundarios ?? '-' }}
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-info" style="font-size: 1rem;">
                                        {{ $totalPessoasReserva }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        ({{ $reserva->n_adultos }} Adt / {{ $totalCriancasReserva }} Cri)
                                    </small>
                                </td>
                                <td class="align-middle">
                                    <small>
                                        Check-in: {{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}<br>
                                        Check-out: {{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <h5>Nenhuma reserva encontrada para este período.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-secondary">
                            <td colspan="3" class="text-right font-weight-bold">Total Geral de Pessoas:</td>
                            <td class="text-center font-weight-bold">
                                {{ $totalGeral }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop
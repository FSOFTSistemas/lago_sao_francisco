@extends('adminlte::page')

@section('title', 'Mapa de Quartos')

@section('content_header')
<h1>Mapa de Quartos - {{ \Carbon\Carbon::now()->format('d/m/Y') }}</h1>
@stop

@section('content')
<div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
    <div class="d-flex align-items-center me-3">
        <span class="status-indicator" style="background-color: #679A4C;"></span> Disponível
    </div>
    <div class="d-flex align-items-center me-3">
        <span class="status-indicator" style="background-color: #FFFF00;"></span> Pré-reserva
    </div>
    <div class="d-flex align-items-center me-3">
        <span class="status-indicator" style="background-color: #FF0000;"></span> Hospedado
    </div>
    <div class="d-flex align-items-center me-3">
        <span class="status-indicator" style="background-color: #007BFF;"></span> Reservado
    </div>
    <div class="d-flex align-items-center me-3">
        <span class="status-indicator" style="background-color: #343A40;"></span> Data bloqueada
    </div>
</div>
    <div class="row">
        @foreach ($quartos as $quarto)
            @php
                $reserva = $quarto->reservaAtual();
                $situacao = $reserva->situacao ?? 'disponivel';

                $cores = [
                    'pre-reserva' => 'warning',
                    'reserva' => 'primary',
                    'hospedado' => 'danger',
                    'bloqueado' => 'dark',
                    'disponivel' => 'success'
                ];

                $badge = $cores[$situacao] ?? 'secondary';
                $nomeHospede = $reserva->hospede->nome ?? '';
                $periodo = $reserva ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '';
            @endphp

            <div class="col-md-3">
                <div class="card border-{{ $badge }}">
                    <div class="card-header bg-{{ $badge }} text-white">
                        <strong>{{ $quarto->nome }}</strong>
                    </div>
                    <div class="card-body">
                        <p><i class="fa fa-user-check"></i> - {{ $nomeHospede }}</p>
                        <p><i class="fa fa-calendar"></i>  - {{ $periodo }}</p>
                        <div class="botoes">
                            @if ($reserva)
                                <a href="{{ route('reserva.edit', $reserva->id) }}" class="btn btn-sm btn-outline-info">Atualizar Reserva</a>
                                @else
                                <a href="{{ route('reserva.create') }}" class="btn btn-sm btn-outline-secondary">Nova Reserva</a>
                            @endif
                            @if ($reserva && $reserva->hospede)
                                <a href="{{ route('hospede.edit', $reserva->hospede->id) }}" class="btn btn-sm btn-outline-info">Informações do Hóspede</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

@section('css')
<style>
    .status-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    margin-right: 8px;
}

.d-flex {
        display: flex;
    }

    .align-items-center {
        align-items: center;
    }

    .me-3 {
        margin-right: 1rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .gap-2 {
        gap: 0.5rem;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }
</style>
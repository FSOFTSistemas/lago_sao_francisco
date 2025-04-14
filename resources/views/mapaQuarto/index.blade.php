@extends('adminlte::page')

@section('title', 'Mapa de Quartos')

@section('content_header')
    <h1>Mapa de Quartos</h1>
@stop

@section('content')
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
                $nomeHospede = $reserva->hospede->nome ?? '-';
                $periodo = $reserva ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '-';
            @endphp

            <div class="col-md-3">
                <div class="card border-{{ $badge }}">
                    <div class="card-header bg-{{ $badge }} text-white">
                        <strong>{{ $quarto->nome }}</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Hóspede:</strong> {{ $nomeHospede }}</p>
                        <p><strong>Período:</strong> {{ $periodo }}</p>

                        @if ($reserva)
                            <a href="{{ route('reserva.edit', $reserva->id) }}" class="btn btn-sm btn-outline-primary">Atualizar Reserva</a>
                        @endif
                        @if ($reserva && $reserva->hospede)
                            <a href="{{ route('hospede.edit', $reserva->hospede->id) }}" class="btn btn-sm btn-outline-secondary">Informações do Hóspede</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

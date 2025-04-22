@extends('adminlte::page')

@section('title', 'Mapa de Reservas')

@section('content_header')
    <h1>Mapa de Reservas</h1>
    <hr>
@stop

@section('content')
<form method="GET" class="mb-3 d-flex gap-2">
    <input type="date" name="inicio" value="{{ request('inicio', now()->toDateString()) }}" class="form-control w-auto">
    <input type="date" name="fim" value="{{ request('fim', now()->addDays(7)->toDateString()) }}" class="form-control w-auto">
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<div class="table-responsive">
<table class="table mapa-reservas text-center">
    <thead>
        <tr>
            <th>Quarto</th>
            @foreach ($datas as $data)
                <th>{{ $data->format('d/m') }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($quartos as $quarto)
            <tr>
                <td class="quarto-nome">{{ $quarto->nome }}</td>
                @php $ultimaReservaId = null; @endphp
                @foreach ($datas as $data)
                    @php
                        $reserva = $quarto->reservas->first(function ($res) use ($data) {
                            return $data->between(
                                \Carbon\Carbon::parse($res->data_checkin),
                                \Carbon\Carbon::parse($res->data_checkout)->subDay()
                            );
                        });

                        $cor = match($reserva->situacao ?? '') {
                            'pre-reserva' => 'bg-warning',
                            'reserva' => 'bg-primary',
                            'hospedado' => 'bg-danger',
                            'bloqueado' => 'bg-dark',
                            default => 'bg-success',
                        };

                        $isSameReserva = $reserva && $reserva->id === $ultimaReservaId;
                        $roundedLeft = $reserva && !$isSameReserva ? 'rounded-start' : '';
                        $nextData = $data->copy()->addDay();
                        $reservaNext = $quarto->reservas->first(function ($res) use ($nextData) {
                            return $nextData->between(
                                \Carbon\Carbon::parse($res->data_checkin),
                                \Carbon\Carbon::parse($res->data_checkout)->subDay()
                            );
                        });
                        $roundedRight = !$reservaNext || ($reservaNext->id ?? null) !== ($reserva->id ?? null) ? 'rounded-end' : '';
                        $cellClass = $reserva ? "$cor text-white $roundedLeft $roundedRight no-border" : '';
                    @endphp
                    <td class="{{ $cellClass }}">
                        @if ($reserva && !$isSameReserva)
                            <a href="{{ route('reserva.edit', $reserva->id) }}"
                                class="text-white d-block w-100 h-100"
                                data-bs-toggle="tooltip"
                                title="{{ $reserva->hospede->nome }}">
                                {{ \Illuminate\Support\Str::limit($reserva->hospede->nome, 10) }}
                            </a>
                        @endif
                    </td>
                    @php $ultimaReservaId = $reserva->id ?? null; @endphp
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
</div>
<div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
  <div class="d-flex align-items-center me-3">
      <span class="status-indicator" style="background-color: #ffc107;"></span> Pré-reserva
  </div>
  <div class="d-flex align-items-center me-3">
      <span class="status-indicator" style="background-color: #dc3545;"></span> Hospedado
  </div>
  <div class="d-flex align-items-center me-3">
      <span class="status-indicator" style="background-color: #007bff;"></span> Reservado
  </div>
  <div class="d-flex align-items-center me-3">
      <span class="status-indicator" style="background-color: #343A40;"></span> Data bloqueada
  </div>
</div>
@stop

@section('css')
<style>
    .mapa-reservas {
        border-collapse: separate;
        border-spacing: 0;
    }

    .mapa-reservas th,
    .mapa-reservas td {
        min-width: 40px;
        height: 32px;
        padding: 0;
        font-size: 13px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }

    .mapa-reservas td.no-border {
        border-left: none;
    }

    .mapa-reservas .quarto-nome {
        font-weight: bold;
        background-color: #f8f9fa;
        text-align: left;
        padding-left: 8px;
        border-right: 1px solid #dee2e6;
    }

    .bg-warning { background-color: #ffc107 !important; }
    .bg-primary { background-color: #007bff !important; }
    .bg-danger { background-color: #dc3545 !important; }
    .bg-dark { background-color: #343a40 !important; }
    .bg-success { background-color: #28a745 !important; }

    .rounded-start {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .rounded-end {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    a.text-white {
        color: white;
        text-decoration: none;
        padding: 6px;
    }

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
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@stop

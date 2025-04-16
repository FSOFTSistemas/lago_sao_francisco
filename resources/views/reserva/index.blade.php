@extends('adminlte::page')

@section('title', 'Reservas')

@section('content_header')
    <h5>Lista de Reservas</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('reserva.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Nova Reserva
        </a>
    </div>

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


                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
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
            <thead class="bg-primary text-white">
                <tr>
                    <th>Reserva/Hospedagem</th>
                    <th>Hóspede</th>
                    <th>Quarto</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Qtd.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservas as $reserva)
                    <tr>
                        <td>
                            <span class="status-indicator" style="background-color: {{ getReservaStatusColor($reserva->situacao) }};"></span>
                            <a id="editlink" href="{{ route('reserva.edit', $reserva->id) }}">
                            00000{{ $reserva->id }}
                            </a>
                        </td>
                        <td>{{ $reserva->hospede->nome }}</td>
                        <td>{{ $reserva->quarto->nome }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</td>
                        <td>
                            {{ $reserva->n_adultos }}
                            @if($reserva->n_criancas >= 1)
                            / ({{ $reserva->n_criancas }})
                            @else
                            
                            @endif
                        </td>
                    
                    </tr>
                @endforeach
            </tbody>
    @endcomponent
    @php
    function getReservaStatusColor($situacao) {
        switch ($situacao) {
            case 'bloqueado':
                return '#343A40';
            case 'reserva':
                return '#007BFF';
            case 'hospedado':
                return '#FF0000';
            case 'pre-reserva':
                return '#FFFF00';
            default:
                return '#808080';
        }
    }
@endphp

@stop

@section('css')
<style>
    #editlink {
        color: #679A4C;
        font-weight: 600;
    }
    #editlink:hover {
        color: #3e7222;
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
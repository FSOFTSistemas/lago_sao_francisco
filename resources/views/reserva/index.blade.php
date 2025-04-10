@extends('adminlte::page')

@section('title', 'Reservas')

@section('content_header')
    <h5>Lista de Reservas</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('reserva.create')}}" class="btn btn-success">
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

            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
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
                          <a id="editlink" href="{{ route('reserva.edit', $reserva->id) }}">
                          {{ $reserva->id }}</td>
                          </a>
                        <td>{{ $reserva->hospede_id->nome }}</td>
                        <td>{{ $reserva->quarto }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</td>
                        <td>
                          {{ $reserva->n_adultos }}
                          @if($reserva->n_criancas >= 1)
                          / ({{ $reserva->n_criancas }})
                          @else
                          ""
                          @endif
                        </td>
                        {{-- <td>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteReservaModal{{ $reserva->id }}">
                                🗑️
                            </button>
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
    @endcomponent
@stop

@section('css')
<style>
    #editlink {
        text-decoration: none
    }
</style>
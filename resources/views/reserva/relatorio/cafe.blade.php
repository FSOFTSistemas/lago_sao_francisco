@extends('adminlte::page')

@section('title', 'Lista de Café da Manhã')

@section('content_header')
    <h1>Lista de Café da Manhã</h1>
    @stop
    
    @section('content')
    @php $totalGeral = 0; @endphp
    @foreach($reservas as $reserva)
    @php 
                    $qtdPessoas = $reserva->n_adultos + $reserva->n_criancas;
                    $totalGeral += $qtdPessoas;
    @endphp
    @endforeach
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
                                {{-- Alterado de hospedes_secundarios para lista_secundarios --}}
                                @if($reserva->lista_secundarios && $reserva->lista_secundarios->count() > 0)
                                    <ul class="mb-0 pl-3">
                                        @foreach($reserva->lista_secundarios as $sec)
                                            <li>{{ $sec->nome }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-info" style="font-size: 1rem;">
                                    {{-- Contagem ajustada --}}
                                    {{-- {{ 1 + ($reserva->lista_secundarios ? $reserva->lista_secundarios->count() : 0) }} --}}
                                    {{ $reserva->n_adultos + $reserva->n_criancas }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    ({{ $reserva->n_adultos }} Adt / {{ $reserva->n_criancas }} Cri)
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
                        </td>
                        <td>{{$totalGeral}}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop
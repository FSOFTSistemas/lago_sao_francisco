@extends('adminlte::page')

@section('title', 'Relatório Detalhado de Vendas')

@section('content_header')
    <h1>Relatório Detalhado de Vendas (Por Vendedor)</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <form action="{{ route('relatorios.vendas_detalhado') }}" method="GET" class="form-inline">
            
            <div class="form-group mb-2 mr-3">
                <label class="mr-2 font-weight-bold">Vendedor:</label>
                <select name="vendedor_id" class="form-control select2">
                    {{-- Opção Extra: Todos --}}
                    <option value="todos" {{ $vendedorId == 'todos' ? 'selected' : '' }}>Todos os Vendedores</option>
                    
                    {{-- Lista apenas vendedores com reservas --}}
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}" {{ $vendedorId == $v->id ? 'selected' : '' }}>
                            {{ $v->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-2">
                <label class="mr-2">De:</label>
                <input type="date" class="form-control" name="data_inicio" value="{{ $dataInicio }}">
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <label class="mr-2">Até:</label>
                <input type="date" class="form-control" name="data_fim" value="{{ $dataFim }}">
            </div>
            
            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>

            {{-- Botão PDF --}}
            <a href="{{ route('relatorios.vendas_detalhado_pdf', request()->all()) }}" 
               target="_blank" 
               class="btn btn-danger mb-2 ml-3">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </a>
        </form>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Data Venda</th>
                        <th>Quarto</th>
                        <th>Hóspede</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th class="text-right">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalGeral = 0; @endphp

                    @forelse($vendasAgrupadas as $nomeVendedor => $reservas)
                        <tr class="bg-secondary">
                            <td colspan="6" class="font-weight-bold pl-3" style="background-color: #6c757d; color: white;">
                                <i class="fas fa-user mr-2"></i> {{ $nomeVendedor }}
                            </td>
                        </tr>

                        @php $subtotalVendedor = 0; @endphp

                        @foreach($reservas as $reserva)
                            @php 
                                $subtotalVendedor += $reserva->valor_total;
                                $totalGeral += $reserva->valor_total;
                            @endphp
                            <tr>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($reserva->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="align-middle">{{ $reserva->quarto->nome ?? '-' }}</td>
                                <td class="align-middle">{{ $reserva->hospede->nome ?? '-' }}</td>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</td>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</td>
                                <td class="align-middle text-right">R$ {{ number_format($reserva->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        <tr style="background-color: #e9ecef;">
                            <td colspan="5" class="text-right font-weight-bold">Total {{ $nomeVendedor }}:</td>
                            <td class="text-right font-weight-bold text-dark">R$ {{ number_format($subtotalVendedor, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Nenhuma venda encontrada para o filtro selecionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($vendedorId == 'todos')
                <tfoot>
                    <tr class="bg-dark text-white font-weight-bold">
                        <td colspan="5" class="text-right text-uppercase">Total Geral (Todos):</td>
                        <td class="text-right">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@stop
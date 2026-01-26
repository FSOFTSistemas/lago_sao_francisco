@extends('adminlte::page')

@section('title', 'Relatório Detalhado de Vendas')

@section('content_header')
    <h1>Relatório Detalhado de Vendas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('relatorios.vendas_detalhado') }}" method="GET" class="form-inline">

                <div class="form-group mb-2 mr-3">
                    <label class="mr-2 font-weight-bold">Vendedor:</label>
                    <select name="vendedor_id" class="form-control select2" required>
                        <option value="" disabled {{ is_null($vendedorId) ? 'selected' : '' }}>Selecione...</option>
                        <option value="todos" {{ $vendedorId == 'todos' ? 'selected' : '' }}>Todos os Vendedores</option>
                        @foreach ($vendedores as $v)
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

                <button type="submit" class="btn btn-primary mb-2 mr-2">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </form>
        </div>

        <div class="card-body">

            @if ($vendasReservasAgrupadas->isEmpty() && $vendasDayUseAgrupadas->isEmpty())
                <div class="alert alert-light text-center" role="alert">
                    <i class="fas fa-search fa-2x mb-2 text-muted"></i><br>
                    Utilize o filtro acima para gerar o relatório.
                </div>
            @else
                {{-- BLOCO 1: RESERVAS --}}
                @if ($vendasReservasAgrupadas->isNotEmpty())
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="text-primary"><i class="fas fa-bed"></i> Vendas de Hospedagem (Reservas)</h4>
                        <a href="{{ route('relatorios.vendas_detalhado_pdf', array_merge(request()->all(), ['tipo' => 'reserva'])) }}"
                            target="_blank" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> PDF Reservas
                        </a>
                    </div>

                    <div class="table-responsive mb-5">
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
                                @php $totalGeralReserva = 0; @endphp
                                @foreach ($vendasReservasAgrupadas as $nomeVendedor => $reservas)
                                    <tr class="bg-secondary">
                                        <td colspan="6" class="pl-3 font-weight-bold">{{ $nomeVendedor }}</td>
                                    </tr>
                                    @php $sub = 0; @endphp
                                    @foreach ($reservas as $reserva)
                                        @php
                                            $sub += $reserva->valor_total;
                                            $totalGeralReserva += $reserva->valor_total;
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($reserva->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $reserva->quarto->nome ?? '-' }}</td>
                                            <td>{{ $reserva->hospede->nome ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</td>
                                            <td class="text-right">R$
                                                {{ number_format($reserva->valor_total, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background-color: #f1f1f1;">
                                        <td colspan="5" class="text-right font-weight-bold">Total {{ $nomeVendedor }}:
                                        </td>
                                        <td class="text-right font-weight-bold">R$ {{ number_format($sub, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($vendedorId == 'todos')
                                <tfoot>
                                    <tr class="bg-dark font-weight-bold">
                                        <td colspan="5" class="text-right">TOTAL GERAL RESERVAS:</td>
                                        <td class="text-right">R$ {{ number_format($totalGeralReserva, 2, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif

                {{-- BLOCO 2: DAY USE --}}
                @if ($vendasDayUseAgrupadas->isNotEmpty())
                    <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-4">
                        <h4 class="text-success"><i class="fas fa-umbrella-beach"></i> Vendas de Day Use</h4>
                        <a href="{{ route('relatorios.vendas_detalhado_pdf', array_merge(request()->all(), ['tipo' => 'dayuse'])) }}"
                            target="_blank" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> PDF Day Use
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th>Data Venda</th>
                                    <th>Hóspede/Cliente</th>
                                    <th>Data de Uso</th>
                                    <th class="text-right">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalGeralDayUse = 0; @endphp
                                @foreach ($vendasDayUseAgrupadas as $nomeVendedor => $dayUses)
                                    <tr class="bg-secondary">
                                        <td colspan="4" class="pl-3 font-weight-bold">{{ $nomeVendedor }}</td>
                                    </tr>
                                    @php $sub = 0; @endphp
                                    @foreach ($dayUses as $du)
                                        @php
                                            $valor = $du->total ?? ($du->valor_total ?? ($du->valor ?? 0));
                                            $sub += $valor;
                                            $totalGeralDayUse += $valor;
                                            $cliente = $du->cliente->nome_razao_social ?? ($du->cliente_nome ?? 'N/D');
                                            $dataUso = isset($du->data)
                                                ? \Carbon\Carbon::parse($du->data)->format('d/m/Y')
                                                : '-';
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($du->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $cliente }}</td>
                                            <td>{{ $dataUso }}</td>
                                            <td class="text-right">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background-color: #f1f1f1;">
                                        <td colspan="3" class="text-right font-weight-bold">Total {{ $nomeVendedor }}:
                                        </td>
                                        <td class="text-right font-weight-bold">R$ {{ number_format($sub, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($vendedorId == 'todos')
                                <tfoot>
                                    <tr class="bg-success text-white font-weight-bold">
                                        <td colspan="3" class="text-right">TOTAL GERAL DAY USE:</td>
                                        <td class="text-right">R$ {{ number_format($totalGeralDayUse, 2, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif

            @endif
        </div>
    </div>
@stop

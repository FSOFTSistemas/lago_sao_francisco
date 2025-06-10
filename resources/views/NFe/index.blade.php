@extends('adminlte::page')

@section('title', 'Notas Fiscais')

@section('content_header')
    <h5>Lista de Notas Fiscais</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-success float-end new" href="{{ route('nota_fiscal.create') }}">
            <i class="fas fa-plus"></i> Nova Nota Fiscal
        </a>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 6,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Série</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Valor Total (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($notasFiscais as $nota)
                <tr>
                    <td>{{ $nota->id }}</td>
                    <td>{{ $nota->numero }}</td>
                    <td>{{ $nota->serie }}</td>
                    <td>{{ \Carbon\Carbon::parse($nota->data)->format('d/m/Y') }}</td>
                    <td>{{ $nota->cliente->nome_razao_social ?? 'N/A' }}</td>
                    <td>{{ number_format($nota->total_notas, 2, ',', '.') }}</td>
                    <td>
                        <!-- <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showNotaFiscal{{ $nota->id }}">
                            👁️
                        </button>

                        <a class="btn btn-warning btn-sm" href="{{ route('nota_fiscal.edit', $nota->id) }}">
                            ✏️
                        </a>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteNotaFiscalModal{{ $nota->id }}">
                            🗑️
                        </button> -->
                    </td>
                </tr>

             
            @endforeach
        </tbody>
    @endcomponent
@stop

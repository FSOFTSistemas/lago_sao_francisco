@extends('adminlte::page')

@section('title', 'Lançamentos da Conta Corrente')

@section('content_header')
    <h5>Lançamentos da Conta Corrente</h5>
    <hr>
@stop

@section('content')

{{-- Filtro por Conta Corrente --}}
<form method="GET" action="{{ route('lancamentos.index') }}" class="mb-4">
    <div class="form-row align-items-end">
        <div class="form-group col-md-6">
            <label for="conta_id">Conta Corrente:</label>
            <select name="conta_id" id="conta_id" class="form-control">
                @foreach ($contasCorrente as $conta)
                    <option value="{{ $conta->id }}" {{ request('conta_id') == $conta->id ? 'selected' : '' }}>
                        {{ $conta->titular }} - Conta: {{ $conta->numero_conta }} 
                        (Saldo: R$ {{ number_format($conta->saldo, 2, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-6 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary mr-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('lancamentos.index') }}" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Limpar
            </a>
        </div>
    </div>
</form>

@component('components.data-table', [
    'responsive' => [
        ['responsivePriority' => 1, 'targets' => 0],
        ['responsivePriority' => 2, 'targets' => 1],
        ['responsivePriority' => 2, 'targets' => 2],
        ['responsivePriority' => 2, 'targets' => 3],
        ['responsivePriority' => 2, 'targets' => 4],
        ['responsivePriority' => 2, 'targets' => 5],
        ['responsivePriority' => 2, 'targets' => 6],
        ['responsivePriority' => 2, 'targets' => 7],
        ['responsivePriority' => 2, 'targets' => 8],
        ['responsivePriority' => 2, 'targets' => 9],
        ['responsivePriority' => 2, 'targets' => 10],
        ['responsivePriority' => 4, 'targets' => -1],
    ],
    'itemsPerPage' => 10,
    'valueColumnIndex' => 8
])
    <thead class="bg-primary text-white">
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Descrição</th>
            <th>Conta</th>
            <th>Banco</th>
            <th>Empresa</th>
            <th>Tipo</th>
            <th>Status</th>
            <th class="text-right">Valor (R$)</th>
            <th>Criado em</th>
            <th>Atualizado em</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        @if($lancamentos->count())
            @foreach($lancamentos as $lancamento)
                <tr>
                    <td>{{ $lancamento->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($lancamento->data)->format('d/m/Y') }}</td>
                    <td>{{ $lancamento->descricao }}</td>
                    <td>{{ $lancamento->contaCorrente->numero_conta ?? 'N/A' }}</td>
                    <td>{{ $lancamento->banco->nome ?? 'N/A' }}</td>
                    <td>{{ $lancamento->empresa->nome_fantasia ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $lancamento->tipo == 'entrada' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($lancamento->tipo) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $lancamento->status == 'finalizado' ? 'badge-primary' : 'badge-warning' }}">
                            {{ ucfirst($lancamento->status) }}
                        </span>
                    </td>
                    <td class="text-right font-weight-bold {{ $lancamento->tipo == 'entrada' ? 'text-success' : 'text-danger' }}">
                        {{ number_format($lancamento->valor, 2, ',', '.') }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($lancamento->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($lancamento->updated_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showLancamentoModal{{ $lancamento->id }}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @include('lancamentos.modals._show', ['lancamento' => $lancamento])
            @endforeach
        @else
            <tr>
                <td colspan="12" class="text-center text-muted">Nenhum lançamento encontrado para esta conta.</td>
            </tr>
        @endif
    </tbody>
@endcomponent


@stop

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            $('#conta_id').select2({
                placeholder: 'Selecione uma conta',
                width: '100%',
                allowClear: true,
                language: 'pt-BR'
            });
        });
    </script>
@endpush

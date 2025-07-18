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
            {{-- Conta Corrente --}}
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

            {{-- Data Início --}}
            <div class="form-group col-md-3">
                <label for="data_inicio">Data Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                       value="{{ request('data_inicio') }}">
            </div>

            {{-- Data Fim --}}
            <div class="form-group col-md-3">
                <label for="data_fim">Data Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control"
                       value="{{ request('data_fim') }}">
            </div>

            {{-- Status --}}
            <div class="form-group col-md-3">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="finalizado" {{ request('status') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                </select>
            </div>

            {{-- Tipo --}}
            <div class="form-group col-md-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                </select>
            </div>

            {{-- Botões --}}
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

    {{-- Tabela de Lançamentos --}}
    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 2, 'targets' => 3],
            ['responsivePriority' => 2, 'targets' => 4],
            ['responsivePriority' => 2, 'targets' => 5],
            ['responsivePriority' => 2, 'targets' => 6],
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
                <th>Empresa</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Valor (R$)</th>
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
                        <td>{{ $lancamento->empresa->nome_fantasia ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $lancamento->tipo == 'entrada' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($lancamento->tipo) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $lancamento->status == 'finalizado' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($lancamento->status) }}
                            </span>
                        </td>
                        <td>{{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

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

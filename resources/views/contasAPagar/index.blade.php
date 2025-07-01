@extends('adminlte::page')

@section('title', 'Contas a Pagar')

@section('content_header')
    <h5>Lista de Contas A Pagar</h5>
    <hr>
@stop

@section('content')

    {{-- Filtro por mês --}}
    <form method="GET" action="{{ route('contasAPagar.index') }}" class="form-inline mb-3">
        <div class="form-group mr-2">
            <label for="mes" class="mr-2">Filtrar por mês:</label>
            <input type="month" name="mes" id="mes" class="form-control"
                value="{{ request('mes') ?? now()->format('Y-m') }}">
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('contasAPagar.index') }}" class="btn btn-secondary ml-2">Limpar</a>
    </form>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createContasAPagarModal">
            <i class="fas fa-plus"></i> Nova Conta a Pagar
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 2, 'targets' => 3],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Data de Vencimento</th>
                <th>Valor</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contasAPagar as $contasAPagar)
                <tr>
                    <td>{{ $contasAPagar->id }}</td>
                    <td>
                        {{ $contasAPagar->descricao }}
                        @if($contasAPagar->total_parcelas > 1)
                            <small class="text-muted d-block">
                                Parcela {{ $contasAPagar->numero_parcela }} de {{ $contasAPagar->total_parcelas }}
                            </small>

                            @if(request('mes'))
                                @php
                                    $mesFiltro = \Carbon\Carbon::createFromFormat('Y-m', request('mes'));
                                    $dataVencimento = \Carbon\Carbon::parse($contasAPagar->data_vencimento);
                                @endphp

                                @if($dataVencimento->format('Y-m') === $mesFiltro->format('Y-m'))
                                    <small class="text-primary d-block">
                                        📌 Parcela atual
                                    </small>
                                @endif
                            @endif
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($contasAPagar->data_vencimento)->format('d/m/Y') }}</td>
                    <td>R${{ number_format($contasAPagar->valor, 2, ',', '.') }}</td>
                    <td>
                        @if($contasAPagar->status == "finalizado")
                            <span class="text-success">Finalizado <i class="fa-regular fa-circle-check"></i></span>
                        @else
                            <span class="text-warning">Pendente <i class="fa-solid fa-triangle-exclamation"></i></span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showContasAPagar{{ $contasAPagar->id }}">
                            👁️
                        </button>

                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                            data-target="#editContasAPagarModal{{ $contasAPagar->id }}">
                            ✏️
                        </button>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteContasAPagarModal{{ $contasAPagar->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                @include('contasAPagar.modals._show', ['contasAPagar' => $contasAPagar])
                @include('contasAPagar.modals._edit', ['contasAPagar' => $contasAPagar])
                @include('contasAPagar.modals._delete', ['contasAPagar' => $contasAPagar])
            @endforeach
        </tbody>
    @endcomponent

    @include('contasAPagar.modals._create')

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        function validarValor(input) {
            const valor = parseFloat(input.value);
            if (isNaN(valor) || valor < 0.01) {
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        }

        function validarValorPago(input) {
            const valorPago = parseFloat(input.value) || 0;
            const valorConta = parseFloat(document.getElementById('valor').value) || 0;

            if (valorPago < 0 || (valorPago > 0 && valorPago > valorConta)) {
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        }
    </script>
@stop

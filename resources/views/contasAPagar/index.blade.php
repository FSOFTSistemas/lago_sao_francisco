@extends('adminlte::page')

@section('title', 'Contas a Pagar')

@section('content_header')
<h5>Lista de Contas A Pagar</h5>
<hr>
@stop

@section('content')

<form method="GET" action="{{ route('contasAPagar.index') }}" class="mb-4">

    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="data_inicio">Data Início</label>
            <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                value="{{ request('data_inicio') }}">
        </div>

        <div class="form-group col-md-3">
            <label for="data_fim">Data Fim</label>
            <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
        </div>

        <div class="form-group col-md-3">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">Todos</option>
                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
            </select>
        </div>

    <div class="col-md-6">
        <label for="fornecedorSelect" class="form-label" style="font-size: 1.2rem">Fornecedor:</label>
        <select id="fornecedorSelect" name="fornecedor_id" class="form-control w-100">
            @if(old('fornecedor_id') && $fornecedor = \App\Models\Fornecedor::find(old('fornecedor_id')))
                <option value="{{ $fornecedor->id }}" selected>{{ $fornecedor->nome_fantasia }}</option>
            @endif
        </select>
    </div>



    </div>

    <div class="form-row">
        <div class="form-group col-md-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary mr-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <a href="{{ route('contasAPagar.index') }}" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Limpar
            </a>

        </div>
    </div>

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
        ['responsivePriority' => 2, 'targets' => 4],
        ['responsivePriority' => 2, 'targets' => 5],
        ['responsivePriority' => 2, 'targets' => 6],
        ['responsivePriority' => 4, 'targets' => -1],
    ],
    'itemsPerPage' => 10,
    'showTotal' => false,
    'valueColumnIndex' => 3,
     'order'=> [
        [] // Ordena pela 3ª coluna (índice 2), ascendente
    ]
])
<thead class="bg-primary text-white">
    <tr>
        <th>ID</th>
        <th>Descrição</th>
        <th>Data de Vencimento</th>
        <th>Valor</th>
        <th>Situação</th>
        <th>Forma de pagamento</th>
        <th>Fornecedor</th>
        <th>Empresa</th>
        <th>Ações</th>
    </tr>
</thead>
<tbody>
    @foreach ($contasComParcelas as $contasAPagar)
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
                @if($contasAPagar->status == "pago")
                    <span class="text-success">Pago <i class="fa-regular fa-circle-check"></i></span>
                @else
                    <span class="text-warning">Pendente <i class="fa-solid fa-triangle-exclamation"></i></span>
                @endif
            </td>
           <td>
            @php
                $formas = explode("\n", $contasAPagar->forma_pagamento);
            @endphp

            @foreach($formas as $forma)
                @if(trim($forma) == 'conta_corrente')
                    <span class="text-success">Conta Corrente</span><br>
                @elseif(trim($forma) == 'caixa')
                    <span class="text-success">Caixa</span><br>
                @endif
            @endforeach
        </td>

            <td>
                {{ $contasAPagar->fornecedor->nome_fantasia ?? ''}}
            </td>
            <td>
                {{ $contasAPagar->empresa->nome_fantasia ?? ''}}
            </td>
            <td>
                @if($contasAPagar->valor - $contasAPagar->valor_pago > 0)
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                        data-target="#pagarContasAPagarModal{{ $contasAPagar->id }}">
                        💰
                    </button>
                @endif
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#showContasAPagar{{ $contasAPagar->id }}">
                    👁️
                </button>

               @if($contasAPagar->pode_excluir)
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                        data-target="#deleteContasAPagarModal{{ $contasAPagar->conta_id }}">
                        🗑️
                    </button>
                @endif


            </td>
        </tr>
        @include('contasAPagar.modals._pagar', ['contasAPagar' => $contasAPagar])
        @include('contasAPagar.modals._show', ['contasAPagar' => $contasAPagar])
        @push('modais')
            @include('contasAPagar.modals._delete', ['contasAPagar' => $contasAPagar])
        @endpush

        @endforeach
        
    </tbody>

@endcomponent
@include('contasAPagar.modals._create')
@stack('modais')
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
    .new {
        background-color: #679A4C !important;
        border: none !important;
    }
</style>
@endpush

@push('js')
{{-- 1. Carregue o jQuery PRIMEIRO --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- 2. Depois, carregue o JavaScript do Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- 3. Finalmente, seu script de inicialização --}}
<script>
  $(document).ready(function() {
    // Inicializa o Select2 no elemento correto
    $('#fornecedorSelect').select2({
        placeholder: "Selecione um fornecedor",
        allowClear: true,
        minimumInputLength: 2,
        language: "pt-BR", // Adicionar tradução se necessário
        ajax: {
            url: '{{ route("fornecedores.search") }}',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.nome_fantasia,
                            id: item.id
                        }
                    })
                };
            },
            cache: true
        }
    });
  });
</script>
@endpush
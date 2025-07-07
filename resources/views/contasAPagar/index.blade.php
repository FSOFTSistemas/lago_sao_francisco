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
        <th>Fornecedor</th>
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
                {{ $contasAPagar->fornecedor->nome_fantasia ?? ''}}
            </td>
            <td>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                    data-target="#pagarContasAPagarModal{{ $contasAPagar->id }}">
                    💰
                </button>
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#showContasAPagar{{ $contasAPagar->id }}">
                    👁️
                </button>

                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                    data-target="#editContasAPagarModal{{ $contasAPagar->id }}">
                    ✏️
                </button>

               @if($contasAPagar->pode_excluir)
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                        data-target="#deleteContasAPagarModal{{ $contasAPagar->id }}">
                        🗑️
                    </button>
                @endif


            </td>
        </tr>
        @include('contasAPagar.modals._pagar', ['contasAPagar' => $contasAPagar])
        @include('contasAPagar.modals._show', ['contasAPagar' => $contasAPagar])
        @include('contasAPagar.modals._edit', ['contasAPagar' => $contasAPagar])
        @include('contasAPagar.modals._delete', ['contasAPagar' => $contasAPagar])
        @endforeach
    </tbody>
@endcomponent

@include('contasAPagar.modals._create')
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
<!-- Then Select2 JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        console.log('Inicializando Select2...');
        
        $('#fornecedorSelect').select2({
            placeholder: "Selecione um fornecedor",
            allowClear: true,
            minimumInputLength: 2,
            language: "pt-BR",
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
                }
            }
        }).on('select2:open', () => {
            console.log('Select2 aberto');
        });
        
        console.log('Select2 inicializado');
    });
</script>
@endpush
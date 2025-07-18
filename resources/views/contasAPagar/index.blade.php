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

        </div>
        {{-- Busca do fornecedor --}}
        @php
            $fornecedorId = request('fornecedor_id');
            $fornecedorNome = $fornecedorId ? \App\Models\Fornecedor::find($fornecedorId)?->nome_fantasia : '';
        @endphp

        <div class="form-group row">
            <label for="fornecedor_id" class="col-md-3 label-control">* Fornecedor</label>
            <div class="col-sm-4">
                <select class="form-control select2" name="fornecedor_id" id="fornecedor_id" style="width: 100%;">
                    @if ($fornecedorId && $fornecedorNome)
                        <option value="{{ $fornecedorId }}" selected>{{ $fornecedorNome }}</option>
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
            ['responsivePriority' => 2, 'targets' => 7],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
        'order' => [[]],
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>Conta ID</th>
                <th>Parcela ID</th>
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
                    <td>{{ $contasAPagar->conta_id }}</td>
                    <td>{{ $contasAPagar->parcela_id }}</td>
                    <td>
                        {{ $contasAPagar->descricao }}
                        @if ($contasAPagar->total_parcelas > 1)
                            <small class="text-muted d-block">
                                Parcela {{ $contasAPagar->numero_parcela }} de {{ $contasAPagar->total_parcelas }}
                            </small>

                            @if (request('mes'))
                                @php
                                    $mesFiltro = \Carbon\Carbon::createFromFormat('Y-m', request('mes'));
                                    $dataVencimento = \Carbon\Carbon::parse($contasAPagar->data_vencimento);
                                @endphp

                                @if ($dataVencimento->format('Y-m') === $mesFiltro->format('Y-m'))
                                    <small class="text-primary d-block">📌 Parcela atual</small>
                                @endif
                            @endif
                        @endif
                    </td>

                    <td>{{ \Carbon\Carbon::parse($contasAPagar->data_vencimento)->format('d/m/Y') }}</td>
                    <td>R${{ number_format($contasAPagar->valor, 2, ',', '.') }}</td>

                    <td>
                        @if ($contasAPagar->status == 'pago')
                            <span class="text-success">Pago <i class="fa-regular fa-circle-check"></i></span>
                        @else
                            <span class="text-warning">Pendente <i class="fa-solid fa-triangle-exclamation"></i></span>
                        @endif
                    </td>

                    <td>
                        @php
                            $formas = explode("\n", $contasAPagar->forma_pagamento);
                        @endphp

                        @foreach ($formas as $forma)
                            @if (trim($forma) == 'conta_corrente')
                                <span class="text-success">Conta Corrente</span><br>
                            @elseif(trim($forma) == 'caixa')
                                <span class="text-success">Caixa</span><br>
                            @endif
                        @endforeach
                    </td>

                    <td>{{ $contasAPagar->fornecedor->nome_fantasia ?? '' }}</td>
                    <td>{{ $contasAPagar->empresa->nome_fantasia ?? '' }}</td>
                    <td>
                        @if ($contasAPagar->valor - $contasAPagar->valor_pago > 0)
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                data-target="#pagarContasAPagarModal{{ $contasAPagar->id }}">
                                💰
                            </button>
                        @endif

                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showContasAPagar{{ $contasAPagar->id }}">
                            👁️
                        </button>

                        @if ($contasAPagar->pode_excluir)
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteContasAPagarModal{{ $contasAPagar->id }}">
                                🗑️
                            </button>
                        @endif
                    </td>
                </tr>
                @push('modais')
                    @include('contasAPagar.modals._pagar', ['contasAPagar' => $contasAPagar])
                    @include('contasAPagar.modals._show', ['contasAPagar' => $contasAPagar])
                    @include('contasAPagar.modals._delete', ['contasAPagar' => $contasAPagar])
                @endpush
            @endforeach
        </tbody>
    @endcomponent

    @include('contasAPagar.modals._create')
    @stack('modais')

@stop

@push('js')
        <script>console.log('aqui1')</script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <script>console.log('aqui2')</script>
        <script>
            $(document).ready(function() {
                console.log('aqui3')
                console.log($.fn.jquery)
                
            const select = $('#fornecedor_id');

            if (!select) {
                console.warn("⚠️ Campo #fornecedor_id não encontrado no DOM.");
                return;
            }

            if (typeof $ === 'undefined') {
                console.error("❌ jQuery não está disponível.");
                return;
            }

            if (typeof $.fn.select2 === 'undefined') {
                console.error("❌ Select2 não foi carregado corretamente.");
                return;
            }



            $('#fornecedor_id').select2({
                placeholder: 'Selecione um Fornecedor',
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route('fornecedores.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        console.log("Dados recebidos do backend:", data);

                        return {
                            results: data.map(function(fornecedor) {
                                return {
                                    id: fornecedor.id,
                                    text: fornecedor.nome_fantasia
                                };
                            })
                        };
                    },
                    cache: true
                },
                width: '100%',
                language: {
                    noResults: function() {
                        return "Nenhum resultado encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    inputTooShort: function(args) {
                        var remainingChars = args.minimum - args.input.length;
                        return 'Digite mais ' + remainingChars + ' caractere' + (remainingChars !== 1 ?
                            's' : '') + ' para buscar';
                    },
                    loadingMore: function() {
                        return "Carregando mais resultados...";
                    }
                }
            });
        });
    </script>
@endpush

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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

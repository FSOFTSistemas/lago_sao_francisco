@extends('adminlte::page')

@section('title', 'Fluxo de Caixa')

@section('content_header')
    <h5>Fluxo de Caixas</h5>
    <hr>
@stop

@section('content')
    <form method="GET" action="{{ route('fluxoCaixa.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="data_inicio">Data Início</label>
                <input type="date" class="form-control" name="data_inicio" id="data_inicio"
                    value="{{ request('data_inicio', now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label for="data_fim">Data Fim</label>
                <input type="date" class="form-control" name="data_fim" id="data_fim"
                    value="{{ request('data_fim', now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                    <option value="abertura" {{ request('tipo') == 'abertura' ? 'selected' : '' }}>Abertura</option>
                    <option value="fechamento" {{ request('tipo') == 'fechamento' ? 'selected' : '' }}>Fechamento</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-end">
            <button id="btnToggleTotais" class="btn btn-outline-secondary mb-3" type="button" data-toggle="collapse"
                data-target="#totaisMovimentosCollapse" aria-expanded="false" aria-controls="totaisMovimentosCollapse">
                <i class="fas fa-filter"></i> Mostrar Totais por Movimento
            </button>
        </div>
        <div class="collapse" id="totaisMovimentosCollapse">
            <br>
            <div class="card card-body">
                <div class="row mb-4">
                    @foreach ($totaisPorMovimento as $item)
                        @php
                            $descricao = $item->movimento->descricao ?? 'outros';

                            // Mapas de cor e ícone
                            $mapa = [
                                'venda-dinheiro' => [
                                    'cor' => 'success',
                                    'icone' => 'fas fa-money-bill-wave',
                                    'nome' => 'Dinheiro',
                                ],
                                'venda-cartão-crédito' => [
                                    'cor' => 'primary',
                                    'icone' => 'fas fa-credit-card',
                                    'nome' => 'Cartão Crédito',
                                ],
                                'venda-cartão-débito' => [
                                    'cor' => 'info',
                                    'icone' => 'fas fa-credit-card',
                                    'nome' => 'Cartão Débito',
                                ],
                                'venda-pix' => ['cor' => 'success', 'icone' => 'fas fa-qrcode', 'nome' => 'Pix'],
                                'venda-transferência-bancária' => [
                                    'cor' => 'warning',
                                    'icone' => 'fas fa-university',
                                    'nome' => 'Transferência',
                                ],
                                'venda-boleto-bancário' => [
                                    'cor' => 'secondary',
                                    'icone' => 'fas fa-barcode',
                                    'nome' => 'Boleto',
                                ],
                                'venda-carteira' => ['cor' => 'dark', 'icone' => 'fas fa-wallet', 'nome' => 'Carteira'],
                                'venda-cheque' => [
                                    'cor' => 'danger',
                                    'icone' => 'fas fa-file-invoice-dollar',
                                    'nome' => 'Cheque',
                                ],
                            ];

                            $dados = $mapa[$descricao] ?? [
                                'cor' => 'secondary',
                                'icone' => 'fas fa-cash-register',
                                'nome' => ucfirst(str_replace('-', ' ', $descricao)),
                            ];
                        @endphp

                        <div class="col-md-3 mb-3">
                            <div class="card border-left-{{ $dados['cor'] }} shadow h-100 py-2">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-{{ $dados['cor'] }} font-weight-bold text-uppercase mb-1">
                                            {{ $dados['nome'] }}
                                        </h6>
                                        <span class="text-dark">R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="icon text-{{ $dados['cor'] }} ml-3">
                                        <i class="{{ $dados['icone'] }} fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-md-3 mb-3">
                        <div class="card border-left-dark shadow h-100 py-2">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-dark font-weight-bold text-uppercase mb-1">Total Geral</h6>
                                    <span class="text-dark">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
                                </div>
                                <div class="icon text-dark ml-3">
                                    <i class="fas fa-calculator fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </form>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createFluxoCaixaModal">
            <i class="fas fa-plus"></i> Novo Fluxo de Caixa
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 3, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 2,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Movimento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fluxoCaixas as $fluxoCaixa)
                <tr>
                    <td>{{ $fluxoCaixa->id }}</td>
                    <td>{{ $fluxoCaixa->descricao }}</td>
                    <td>R${{ $fluxoCaixa->valor }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($fluxoCaixa->data)->format('d/m/Y') }}</td>
                    <td
                        style="color:
                        @switch($fluxoCaixa->tipo)
                            @case('entrada') green @break
                            @case('saida') red @break
                            @case('abertura') maroon @break
                            @case('fechamento') maroon @break
                            @default black
                        @endswitch;">
                        {{ ucfirst($fluxoCaixa->tipo) }}
                    </td>
                    <td>{{ $fluxoCaixa->movimento->descricao }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target="#showFluxoCaixa{{ $fluxoCaixa->id }}">
                            👁️
                        </button>

                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                            data-target="#editFluxoCaixaModal{{ $fluxoCaixa->id }}">
                            ✏️
                        </button>

                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFluxoCaixaModal{{ $fluxoCaixa->id }}">
                            🗑️
                        </button>
                    </td>
                </tr>

                @include('fluxoCaixa.modals._show', ['fluxoCaixa' => $fluxoCaixa])
                @include('fluxoCaixa.modals._edit', ['fluxoCaixa' => $fluxoCaixa])
                @include('fluxoCaixa.modals._delete', ['fluxoCaixa' => $fluxoCaixa])
            @endforeach
        </tbody>
    @endcomponent

    @include('fluxoCaixa.modals._create')
    @if (session('sweet_error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Atenção!',
                    text: '{{ session('sweet_error') }}',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btnToggleTotais').click(function() {
                let $btn = $(this);
                let $collapse = $('#totaisMovimentosCollapse');
                if ($collapse.hasClass('show')) {
                    $btn.html('<i class="fas fa-filter"></i> Mostrar Totais por Movimento');
                } else {
                    $btn.html('<i class="fas fa-filter"></i> Esconder Totais por Movimento');
                }
            });
        });
    </script>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

@stop
